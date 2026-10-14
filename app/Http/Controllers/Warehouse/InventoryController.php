<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\PengajuanBarang;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\ReorderingRule;
use App\Models\StockMove;
use App\Notifications\PengajuanBarangNotification;
use App\Services\StockMoveService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class InventoryController extends Controller
{
    public function __construct(protected StockMoveService $moves) {}

    /**
     * Halaman Inventory Command Center (5 tab).
     */
    public function index(Request $request)
    {
        if (! $this->hasAccess()) {
            abort(403, 'Anda tidak memiliki hak akses ke halaman Inventory.');
        }

        $tab = $request->get('tab', 'overview');
        $search = trim((string) $request->get('search', ''));
        $canManage = $this->canManage();

        // KPI (sama dengan dashboard gudang).
        $totalSku = Product::active()->count();
        $stokHabis = Product::active()->where('stock', '<=', 0)->count();
        $menipis = $this->hasMinCol()
            ? Product::active()->whereNotNull('min_stock')->whereColumn('stock', '<=', 'min_stock')->count() : 0;
        $expired30 = Schema::hasTable('product_batches') ? DB::table('product_batches')
            ->where('qty_current', '>', 0)->whereNotNull('expiry_date')
            ->whereBetween('expiry_date', [today()->toDateString(), today()->addDays(30)->toDateString()])
            ->distinct('product_id')->count('product_id') : 0;
        $movesWaiting = Schema::hasTable('stock_moves')
            ? StockMove::where('status', StockMove::STATUS_WAITING)->count() : 0;

        // Tab Stok + Opname: produk + expiry terdekat + rule.
        $products = Product::active()
            ->when($search !== '', fn ($q) => $q->where(fn ($w) => $w
                ->where('product_code', 'like', "%{$search}%")
                ->orWhere('product_name', 'like', "%{$search}%")))
            ->orderByRaw('LOWER(COALESCE(NULLIF(TRIM(product_name_clean), ""), product_name))')
            ->with(['batches' => fn ($q) => $q->available()->whereNotNull('expiry_date')->orderBy('expiry_date')->limit(1)])
            ->paginate(20, ['*'], 'stockPage')->withQueryString();

        // Tab Operations: dokumen mutasi.
        $moves = collect();
        if (Schema::hasTable('stock_moves')) {
            $moves = StockMove::with(['product', 'user', 'validator'])
                ->when($request->filled('mstatus'), fn ($q) => $q->where('status', $request->mstatus))
                ->when($request->filled('mtype'), fn ($q) => $q->where('type', $request->mtype))
                ->orderByDesc('id')->paginate(15, ['*'], 'movePage')->withQueryString();
        }

        // Tab Replenishment: SKU di bawah ambang + rules.
        $belowMin = collect();
        $rules = collect();
        if ($this->hasMinCol()) {
            $belowMin = Product::active()->whereNotNull('min_stock')
                ->whereColumn('stock', '<', 'min_stock')
                ->orderBy('stock')->limit(50)->get();
            $rules = Schema::hasTable('reordering_rules')
                ? ReorderingRule::with('product')->orderByDesc('id')->limit(50)->get() : collect();
        }

        // Tab Laporan: batch expired + aging.
        $expiredBatches = collect();
        $agingBatches = collect();
        if (Schema::hasTable('product_batches')) {
            $expiredBatches = ProductBatch::with('product')->available()->whereNotNull('expiry_date')
                ->orderBy('expiry_date')->limit(50)->get();
            $agingBatches = ProductBatch::with('product')->available()
                ->orderBy('receipt_date')->limit(50)->get();
        }

        $productOptions = Product::active()
            ->orderByRaw('LOWER(COALESCE(NULLIF(TRIM(product_name_clean), ""), product_name))')
            ->get(['id', 'product_code', 'product_name']);

        return view('users.warehouse.inventory', compact(
            'tab', 'search', 'canManage',
            'totalSku', 'stokHabis', 'menipis', 'expired30', 'movesWaiting',
            'products', 'moves', 'belowMin', 'rules', 'expiredBatches', 'agingBatches',
            'productOptions'
        ));
    }

    /** Buat dokumen mutasi (waiting). */
    public function storeMove(Request $request, StockMoveService $svc)
    {
        if (! $this->canManage()) {
            abort(403, 'Hanya pengelola stok yang bisa membuat mutasi.');
        }
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'qty' => ['required', 'numeric', 'min:0.01'],
            'type' => ['required', 'in:receipt,delivery,transfer,adjustment,scrap'],
            'expiry_date' => ['nullable', 'date'],
            'reason' => ['nullable', 'string', 'max:1000'],
            'to_location' => ['nullable', 'string', 'max:50'],
        ]);

        // Prefix Opname: milik alur Opname (tercatat otomatis di rekap).
        if (str_starts_with(trim((string) ($validated['reason'] ?? '')), 'Opname:')) {
            return back()->with('error', 'Gunakan halaman Catat Opname untuk entri opname.')->withInput();
        }

        try {
            $svc->createMove($validated, Auth::user());
        } catch (\Throwable $e) {
            report($e);
            $errMsg = $e instanceof \InvalidArgumentException ? $e->getMessage() : 'Terjadi kesalahan sistem. Coba lagi atau hubungi admin.';

            return back()->with('error', $errMsg)->withInput();
        }

        return redirect()->route('warehouse.inventory', ['tab' => 'operations'])->with('success', 'Dokumen mutasi dibuat, menunggu validasi.');
    }

    public function validateMove(StockMove $move, StockMoveService $svc)
    {
        if (! $this->canManage()) {
            abort(403);
        }
        // Draf opname diselesaikan lewat halaman Opname (butuh nama BA + cek basi).
        if ($move->type === StockMove::TYPE_ADJUSTMENT && str_starts_with((string) $move->reason, 'Opname:')) {
            return back()->with('error', 'Draf opname diselesaikan lewat halaman Opname (tombol Selesaikan).');
        }
        try {
            $svc->validateMove($move, Auth::user());
        } catch (\Throwable $e) {
            report($e);
            $errMsg = $e instanceof \InvalidArgumentException ? $e->getMessage() : 'Terjadi kesalahan sistem. Coba lagi atau hubungi admin.';

            return back()->with('error', $errMsg);
        }

        return back()->with('success', "Mutasi #{$move->id} divalidasi, stok diperbarui.");
    }

    public function cancelMove(StockMove $move, StockMoveService $svc)
    {
        if (! $this->canManage()) {
            abort(403);
        }
        // Draf opname dikelola lewat halaman Catat (hitung ulang menggantikan draf lama).
        if ($move->type === StockMove::TYPE_ADJUSTMENT && str_starts_with((string) $move->reason, 'Opname:')) {
            return back()->with('error', 'Draf opname dikelola lewat halaman Catat Opname.');
        }
        try {
            $svc->cancelMove($move);
        } catch (\Throwable $e) {
            report($e);
            $errMsg = $e instanceof \InvalidArgumentException ? $e->getMessage() : 'Terjadi kesalahan sistem. Coba lagi atau hubungi admin.';

            return back()->with('error', $errMsg);
        }

        return back()->with('success', "Mutasi #{$move->id} dibatalkan.");
    }

    public function reverseMove(StockMove $move, StockMoveService $svc)
    {
        if (! $this->canManage()) {
            abort(403);
        }
        // Baris opname utuh milik alur Opname (koreksi via Catat → Selesaikan).
        if ($move->type === StockMove::TYPE_ADJUSTMENT && str_starts_with((string) $move->reason, 'Opname:')) {
            return back()->with('error', 'Baris opname hanya bisa dikoreksi lewat halaman Catat Opname.');
        }
        try {
            $svc->reverseMove($move, Auth::user(), $move->id ? "Reversal mutasi #{$move->id} dari Inventory" : null);
        } catch (\Throwable $e) {
            report($e);
            $errMsg = $e instanceof \InvalidArgumentException ? $e->getMessage() : 'Terjadi kesalahan sistem. Coba lagi atau hubungi admin.';

            return back()->with('error', $errMsg);
        }

        return back()->with('success', "Mutasi #{$move->id} direversal via adjustment.");
    }

    /** Atur min-stock / reorder per produk. */
    public function minStockUpdate(Request $request, Product $product)
    {
        if (! $this->canManage()) {
            abort(403);
        }
        $validated = $request->validate([
            'min_stock' => ['nullable', 'numeric', 'min:0'],
            'reorder_qty' => ['nullable', 'numeric', 'min:0'],
        ]);
        if (! $this->hasMinCol()) {
            return back()->with('error', 'Kolom min-stock belum ada. Jalankan migration inventory dulu.');
        }
        $product->update($validated);

        return back()->with('success', "Batas stok {$product->product_name} diperbarui.");
    }

    /** Simpan/ubah reordering rule. */
    public function rulesStore(Request $request)
    {
        if (! $this->canManage()) {
            abort(403);
        }
        if (! Schema::hasTable('reordering_rules')) {
            return back()->with('error', 'Tabel reordering_rules belum ada. Jalankan migration inventory dulu.');
        }
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'min_qty' => ['required', 'numeric', 'min:0'],
            'max_qty' => ['nullable', 'numeric', 'min:0'],
            'active' => ['nullable', 'boolean'],
        ]);
        $validated['active'] = (bool) ($validated['active'] ?? true);
        ReorderingRule::updateOrCreate(['product_id' => $validated['product_id']], $validated);

        return redirect()->route('warehouse.inventory', ['tab' => 'replenishment'])->with('success', 'Aturan reorder disimpan.');
    }

    /**
     * Buatkan pengajuan barang dari SKU di bawah ambang (masuk alur approval normal).
     */
    public function draftOrder(Request $request)
    {
        if (! $this->canManage()) {
            abort(403);
        }
        $user = Auth::user();
        $ids = array_filter((array) $request->input('product_ids', []), 'is_numeric');
        if (empty($ids)) {
            return back()->with('error', 'Pilih minimal 1 produk.');
        }
        if (! $user->approver_barang_1_id && ! $user->approver_barang_2_id && ! $user->approver_barang_3_id && ! $user->approver_barang_4_id) {
            return back()->with('error', 'Anda belum memiliki Approver Barang. Hubungi Admin/HRD.');
        }

        $items = Product::active()->whereIn('id', $ids)->get();
        if ($items->isEmpty()) {
            return back()->with('error', 'Produk tidak ditemukan.');
        }

        $rincian = [];
        foreach ($items as $item) {
            $need = $this->suggestQty($item);
            if ($need <= 0) {
                continue;
            }
            $rincian[] = [
                'deskripsi' => $item->product_name . " ({$item->product_code})",
                'supplier' => '-',
                'satuan' => $item->unit ?? 'Pcs',
                'jumlah' => $need,
                'keterangan' => 'Auto-reorder Inventory' . ($item->min_stock !== null ? " (stok {$item->stock} < min {$item->min_stock})" : ''),
            ];
        }
        if (empty($rincian)) {
            return back()->with('error', 'Semua produk pilihan stoknya masih aman.');
        }

        $app = [$user->approver_barang_1_id, $user->approver_barang_2_id, $user->approver_barang_3_id, $user->approver_barang_4_id];
        $st = array_map(fn ($a) => $a ? 'menunggu' : 'skipped', $app);

        $pengajuan = PengajuanBarang::create([
            'user_id' => $user->id,
            'judul_pengajuan' => 'Restock otomatis Inventory (' . count($rincian) . ' item)',
            'divisi' => $user->divisi ?: 'Umum',
            'catatan_pemohon' => 'Dibuat otomatis dari halaman Inventory (replenishment).',
            'rincian_barang' => $rincian,
            'status' => 'diajukan',
            'approver_barang_1_id' => $app[0], 'status_appr_1' => $st[0],
            'approver_barang_2_id' => $app[1], 'status_appr_2' => $st[1],
            'approver_barang_3_id' => $app[2], 'status_appr_3' => $st[2],
            'approver_barang_4_id' => $app[3], 'status_appr_4' => $st[3],
        ]);

        $firstApprover = $pengajuan->approver1 && $st[0] === 'menunggu' ? $pengajuan->approver1
            : ($pengajuan->approver2 && $st[1] === 'menunggu' ? $pengajuan->approver2
            : ($pengajuan->approver3 && $st[2] === 'menunggu' ? $pengajuan->approver3
            : ($pengajuan->approver4 && $st[3] === 'menunggu' ? $pengajuan->approver4 : null)));
        if ($firstApprover) {
            $firstApprover->notify(new PengajuanBarangNotification($pengajuan, 'baru'));
        }

        return redirect()->route('pengajuan_barang.index')->with('success', 'Draft restock (' . count($rincian) . ' item) dikirim ke alur approval.');
    }

    protected function suggestQty(Product $item): float
    {
        $rule = Schema::hasTable('reordering_rules')
            ? ReorderingRule::where('product_id', $item->id)->where('active', true)->first() : null;
        $min = $rule?->min_qty ?? $item->min_stock;
        if ($min === null || (float) $item->stock >= (float) $min) {
            return 0;
        }
        $target = $rule?->max_qty ?? $item->reorder_qty ?? ((float) $min * 2);

        return max(0, round((float) $target - (float) $item->stock, 2));
    }

    /** Pindah ke Warehouse\WsStockOpnameController — method opnameIndex dihapus dari sini. */

    protected function hasMinCol(): bool
    {
        return Schema::hasColumn('products', 'min_stock');
    }

    /** Akses baca: divisi sales/gudang + manajemen (cermin Warehouse\WsBaseController). */
    protected function hasAccess(): bool
    {
        $user = Auth::user();
        if (! $user) {
            return false;
        }
        if (\App\Models\User::isSuperUserEmail($user->email ?? '')) {
            return true;
        }
        if ($user->role === 'admin') {
            return true;
        }
        $jabatan = strtolower(trim($user->jabatan ?? ''));
        $divisi = strtolower(trim($user->divisi ?? ''));
        if (\Illuminate\Support\Str::contains($jabatan, 'direktur') || $divisi === 'top management') {
            return true;
        }

        // Seluruh anggota divisi gudang (nama apa pun yang mengandung 'gudang')
        // + jabatan berbau gudang: full akses baca.
        if (\Illuminate\Support\Str::contains($divisi, 'gudang')
            || \Illuminate\Support\Str::contains($jabatan, 'gudang')) {
            return true;
        }

        return in_array($divisi, ['marketing dan operasional', 'finance dan gudang', 'fianance dan gudang'])
            || \Illuminate\Support\Str::contains($jabatan, 'admin gudang') || $jabatan === 'gudang'
            || \Illuminate\Support\Str::contains($jabatan, 'legal & purchasing') || \Illuminate\Support\Str::contains($jabatan, 'purchasing');
    }

    /** Mutasi: pengelola stok + seluruh anggota divisi gudang (full akses). */
    protected function canManage(): bool
    {
        $user = Auth::user();
        if (! $user) {
            return false;
        }
        if (\App\Models\User::isSuperUserEmail($user->email ?? '')) {
            return true;
        }
        if ($user->role === 'admin') {
            return true;
        }
        $jabatan = strtolower(trim($user->jabatan ?? ''));
        $divisi = strtolower(trim($user->divisi ?? ''));

        // Seluruh anggota divisi gudang: full akses tanpa memandang jabatan.
        $isDivisiGudang = in_array($divisi, ['finance dan gudang', 'fianance dan gudang'], true)
            || \Illuminate\Support\Str::contains($divisi, 'gudang')
            || \Illuminate\Support\Str::contains($jabatan, 'gudang');

        return \Illuminate\Support\Str::contains($jabatan, 'test')
            || \Illuminate\Support\Str::contains($jabatan, 'admin gudang') || $jabatan === 'gudang'
            || \Illuminate\Support\Str::contains($jabatan, 'legal & purchasing')
            || $isDivisiGudang;
    }
}

<?php

namespace App\Http\Controllers\Warehouse;

use App\Models\Product;
use App\Models\StockMove;
use App\Models\StockOpname;
use App\Models\WsMutation;
use App\Services\StockMoveService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Kartu Stock per produk (read-only; mutasi hanya dari Pencatatan).
 * Route: warehouse.stockcard.*
 */
class WsStockCardController extends WsBaseController
{
    // ==============================================================
    // Kartu Stock — halaman terpisah per produk (bukan tab inventory).
    // Route: warehouse.stockcard.* — Tanggal | Batch No | Keterangan | Masuk | Keluar | Sisa
    // ==============================================================

    /** Daftar produk untuk dipilih kartunya. */
    public function cardIndex(Request $request)
    {
        if (! $this->hasAnySalesAccess()) {
            abort(403, 'Anda tidak memiliki hak akses ke Kartu Stock.');
        }

        $search = trim((string) $request->get('search', ''));
        // Selalu muat SEMUA produk (tanpa pagination & tanpa filter server):
        // filter murni client-side (live search) supaya mengosongkan search
        // selalu mengembalikan seluruh daftar, bukan subset hasil filter lama.
        $products = Product::active()
            ->orderByRaw('LOWER(COALESCE(NULLIF(TRIM(product_name_clean), ""), product_name))')
            ->get();

        return view('users.warehouse.stock-card.index', [
            'title' => 'Kartu Stock',
            'products' => $products,
            'search' => $search,
            'canManage' => $this->canManageStock(),
        ]);
    }

    /** Detail kartu satu produk + form Masuk/Keluar langsung tervalidasi. */
    public function cardShow(Request $request, Product $product)
    {
        if (! $this->hasAnySalesAccess()) {
            abort(403, 'Anda tidak memiliki hak akses ke Kartu Stock.');
        }

        $viewData = array_merge($this->buildCardData($request, $product), [
            'title' => 'Kartu Stock — '.($product->product_name_clean ?: $product->product_name),
            'canManage' => $this->canManageStock(),
            'embed' => $request->boolean('embed'),
        ]);

        // Mode embed: isi popup modal (tanpa layout).
        if ($request->boolean('embed')) {
            return view('users.warehouse.stock-card.detail', $viewData);
        }

        return view('users.warehouse.stock-card.show', $viewData);
    }

    /** Cetak browser isi dokumen export yang sama (bukan halaman kartu). */
    public function cardPrint(Request $request, Product $product)
    {
        if (! $this->hasAnySalesAccess()) {
            abort(403, 'Anda tidak memiliki hak akses ke Kartu Stock.');
        }

        $data = $this->buildCardData($request, $product);

        // Logo sebagai data-URI agar tampil di browser (path lokal tak bisa dibaca browser).
        $logoDataUri = null;
        $logoPath = public_path('images/logo-kop.png');
        if (is_file($logoPath)) {
            $logoDataUri = 'data:'.(mime_content_type($logoPath) ?: 'image/png').';base64,'.base64_encode((string) file_get_contents($logoPath));
        }

        return view('pdf.warehouse.card', array_merge($data, [
            'printedBy' => Auth::user()->name ?? 'System',
            'tipeBarang' => '-',
            'noAkdAkl' => '-',
            'noKartu' => 'KS-'.str_pad((string) $product->id, 5, '0', STR_PAD_LEFT),
            'forPrint' => true,
            'logoDataUri' => $logoDataUri,
        ]));
    }

    /** Export PDF Kartu Stock satu produk (periode From s.d. To). */
    public function cardExportPdf(Request $request, Product $product)
    {
        if (! $this->hasAnySalesAccess()) {
            abort(403, 'Anda tidak memiliki hak akses ke Kartu Stock.');
        }

        $data = $this->buildCardData($request, $product);

        $pdf = Pdf::loadView('pdf.warehouse.card', array_merge($data, [
            'printedBy' => Auth::user()->name ?? 'System',
            'tipeBarang' => '-', // TODO: kolom tipe_barang sendiri (menyusul)
            'noAkdAkl' => '-',
            'noKartu' => 'KS-'.str_pad((string) $product->id, 5, '0', STR_PAD_LEFT),
        ]))->setPaper('a4', 'portrait');

        $filename = 'Kartu_Stock_'.($product->product_code ?? $product->id).'_'.date('Ymd_His').'.pdf';

        return $pdf->download($filename);
    }

    // buildCardData() pindah ke WsBaseController (dipakai juga tab Kartu di WsMutationController).

    /**
     * Input transaksi Barang Masuk / Keluar langsung dari popup Kartu Stock.
     * Menggunakan StockMoveService + WsMutation agar integrasi audit & sinkronisasi stok sempurna.
     */
    public function cardMoveStore(Request $request, Product $product)
    {
        if (! $this->canManageStock()) {
            abort(403, 'Hanya pengelola stok yang bisa mencatat transaksi barang.');
        }

        $validated = $request->validate([
            'arah' => ['required', 'in:masuk,keluar'],
            'tanggal' => ['required', 'date', 'before_or_equal:today'],
            'qty' => ['required', 'numeric', 'min:0.01'],
            'batch_no' => ['nullable', 'string', 'max:100'],
            'expiry_date' => ['nullable', 'date'],
            'purpose' => ['nullable', 'string', 'max:50'],
            'partner' => ['nullable', 'string', 'max:150'],
            'doc_no' => ['nullable', 'string', 'max:100'],
            'po_no' => ['nullable', 'string', 'max:100'],
            'keterangan' => ['nullable', 'string', 'max:1000'],
        ], [
            'arah.required' => 'Arah transaksi harus dipilih (masuk atau keluar).',
            'tanggal.required' => 'Tanggal transaksi wajib diisi.',
            'tanggal.before_or_equal' => 'Tanggal transaksi tidak boleh melebihi hari ini.',
            'qty.required' => 'Jumlah Qty wajib diisi.',
            'qty.min' => 'Jumlah Qty minimal 0.01.',
        ]);

        $arah = $validated['arah'];
        $direction = $arah === 'masuk' ? WsMutation::DIRECTION_IN : WsMutation::DIRECTION_OUT;
        $qty = (float) $validated['qty'];
        $batch = trim((string) ($validated['batch_no'] ?? ''));
        $ed = ! empty($validated['expiry_date']) ? $validated['expiry_date'] : null;
        $docNo = trim((string) ($validated['doc_no'] ?? ''));
        $poNo = trim((string) ($validated['po_no'] ?? ''));
        $partnerName = trim((string) ($validated['partner'] ?? ''));
        $note = trim((string) ($validated['keterangan'] ?? ''));

        // Tentukan purpose default jika kosong
        $rawPurpose = trim((string) ($validated['purpose'] ?? ''));
        if ($rawPurpose === '') {
            $purpose = $arah === 'masuk' ? WsMutation::PURPOSE_PURCHASE : WsMutation::PURPOSE_SALE;
        } else {
            $purpose = $rawPurpose;
        }

        // Buat reason yang serasi dengan WsMutationController
        $reason = WsMutationController::buildMutationReason(
            $arah,
            $purpose,
            $partnerName !== '' ? $partnerName : null,
            $validated['tanggal'],
            $docNo !== '' ? $docNo : null,
            $note !== '' ? $note : null
        );

        $customerId = null;
        $supplierId = null;
        $shipAddress = null;
        if ($partnerName !== '') {
            if ($arah === 'keluar') {
                $cust = \App\Models\Customer::whereRaw('LOWER(customer_name) = ?', [mb_strtolower($partnerName)])->first();
                if ($cust) {
                    $customerId = $cust->id;
                    $shipAddress = $cust->address ?? null;
                }
            } else {
                $supp = \App\Models\Supplier::whereRaw('LOWER(supplier_name) = ?', [mb_strtolower($partnerName)])->first();
                if ($supp) {
                    $supplierId = $supp->id;
                    $shipAddress = $supp->address ?? null;
                }
            }
        }

        $svc = app(StockMoveService::class);
        try {
            DB::transaction(function () use ($svc, $product, $validated, $arah, $direction, $qty, $batch, $ed, $docNo, $poNo, $purpose, $partnerName, $note, $reason, $customerId, $supplierId, $shipAddress) {
                // 1. Dokumen parent mutasi
                $doc = WsMutation::create([
                    'doc_no' => $docNo !== '' ? $docNo : null,
                    'direction' => $direction,
                    'mutation_date' => $validated['tanggal'],
                    'customer_id' => $customerId,
                    'supplier_id' => $supplierId,
                    'ship_address' => $shipAddress,
                    'po_no' => $poNo !== '' ? $poNo : null,
                    'purpose' => $purpose,
                    'source' => WsMutation::SOURCE_MANUAL,
                    'status' => WsMutation::STATUS_POSTED,
                    'notes' => $note !== '' ? $note : ($partnerName !== '' ? $partnerName : null),
                    'created_by' => Auth::id(),
                ]);

                // 2. Stock Move record
                $move = $svc->createMove([
                    'product_id' => $product->id,
                    'qty' => $qty,
                    'type' => $arah === 'masuk' ? StockMove::TYPE_RECEIPT : StockMove::TYPE_DELIVERY,
                    'to_location' => ($arah === 'keluar' && $purpose === WsMutation::PURPOSE_RETURN) ? 'Vendor' : null,
                    'batch_no' => $batch !== '' ? $batch : null,
                    'doc_no' => $docNo !== '' ? $docNo : null,
                    'partner' => $partnerName !== '' ? $partnerName : null,
                    'po_no' => $poNo !== '' ? $poNo : null,
                    'purpose' => $purpose,
                    'expiry_date' => $ed,
                    'occurred_at' => $validated['tanggal'],
                    'reason' => $reason,
                    'ref_type' => 'ws_mutation',
                    'ref_id' => $doc->id,
                ], Auth::user());

                // 3. Terapkan stok secara langsung (perbolehkan stok negatif fisik)
                $svc->validateMove($move, Auth::user(), true);
            });
        } catch (\Throwable $e) {
            $msg = 'Gagal mencatat mutasi: ' . $e->getMessage();
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return back()->with('error', $msg)->withInput();
        }

        $product->refresh();
        $stockAwalInfo = $this->resolveStockAwalSingle($product->id);
        $labelArah = $arah === 'masuk' ? 'Barang Masuk' : 'Barang Keluar';
        $satuan = $product->unit ?: ($product->fill_unit ?: 'Pcs');
        $msg = "{$labelArah} ({$qty} {$satuan}) berhasil dicatat. Stok kini: {$product->stock} {$satuan}.";

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $msg,
                'stock' => (float) $product->stock,
                'stock_awal_opname' => (float) ($stockAwalInfo['stok_awal'] ?? 0.0),
                'product_id' => $product->id,
            ]);
        }

        return back()->with('success', $msg);
    }

    /**
     * Edit baris pencatatan Kartu Stock (semua kolom kecuali Sisa).
     * - Keterangan/Batch saja berubah -> update langsung (tanpa efek stok).
     * - Qty/Tanggal berubah -> reversal + mutasi koreksi tervalidasi (jejak audit utuh).
     */
    public function cardMoveUpdate(Request $request, StockMove $move)
    {
        if (! $this->canManageStock()) {
            abort(403, 'Hanya pengelola stok yang bisa edit Kartu Stock.');
        }
        // Baris opname utuh milik alur Opname (koreksi via Catat → Selesaikan).
        if (str_starts_with((string) $move->reason, 'Opname:') || str_starts_with((string) $move->reason, 'Opname (')) {
            $msg = 'Baris opname hanya bisa dikoreksi lewat menu Catat Opname.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }

            return back()->with('error', $msg);
        }
        if (str_starts_with((string) $move->reason, 'Perubahan Stok Awal') || $move->ref_type === 'master_stock' || $move->ref_type === 'master_stock_edit') {
            $msg = 'Stok awal produk dikelola melalui menu Data Product.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }

            return back()->with('error', $msg);
        }
        if ($move->status !== StockMove::STATUS_DONE) {
            $msg = 'Hanya baris berstatus selesai yang bisa diedit.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }

            return back()->with('error', $msg);
        }

        $validated = $request->validate([
            'tanggal' => ['required', 'date', 'before_or_equal:today'],
            'qty' => ['required', 'numeric', 'not_in:0'],
            'batch_no' => ['nullable', 'string', 'max:100'],
            'keterangan' => ['required', 'string', 'max:1000'],
        ]);

        $inputQty = abs((float) $validated['qty']);
        $qtyChanged = abs((float) $move->qty - $inputQty) >= 0.005;
        $dateChanged = Carbon::parse($move->created_at)->toDateString() !== $validated['tanggal'];

        $svc = app(StockMoveService::class);
        try {
            if (! $qtyChanged && ! $dateChanged) {
                $move->update([
                    'batch_no' => trim((string) ($validated['batch_no'] ?? '')) ?: null,
                    'reason' => $validated['keterangan'],
                ]);
                $okMsg = "Baris #{$move->id} diperbarui (tanpa perubahan stok).";
            } else {
                $okMsg = DB::transaction(function () use ($svc, $move, $validated, $inputQty) {
                    $user = Auth::user();
                    $svc->reverseMove($move, $user, "Koreksi baris #{$move->id} ({$move->reason})");
                    $move->update(['status' => StockMove::STATUS_CANCELLED]);
                    $baru = $svc->createMove([
                        'product_id' => $move->product_id,
                        'qty' => $inputQty,
                        'type' => $move->type,
                        'from_location' => $move->from_location,
                        'to_location' => $move->to_location,
                        'batch_no' => trim((string) ($validated['batch_no'] ?? '')) ?: null,
                        'doc_no' => $move->doc_no,
                        'partner' => $move->partner,
                        'po_no' => $move->po_no,
                        'purpose' => $move->purpose,
                        'ship_address' => $move->ship_address,
                        'expedition' => $move->expedition,
                        'expiry_date' => $move->expiry_date?->toDateString(),
                        'occurred_at' => $validated['tanggal'],
                        'reason' => $validated['keterangan'],
                        'ref_type' => $move->ref_type ?: 'stock_move_correction',
                        'ref_id' => $move->ref_id ?: $move->id,
                    ], $user);
                    $svc->validateMove($baru, $user, true);
                    $this->refreshMutationStatus($move);

                    return "Baris #{$move->id} dikoreksi via reversal + mutasi baru #{$baru->id}.";
                });
            }
        } catch (\Throwable $e) {
            report($e);
            $errMsg = $e instanceof \InvalidArgumentException ? $e->getMessage() : 'Terjadi kesalahan sistem. Coba lagi atau hubungi admin.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $errMsg], 422);
            }

            return back()->with('error', $errMsg)->withInput();
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => $okMsg]);
        }

        return back()->with('success', $okMsg);
    }

    /**
     * Hapus baris pencatatan = reversal (stok kembali, jejak audit tetap ada).
     * Baris asli ditandai [dibatalkan] agar terbaca di tabel.
     * Khusus baris opname: record StockOpname terkait ikut dihapus agar tidak
     * lagi dipakai sebagai saldo awal maupun muncul di rekap opname.
     */
    public function cardMoveDestroy(Request $request, StockMove $move)
    {
        if (! $this->canManageStock()) {
            abort(403, 'Hanya pengelola stok yang bisa hapus baris Kartu Stock.');
        }
        // Idempoten: baris yang sudah dibatalkan tidak bisa dibatalkan lagi.
        if ($move->status === StockMove::STATUS_CANCELLED || str_contains((string) $move->reason, '[dibatalkan]')) {
            $msg = "Baris #{$move->id} sudah dibatalkan sebelumnya.";
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }

            return back()->with('error', $msg);
        }
        if ($move->status !== StockMove::STATUS_DONE) {
            $msg = 'Hanya baris berstatus selesai yang bisa dihapus.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }

            return back()->with('error', $msg);
        }

        $svc = app(StockMoveService::class);
        try {
            [$lawan, $opnameDeleted] = DB::transaction(function () use ($svc, $move) {
                $lawan = $svc->reverseMove($move, Auth::user(), "Hapus baris #{$move->id} ({$move->reason})");
                $move->update(['status' => StockMove::STATUS_CANCELLED]);
                $this->refreshMutationStatus($move);

                return [$lawan, $this->purgeLinkedOpname($move)];
            });
            $okMsg = "Baris #{$move->id} dibatalkan via reversal #{$lawan->id}. Stok dikembalikan.";
            if ($opnameDeleted > 0) {
                $okMsg .= ' Riwayat opname terkait juga dihapus.';
            }
        } catch (\Throwable $e) {
            report($e);
            $errMsg = $e instanceof \InvalidArgumentException ? $e->getMessage() : 'Terjadi kesalahan sistem. Coba lagi atau hubungi admin.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $errMsg], 422);
            }

            return back()->with('error', $errMsg);
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => $okMsg]);
        }

        return back()->with('success', $okMsg);
    }

    /**
     * Hapus baris StockOpname milik move opname yang sedang dihapus.
     * Sumber utama relasi adalah stock_move_id; data lama tanpa tautan dicari
     * lewat produk + periode move agar tetap bisa dibersihkan dari Kartu Stock.
     */
    private function purgeLinkedOpname(StockMove $move): int
    {
        if (! Schema::hasTable('stock_opnames')) {
            return 0;
        }

        $linked = StockOpname::where('stock_move_id', $move->id)->delete();
        if ($linked > 0) {
            return $linked;
        }

        if (str_starts_with((string) $move->reason, 'Opname')) {
            $period = Carbon::parse($move->created_at)->format('Y-m');

            return StockOpname::where('product_id', $move->product_id)
                ->where('period', $period)
                ->where('status', StockOpname::STATUS_DONE)
                ->whereNull('stock_move_id')
                ->delete();
        }

        // Jika move ini adalah pencatatan stok awal produk / master stock, hapus opname awal periode tersebut jika ada
        if ($move->ref_type === 'master_stock' || $move->ref_type === 'master_stock_edit' || str_starts_with((string) $move->reason, 'Stok awal') || str_starts_with((string) $move->reason, 'Perubahan Stok Awal')) {
            $period = Carbon::parse($move->created_at)->format('Y-m');

            return StockOpname::where('product_id', $move->product_id)
                ->where('period', $period)
                ->where('note', 'like', '%stok awal%')
                ->delete();
        }

        return 0;
    }
}

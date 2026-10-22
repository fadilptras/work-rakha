<?php

namespace App\Http\Controllers\Warehouse;

use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\StockMove;
use App\Models\StockOpname;
use App\Models\User;
use App\Models\WsMutation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Input mutasi manual. Tab: kelola | keluar | masuk | kartu.
 */
class WsMutationController extends WsBaseController
{

    // Input langsung tervalidasi via StockMoveService.

    public function pencatatanIndex(Request $request)
    {
        if (!$this->hasAnySalesAccess()) {
            abort(403, 'Anda tidak memiliki hak akses ke halaman Pencatatan Barang.');
        }

        $base = $this->applyMoveFilters($this->baseMoveQuery(), $request);

        // Tab aktif: kelola | masuk | kartu. Input keluar pindah ke Surat Jalan.
        if ($request->get('tab') === 'keluar') {
            return redirect()->route('warehouse.sj.index', ['tab' => 'keluar']);
        }
        $tab = in_array($request->get('tab'), ['masuk', 'kartu'], true) ? $request->get('tab') : 'kelola';

        $isMasuk = fn ($m) => $m->type === StockMove::TYPE_RECEIPT
            || ($m->type === StockMove::TYPE_ADJUSTMENT
                && $m->to_location === StockMove::LOC_STOCK
                && $m->from_location !== StockMove::LOC_STOCK);

        // Agregat, ringkasan, dan tabel hanya dipakai tab Kelola Data.
        // Tab Masuk/Keluar murni form input; tab Kartu punya query sendiri.
        $qtyMasuk = 0; $qtyKeluar = 0; $trxMasuk = 0; $trxKeluar = 0;
        $ringkasan = collect();
        $kelolaMoves = collect();
        $kartuData = null;
        $kartuProduct = null;
        if ($tab === 'kelola') {
            [$qtyMasuk, $qtyKeluar, $trxMasuk, $trxKeluar, $perBarang] = $this->moveStats($base, $isMasuk);

            $kq = (clone $base);
            if (in_array($request->get('kelola_arah'), ['masuk', 'keluar'], true)) {
                $this->applyArahScope($kq, $request->get('kelola_arah'));
            }
            $kelolaMoves = $kq->orderByDesc('stock_moves.created_at')->orderByDesc('stock_moves.id')
                ->paginate(30, ['stock_moves.*', 'd.id as d_id', 'd.doc_no as d_doc', 'd.purpose as d_purpose', 'd.ship_address as d_ship', 'd.expedition as d_exped', 'd.po_no as d_po', 'cu.customer_name as cu_name', 'su.supplier_name as su_name', 'd.source as d_source', 'd.status as d_status'], 'kelolaPage')->withQueryString();
        }

        // Tab Kartu Stock: bangun data kartu satu produk (pakai ulang logika kartu).
        if ($tab === 'kartu' && $request->filled('kartu_product')) {
            $kartuProduct = Product::find($request->input('kartu_product'));
            if ($kartuProduct) {
                $cardReq = new Request([
                    'from' => $request->get('kartu_from', today()->startOfMonth()->toDateString()),
                    'to' => $request->get('kartu_to', today()->toDateString()),
                ]);
                $kartuData = array_merge(
                    $this->buildCardData($cardReq, $kartuProduct),
                    ['canManage' => $this->canManageStock(), 'embed' => true]
                );
            }
        }

        if ($tab === 'kelola') {
            // Ringkasan per barang: gabung agregat filter + info produk (stok kini).
            if (!empty($perBarang)) {
            $products = Product::whereIn('id', array_keys($perBarang))->get()->keyBy('id');
            foreach ($perBarang as $pid => $ag) {
                $p = $products[$pid] ?? null;
                $ringkasan->push([
                    'id' => $pid,
                    'kode' => $p->product_code ?? '-',
                    'nama' => ($p->product_name_clean ?: $p->product_name) ?? 'Produk Terhapus',
                    'satuan' => $p->unit ?? '-',
                    'masuk' => $ag['masuk'],
                    'keluar' => $ag['keluar'],
                    'net' => round($ag['masuk'] - $ag['keluar'], 2),
                    'trx' => $ag['trx'],
                    'stok_kini' => $p->stock ?? 0,
                ]);
            }
            $ringkasan = $ringkasan->sortByDesc('trx')->values();
            }
        }

        $productOptions = Product::active()
            ->orderByRaw('LOWER(COALESCE(NULLIF(TRIM(product_name_clean), ""), product_name))')
            ->get(['id', 'product_code', 'product_name', 'product_name_clean', 'unit', 'stock']);

        $tahunAda = StockMove::selectRaw('DISTINCT YEAR(created_at) as tahun')
            ->orderByDesc('tahun')->pluck('tahun')->filter()->values();

        $productOptionsJson = $productOptions->map(fn ($p) => [
            'id' => $p->id,
            'nama' => ($p->product_name_clean ?: $p->product_name),
            'kode' => $p->product_code,
            'stok' => $p->stock,
            'unit' => $p->unit,
        ])->values()->toJson();

        // Dropdown master untuk header dokumen ala surat jalan.
        $suppliers = Supplier::active()->orderBy('supplier_name')->get(['id', 'supplier_name']);
        $customers = Customer::active()->orderBy('customer_name')->get(['id', 'customer_name']);
        $supplierAddressMap = Supplier::active()->get(['id', 'supplier_address'])
            ->mapWithKeys(fn ($s) => [$s->id => $s->displayAddress()])->all();
        $customerAddressMap = Customer::active()->get(['id', 'customer_address'])
            ->mapWithKeys(fn ($c) => [$c->id => $c->displayAddress()])->all();
        $expeditions = $this->expeditionOptions();
        $psList = $this->psOptions();

        return view('users.warehouse.pencatatan', [
            'title' => 'Pencatatan Barang',
            'tab' => $tab,
            'sjPreviewNo' => app(\App\Services\DocumentNumberService::class)->previewSj(),
            'kelolaMoves' => $kelolaMoves,
            'kartuData' => $kartuData,
            'kartuProduct' => $kartuProduct,
            'ringkasan' => $ringkasan,
            'qtyMasuk' => $qtyMasuk,
            'qtyKeluar' => $qtyKeluar,
            'trxMasuk' => $trxMasuk,
            'trxKeluar' => $trxKeluar,
            'productOptions' => $productOptions,
            'productOptionsJson' => $productOptionsJson,
            'suppliers' => $suppliers,
            'customers' => $customers,
            'supplierAddressMap' => $supplierAddressMap,
            'customerAddressMap' => $customerAddressMap,
            'expeditions' => $expeditions,
            'psList' => $psList,
            'purposes' => WsMutation::PURPOSES,
            'tahunAda' => $tahunAda,
            'canManage' => $this->canManageStock(),
            'isMasuk' => $isMasuk,
        ]);
    }

    /** Daftar nama PS: karyawan jabatan marketing & product specialist. */
    protected function psOptions(): array
    {
        try {
            return User::whereNotNull('name')->where('name', '!=', '')
                ->where(function ($q) {
                    $q->where('jabatan', 'like', '%marketing%')
                        ->orWhere('jabatan', 'like', '%product spea%')
                        ->orWhere('jabatan', 'like', '%product spec%');
                })
                ->orderBy('name')->pluck('name')->all();
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Simpan 1 dokumen mutasi + N baris sekaligus, langsung tervalidasi.
     * 1 arah per dokumen; gagal satu baris = seluruh dokumen rollback.
     * Form Ops (asal=ops) mandiri: No. SJ input manual + kembali ke tab Ops.
     * Form SJ / Pencatatan: keluar nomor otomatis + mendarat di arsip SJ.
     */
    public function pencatatanStore(Request $request)
    {
        if (!$this->canManageStock()) {
            abort(403, 'Hanya pengelola stok yang bisa mencatat barang.');
        }

        $validated = $request->validate([
            'tanggal' => ['required', 'date', 'before_or_equal:today'],
            'asal' => ['nullable', 'string', 'max:10'],
            'doc_no' => ['nullable', 'string', 'max:100'],
            'po_no' => ['nullable', 'string', 'max:100'],
            'purpose' => ['nullable', 'string', 'max:50'],
            'ps_name' => ['nullable', 'string', 'max:100'],
            'customer_id' => ['nullable', 'exists:customers,id'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'ship_address' => ['nullable', 'string', 'max:2000'],
            'expedition' => ['nullable', 'string', 'max:100'],
            'product_id' => ['required', 'array', 'min:1'],
            'product_id.*' => ['required', 'exists:products,id'],
            'arah' => ['required', 'array', 'min:1'],
            'arah.*' => ['required', 'in:masuk,keluar'],
            'qty' => ['required', 'array', 'min:1'],
            'qty.*' => ['required', 'numeric', 'min:0.01'],
            'batch_no' => ['nullable', 'array'],
            'batch_no.*' => ['nullable', 'string', 'max:100'],
            'expiry_date' => ['nullable', 'array'],
            'expiry_date.*' => ['nullable', 'date'],
            'keterangan' => ['nullable', 'array'],
            'keterangan.*' => ['nullable', 'string', 'max:1000'],
        ]);

        // 1 dokumen = 1 arah.
        $arahList = array_values(array_unique(array_filter($validated['arah'] ?? [])));
        if (count($arahList) !== 1 || !in_array($arahList[0], ['masuk', 'keluar'], true)) {
            return back()->with('error', 'Satu dokumen hanya untuk satu arah (masuk atau keluar).')->withInput();
        }
        $arah = $arahList[0];
        $direction = $arah === 'masuk' ? WsMutation::DIRECTION_IN : WsMutation::DIRECTION_OUT;
        // Penanda form Ops (mandiri) vs form SJ/Pencatatan (terhubung arsip SJ).
        $fromOps = ($validated['asal'] ?? '') === 'ops';

        // No. dokumen: form Ops opsional (boleh kosong).
        // Form keluar non-Ops tetap nomor otomatis atomik "SJ.YYYYMMDD.NOURUT".
        if ($arah === 'keluar' && !$fromOps) {
            $docNo = app(\App\Services\DocumentNumberService::class)->nextSj();
        } else {
            $docNo = trim((string) ($validated['doc_no'] ?? ''));
        }
        if ($docNo !== '' && WsMutation::where('direction', $direction)->where('doc_no', $docNo)->exists()) {
            return back()->with('error', "No. SJ '{$docNo}' sudah dipakai dokumen lain.")->withInput();
        }
        $docNo = $docNo !== '' ? $docNo : null;

        // Aturan pasangan per keterangan: dropdown hanya saran, boleh ketik sendiri.
        // Nilai persis 'Retur Supplier' & 'Internal' tetap punya aturan pasangan khusus.
        $purpose = $arah === 'keluar' ? trim((string) ($validated['purpose'] ?? '')) : null;
        if ($arah === 'keluar' && ($purpose === null || $purpose === '')) {
            return back()->with('error', 'Keterangan keperluan wajib diisi.')->withInput();
        }
        $customerId = $validated['customer_id'] ?? null;
        $supplierId = $validated['supplier_id'] ?? null;
        if ($arah === 'masuk' && !$supplierId) {
            return back()->with('error', 'Barang masuk wajib pilih supplier.')->withInput();
        }
        if ($arah === 'keluar' && $purpose === 'Retur Supplier' && !$supplierId) {
            return back()->with('error', 'Retur Supplier wajib pilih supplier.')->withInput();
        }
        if ($arah === 'keluar' && $purpose !== 'Retur Supplier' && $purpose !== 'Internal' && $purpose !== 'Sample' && !$customerId) {
            return back()->with('error', 'Barang keluar wajib pilih customer (kecuali Sample/Internal/Retur).')->withInput();
        }
        // Sample: pihak = nama PS (boleh tanpa customer).
        $psName = ($arah === 'keluar' && $purpose === 'Sample') ? trim((string) ($validated['ps_name'] ?? '')) : '';
        if ($arah === 'keluar' && $purpose === 'Sample' && !$customerId && $psName === '') {
            return back()->with('error', 'Sample wajib pilih nama PS.')->withInput();
        }
        if ($arah === 'keluar' && $purpose === 'Internal') {
            $customerId = null;
            $supplierId = null;
        }
        if ($arah === 'masuk') {
            $customerId = null;
        }

        $poNo = trim((string) ($validated['po_no'] ?? ''));
        $ship = trim((string) ($validated['ship_address'] ?? ''));
        $expedition = trim((string) ($validated['expedition'] ?? ''));

        $svc = app(\App\Services\StockMoveService::class);
        try {
            $count = DB::transaction(function () use ($svc, $validated, $arah, $direction, $docNo, $purpose, $psName, $customerId, $supplierId, $poNo, $ship, $expedition) {
                $doc = WsMutation::create([
                    'doc_no' => $docNo,
                    'direction' => $direction,
                    'mutation_date' => $validated['tanggal'],
                    'customer_id' => $customerId ?: null,
                    'supplier_id' => $supplierId ?: null,
                    'ship_address' => $ship !== '' ? $ship : null,
                    'po_no' => $poNo !== '' ? $poNo : null,
                    'purpose' => $purpose !== '' ? $purpose : null,
                    'expedition' => $expedition !== '' ? $expedition : null,
                    'status' => WsMutation::STATUS_POSTED,
                    'source' => WsMutation::SOURCE_MANUAL,
                    'created_by' => Auth::id(),
                ]);

                $n = 0;
                foreach ($validated['product_id'] as $i => $pid) {
                    $qty = (float) ($validated['qty'][$i] ?? 0);
                    $batch = trim((string) ($validated['batch_no'][$i] ?? ''));
                    $ed = trim((string) ($validated['expiry_date'][$i] ?? ''));
                    $ket = trim((string) ($validated['keterangan'][$i] ?? ''));
                    if ($qty <= 0) continue;
                    if ($arah === 'masuk' && $batch === '') {
                        throw new \InvalidArgumentException("Baris #" . ($i + 1) . ": Batch No. wajib untuk barang masuk.");
                    }
                    if ($arah === 'masuk' && $ed === '') {
                        throw new \InvalidArgumentException("Baris #" . ($i + 1) . ": Expired Date wajib untuk barang masuk.");
                    }
                    $reason = $ket !== '' ? $ket : ('Pencatatan ' . $arah . ' ' . $validated['tanggal']);
                    $reason = ($docNo ? "[{$docNo}] " : '') . $reason;
                    $move = $svc->createMove([
                        'product_id' => $pid,
                        'qty' => $qty,
                        'type' => $arah === 'masuk' ? StockMove::TYPE_RECEIPT : StockMove::TYPE_DELIVERY,
                        'to_location' => ($arah === 'keluar' && $purpose === 'Retur Supplier') ? 'Vendor' : null,
                        'batch_no' => $batch !== '' ? $batch : null,
                        'doc_no' => $docNo,
                        'partner' => $psName !== '' ? $psName : null,
                        'po_no' => $poNo !== '' ? $poNo : null,
                        'purpose' => ($arah === 'keluar' && $purpose !== '') ? $purpose : null,
                        'ship_address' => $ship !== '' ? $ship : null,
                        'expedition' => $expedition !== '' ? $expedition : null,
                        'expiry_date' => $ed !== '' ? $ed : null,
                        'occurred_at' => $validated['tanggal'],
                        'reason' => $reason,
                        'ref_type' => 'ws_mutation',
                        'ref_id' => $doc->id,
                    ], Auth::user());
                    $svc->validateMove($move, Auth::user());
                    $n++;
                }
                if ($n === 0) {
                    throw new \InvalidArgumentException('Tidak ada baris valid untuk disimpan.');
                }

                return $n;
            });
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal menyimpan dokumen: ' . $e->getMessage())->withInput();
        }

            $docLabel = $docNo ?: 'tanpa No. SJ';
            $msg = "Dokumen {$docLabel} tersimpan ({$count} baris). Stok & kartu stock ter-update.";
            // Form SJ/Pencatatan keluar -> mendarat di tab arsip Surat Jalan;
            // form Ops (keluar/masuk) -> kembali ke tab Ops masing-masing.
            if ($arah === 'keluar' && !$fromOps) {
                return redirect()->route('warehouse.sj.index')->with('success', $msg);
            }

            return redirect()->route('warehouse.index', ['tab' => $arah])->with('success', $msg);
    }

    /**
     * Edit 1 baris: qty/tanggal/ED via reversal + koreksi (terhubung dokumen
     * yang sama bila ada); header dokumen (SJ/alamat/ekspedisi/PO) ikut
     * diperbarui ke seluruh baris dokumennya.
     */
    public function pencatatanUpdate(Request $request, StockMove $move)
    {
        if (!$this->canManageStock()) {
            abort(403, 'Hanya pengelola stok yang bisa edit pencatatan.');
        }
        // Baris opname utuh milik alur Opname (koreksi via Catat → Selesaikan).
        if (str_starts_with((string) $move->reason, 'Opname:')) {
            return back()->with('error', 'Baris opname hanya bisa dikoreksi lewat halaman Catat Opname.');
        }
        if ($move->status !== StockMove::STATUS_DONE) {
            return back()->with('error', 'Hanya baris berstatus selesai yang bisa diedit.');
        }

        $validated = $request->validate([
            'tanggal' => ['required', 'date', 'before_or_equal:today'],
            'qty' => ['required', 'numeric', 'min:0.01'],
            'batch_no' => ['nullable', 'string', 'max:100'],
            'expiry_date' => ['nullable', 'date'],
            'doc_no' => ['nullable', 'string', 'max:100'],
            'po_no' => ['nullable', 'string', 'max:100'],
            'ship_address' => ['nullable', 'string', 'max:2000'],
            'expedition' => ['nullable', 'string', 'max:100'],
            'keterangan' => ['required', 'string', 'max:1000'],
        ]);

        $doc = ($move->ref_type === 'ws_mutation' && $move->ref_id)
            ? WsMutation::find($move->ref_id) : null;

        $svc = app(\App\Services\StockMoveService::class);
        try {
            $qtyChanged = abs((float) $move->qty - (float) $validated['qty']) >= 0.005;
            $dateChanged = \Carbon\Carbon::parse($move->created_at)->toDateString() !== $validated['tanggal'];
            $edChanged = ($move->expiry_date?->toDateString() ?? '') !== ($validated['expiry_date'] ?? '');

            // Header dokumen: SJ unik per arah, berlaku untuk semua barisnya.
            $newDocNo = trim((string) ($validated['doc_no'] ?? ''));
            if ($doc && $newDocNo !== '' && $newDocNo !== $doc->doc_no) {
                if (WsMutation::where('direction', $doc->direction)->where('doc_no', $newDocNo)->where('id', '!=', $doc->id)->exists()) {
                    return back()->with('error', "No. SJ '{$newDocNo}' sudah dipakai dokumen lain.")->withInput();
                }
            }

            $msg = DB::transaction(function () use ($svc, $move, $validated, $doc, $newDocNo, $qtyChanged, $dateChanged, $edChanged) {
                $user = Auth::user();
                $oldDocNo = $doc ? $doc->doc_no : $move->doc_no;

                if ($doc) {
                    $doc->update([
                        'doc_no' => $newDocNo !== '' ? $newDocNo : $doc->doc_no,
                        'po_no' => $validated['po_no'] ?: null,
                        'ship_address' => trim((string) ($validated['ship_address'] ?? '')) !== '' ? trim((string) $validated['ship_address']) : null,
                        'expedition' => trim((string) ($validated['expedition'] ?? '')) !== '' ? trim((string) $validated['expedition']) : null,
                    ]);
                    // Propagasi snapshot header ke semua baris dokumen.
                    $siblings = StockMove::where('ref_type', 'ws_mutation')->where('ref_id', $doc->id)->get();
                    foreach ($siblings as $sib) {
                        $attrs = [
                            'doc_no' => $doc->fresh()->doc_no,
                            'po_no' => $doc->fresh()->po_no,
                            'ship_address' => $doc->fresh()->ship_address,
                            'expedition' => $doc->fresh()->expedition,
                        ];
                        if ($oldDocNo && str_starts_with((string) $sib->reason, "[{$oldDocNo}] ")) {
                            $attrs['reason'] = '[' . $doc->fresh()->doc_no . '] ' . substr((string) $sib->reason, strlen($oldDocNo) + 3);
                        }
                        $sib->update($attrs);
                    }
                    $move = $move->fresh();
                }

                if (!$qtyChanged && !$dateChanged && !$edChanged) {
                    $move->update([
                        'batch_no' => $validated['batch_no'] ?: null,
                        'reason' => $validated['keterangan'],
                    ]);

                    return "Baris #{$move->id} diperbarui (tanpa perubahan stok).";
                }

                $svc->reverseMove($move, $user, "Koreksi pencatatan #{$move->id}");
                $isDoc = $doc !== null;
                $baru = $svc->createMove([
                    'product_id' => $move->product_id,
                    'qty' => (float) $validated['qty'],
                    'type' => $move->type,
                    'from_location' => $move->from_location,
                    'to_location' => $move->to_location,
                    'batch_no' => $validated['batch_no'] ?: null,
                    'doc_no' => $isDoc ? $doc->fresh()->doc_no : ($validated['doc_no'] ?: $move->doc_no),
                    'po_no' => $isDoc ? $doc->fresh()->po_no : ($validated['po_no'] ?: null),
                    'purpose' => $move->purpose,
                    'ship_address' => $isDoc ? $doc->fresh()->ship_address : null,
                    'expedition' => $isDoc ? $doc->fresh()->expedition : null,
                    'expiry_date' => $validated['expiry_date'] ?: null,
                    'occurred_at' => $validated['tanggal'],
                    'reason' => $validated['keterangan'],
                    'ref_type' => $isDoc ? 'ws_mutation' : $move->ref_type,
                    'ref_id' => $isDoc ? $doc->id : $move->id,
                ], $user);
                $svc->validateMove($baru, $user);

                return "Baris #{$move->id} dikoreksi via reversal + mutasi baru #{$baru->id}.";
            });
        } catch (\Throwable $e) {
            report($e);
            $errMsg = $e instanceof \InvalidArgumentException ? $e->getMessage() : 'Terjadi kesalahan sistem. Coba lagi atau hubungi admin.';

            return back()->with('error', $errMsg)->withInput();
        }

        return back()->with('success', $msg);
    }

    /** Batch tersedia per produk (untuk picker batch + info ED di form keluar). */
    public function productBatches(Request $request)
    {
        if (!$this->hasAnySalesAccess()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }
        $request->validate(['product_id' => ['required', 'exists:products,id']]);
        $batches = ProductBatch::where('product_id', $request->input('product_id'))
            ->available()
            ->orderByRaw('expiry_date IS NULL, expiry_date ASC')
            ->get(['batch_no', 'qty_current', 'expiry_date'])
            ->map(fn ($b) => [
                'batch_no' => $b->batch_no,
                'qty' => (float) $b->qty_current,
                'expiry_date' => $b->expiry_date?->toDateString(),
                'expiry_label' => $b->expiry_date?->format('d M Y') ?? '-',
            ])->values();

        return response()->json(['success' => true, 'data' => $batches]);
    }

    /** Hapus 1 baris = reversal (stok kembali, jejak audit utuh). */
    public function pencatatanDestroy(Request $request, StockMove $move)
    {
        if (!$this->canManageStock()) {
            abort(403, 'Hanya pengelola stok yang bisa hapus pencatatan.');
        }
        // Baris opname utuh milik alur Opname (koreksi via Catat → Selesaikan).
        if (str_starts_with((string) $move->reason, 'Opname:')) {
            return back()->with('error', 'Baris opname hanya bisa dikoreksi lewat halaman Catat Opname.');
        }
        // Idempoten: baris yang sudah dibatalkan tidak bisa dibatalkan lagi.
        if (str_contains((string) $move->reason, '[dibatalkan]')) {
            return back()->with('error', "Baris #{$move->id} sudah dibatalkan sebelumnya.");
        }
        if ($move->status !== StockMove::STATUS_DONE) {
            return back()->with('error', 'Hanya baris berstatus selesai yang bisa dihapus.');
        }

        $svc = app(\App\Services\StockMoveService::class);
        try {
            $lawan = $svc->reverseMove($move, Auth::user(), "Hapus pencatatan #{$move->id} ({$move->reason})");
            if (!str_contains((string) $move->reason, '[dibatalkan]')) {
                $move->update(['reason' => trim($move->reason . ' [dibatalkan]')]);
            }
            $msg = "Baris #{$move->id} dibatalkan via reversal #{$lawan->id}. Stok dikembalikan.";
        } catch (\Throwable $e) {
            report($e);
            $errMsg = $e instanceof \InvalidArgumentException ? $e->getMessage() : 'Terjadi kesalahan sistem. Coba lagi atau hubungi admin.';

            return back()->with('error', $errMsg);
        }

        return back()->with('success', $msg);
    }

    /** Batalkan banyak baris sekaligus (reversal per baris, dipakai tab Kelola Data). */
    public function pencatatanBulkDestroy(Request $request)
    {
        if (!$this->canManageStock()) {
            abort(403, 'Hanya pengelola stok yang bisa hapus pencatatan.');
        }

        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['exists:stock_moves,id'],
        ]);

        $svc = app(\App\Services\StockMoveService::class);
        $ok = 0;
        $failures = [];
        foreach ($validated['ids'] as $id) {
            $move = StockMove::find($id);
            if (!$move || $move->status !== StockMove::STATUS_DONE) {
                $failures[] = "#{$id}: sudah bukan status selesai.";
                continue;
            }
            if (str_starts_with((string) $move->reason, 'Opname:')) {
                $failures[] = "#{$id}: baris opname hanya bisa dikoreksi lewat halaman Catat Opname.";
                continue;
            }
            if (str_contains((string) $move->reason, '[dibatalkan]')) {
                $failures[] = "#{$id}: sudah dibatalkan sebelumnya.";
                continue;
            }
            try {
                $lawan = $svc->reverseMove($move, Auth::user(), "Hapus pencatatan #{$move->id} ({$move->reason})");
                if (!str_contains((string) $move->reason, '[dibatalkan]')) {
                    $move->update(['reason' => trim($move->reason . ' [dibatalkan]')]);
                }
                $ok++;
            } catch (\Throwable $e) {
                report($e);
                $failures[] = "#{$id}: " . ($e instanceof \InvalidArgumentException ? $e->getMessage() : 'kesalahan sistem.');
            }
        }

        if ($ok === 0) {
            return back()->with('error', 'Tidak ada baris dibatalkan. ' . implode(' ', $failures));
        }
        $msg = "{$ok} baris dibatalkan via reversal, stok dikembalikan.";
        if (!empty($failures)) $msg .= ' Sebagian gagal: ' . implode(' ', $failures);

        return redirect()->route('warehouse.mutation.index', ['tab' => 'kelola'])
            ->with(empty($failures) ? 'success' : 'warning', $msg);
    }

    /**
     * Query dasar mutasi done + join dokumen & pihak (satu sumber layar + export).
     * Baris lama tanpa dokumen memakai snapshot kolom moves.
     */
    protected function baseMoveQuery()
    {
        return StockMove::with(['product', 'user'])
            ->where('stock_moves.status', StockMove::STATUS_DONE)
            ->whereIn('stock_moves.type', [
                StockMove::TYPE_RECEIPT,
                StockMove::TYPE_DELIVERY,
                StockMove::TYPE_ADJUSTMENT,
                StockMove::TYPE_SCRAP,
            ])
            ->leftJoin('ws_mutation as d', function ($j) {
                $j->on('d.id', '=', 'stock_moves.ref_id')
                    ->where('stock_moves.ref_type', '=', 'ws_mutation');
            })
            ->leftJoin('customers as cu', 'cu.id', '=', 'd.customer_id')
            ->leftJoin('suppliers as su', 'su.id', '=', 'd.supplier_id')
            ->addSelect(['stock_moves.*'])
            ->addSelect([
                'd.id as d_id', 'd.doc_no as d_doc', 'd.purpose as d_purpose',
                'd.ship_address as d_ship', 'd.expedition as d_exped',
                'd.po_no as d_po', 'cu.customer_name as cu_name', 'su.supplier_name as su_name',
                'd.source as d_source', 'd.status as d_status',
            ]);
    }

    /**
     * Rekap mutasi Ops tidak menampilkan baris hasil Stock Opname.
     * Baris opname ditandai prefix "Opname:" pada reason.
     */
    protected function excludeOpnameMoves($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('stock_moves.reason')
                ->orWhere('stock_moves.reason', 'not like', 'Opname:%');
        });
    }

    /**
     * Filter layar mutasi (tab kelola, tab Ops mutasi, export PDF).
     * Tanpa kelola_arah �?" panggil applyArahScope sendiri bila perlu.
     */
    protected function applyMoveFilters($query, Request $request)
    {
        if ($request->filled('search')) {
            $s = trim((string) $request->input('search'));
            $query->where(function ($q) use ($s) {
                $q->where('stock_moves.reason', 'like', "%{$s}%")
                    ->orWhere('stock_moves.batch_no', 'like', "%{$s}%")
                    ->orWhere('stock_moves.doc_no', 'like', "%{$s}%")
                    ->orWhere('stock_moves.partner', 'like', "%{$s}%")
                    ->orWhere('stock_moves.po_no', 'like', "%{$s}%")
                    ->orWhere('stock_moves.purpose', 'like', "%{$s}%")
                    ->orWhere('stock_moves.ship_address', 'like', "%{$s}%")
                    ->orWhere('stock_moves.expedition', 'like', "%{$s}%")
                    ->orWhere('cu.customer_name', 'like', "%{$s}%")
                    ->orWhere('su.supplier_name', 'like', "%{$s}%")
                    ->orWhereHas('product', fn ($pq) => $pq
                        ->where('product_name', 'like', "%{$s}%")
                        ->orWhere('product_code', 'like', "%{$s}%"));
            });
        }
        if ($request->filled('product_id')) {
            $query->where('stock_moves.product_id', $request->input('product_id'));
        }
        if ($request->filled('tanggal')) {
            $query->whereDate('stock_moves.created_at', $request->input('tanggal'));
        }
        if ($request->filled('bulan')) {
            $query->whereMonth('stock_moves.created_at', (int) $request->input('bulan'));
        }
        if ($request->filled('tahun')) {
            $query->whereYear('stock_moves.created_at', (int) $request->input('tahun'));
        }

        return $query;
    }

    /**
     * Agregat qty/trx masuk-keluar + rekap per produk.
     * @return array [qtyMasuk, qtyKeluar, trxMasuk, trxKeluar, perBarang]
     */
    protected function moveStats($query, callable $isMasuk): array
    {
        $qtyMasuk = 0;
        $qtyKeluar = 0;
        $trxMasuk = 0;
        $trxKeluar = 0;
        $perBarang = [];
        foreach ((clone $query)->get(['qty', 'type', 'from_location', 'to_location', 'product_id']) as $m) {
            $qv = (float) $m->qty;
            if ($isMasuk($m)) {
                $qtyMasuk += $qv;
                $trxMasuk++;
            } else {
                $qtyKeluar += $qv;
                $trxKeluar++;
            }
            $pid = $m->product_id;
            if (!isset($perBarang[$pid])) $perBarang[$pid] = ['masuk' => 0, 'keluar' => 0, 'trx' => 0];
            if ($isMasuk($m)) $perBarang[$pid]['masuk'] += $qv;
            else $perBarang[$pid]['keluar'] += $qv;
            $perBarang[$pid]['trx']++;
        }

        return [$qtyMasuk, $qtyKeluar, $trxMasuk, $trxKeluar, $perBarang];
    }

    // Halaman gabungan: produk | mutasi | keluar | masuk.

    public function warehouse(Request $request)
    {
        if (!$this->hasAnySalesAccess()) {
            abort(403, 'Anda tidak memiliki hak akses ke halaman Gudang.');
        }

        $tab = in_array($request->get('tab'), ['produk', 'mutasi', 'keluar', 'masuk'], true)
            ? $request->get('tab') : 'produk';
        $canManage = $this->canManageStock();

        $isMasuk = fn ($m) => $m->type === StockMove::TYPE_RECEIPT
            || ($m->type === StockMove::TYPE_ADJUSTMENT
                && $m->to_location === StockMove::LOC_STOCK
                && $m->from_location !== StockMove::LOC_STOCK);

        // ---- Tab Produk: daftar + stok jelas + forecast ----
        $products = collect();
        $kebutuhan = 'semua';
        $forecastBulan = '';
        $forecastDoi = 30;
        $forecastPct = 20;
        if ($tab === 'produk') {
            $pq = Product::active()
                ->orderByRaw('LOWER(COALESCE(NULLIF(TRIM(product_name_clean), ""), product_name))');
            if ($request->filled('q')) {
                $s = trim((string) $request->input('q'));
                $pq->where(fn ($w) => $w->where('product_name', 'like', "%{$s}%")
                    ->orWhere('product_code', 'like', "%{$s}%"));
            }
            // Tanpa pagination: seluruh SKU dimuat, scroll di tabel (tinggi tetap).
            $products = $pq->get();
            // Data forecast bulan berjalan (satu sumber via ForecastService).
            $forecastMapped = ['map' => [], 'bulan' => '', 'doi' => 30, 'percentage' => 20];
            try {
                $forecastMapped = app(\App\Services\ForecastService::class)->buildMapped(now()->year);
            } catch (\Throwable $e) {
                $forecastMapped = ['map' => [], 'bulan' => '', 'doi' => 30, 'percentage' => 20];
            }
            $forecastBulan = $forecastMapped['bulan'] ?? '';
            $forecastDoi = $forecastMapped['doi'] ?? 30;
            $forecastPct = $forecastMapped['percentage'] ?? 20;
            // Tempel forecast ke tiap produk (cocok product_name, fallback clean).
            $fcMap = $forecastMapped['map'] ?? [];
            $products->each(function ($p) use ($fcMap) {
                $key = strtolower(trim((string) $p->product_name));
                $alt = strtolower(trim((string) ($p->product_name_clean ?? '')));
                $p->forecast = $fcMap[$key] ?? ($alt !== '' ? ($fcMap[$alt] ?? null) : null);
            });
            // Filter kebutuhan: hanya SKU yang perlu di-order (order > 0).
            $kebutuhan = $request->get('butuh') === 'order' ? 'order' : 'semua';
            if ($kebutuhan === 'order') {
                $products = $products->filter(fn ($p) => $p->forecast && $p->forecast['order_qty'] > 0)->values();
            }
        }

        // ---- Tab Mutasi: filter + statistik + tabel ----
        $qtyMasuk = 0; $qtyKeluar = 0; $trxMasuk = 0; $trxKeluar = 0;
        $produkAktif = 0;
        $ringkasan = collect();
        $kelolaMoves = collect();
        if ($tab === 'mutasi') {
            // Data Stock Opname tidak ditampilkan di tabel Data Mutation Ops.
            $base = $this->applyMoveFilters($this->excludeOpnameMoves($this->baseMoveQuery()), $request);

            [$qtyMasuk, $qtyKeluar, $trxMasuk, $trxKeluar, $perBarang] = $this->moveStats($base, $isMasuk);
            $produkAktif = count($perBarang);

            $kq = (clone $base);
            if (in_array($request->get('kelola_arah'), ['masuk', 'keluar'], true)) {
                $this->applyArahScope($kq, $request->get('kelola_arah'));
            }
            // Tanpa pagination: semua baris dimuat, tabel scroll dengan tinggi tetap.
            $kelolaMoves = $kq->orderByDesc('stock_moves.created_at')->orderByDesc('stock_moves.id')
                ->get(['stock_moves.*', 'd.id as d_id', 'd.doc_no as d_doc', 'd.purpose as d_purpose', 'd.ship_address as d_ship', 'd.expedition as d_exped', 'd.po_no as d_po', 'cu.customer_name as cu_name', 'su.supplier_name as su_name', 'd.source as d_source', 'd.status as d_status']);
        }

        // ---- Tab form: data pendukung ----
        $productOptions = Product::active()
            ->orderByRaw('LOWER(COALESCE(NULLIF(TRIM(product_name_clean), ""), product_name))')
            ->get(['id', 'product_code', 'product_name', 'product_name_clean', 'unit', 'stock']);
        // ID produk yang sudah selesai diopname bulan berjalan.
        $opnamedIds = [];
        try {
            $opnamedIds = StockOpname::where('period', now()->format('Y-m'))
                ->where('status', StockOpname::STATUS_DONE)
                ->distinct()->pluck('product_id')->map(fn ($id) => (int) $id)->all();
        } catch (\Throwable $e) {
            $opnamedIds = [];
        }
        $productOptionsJson = $productOptions->map(fn ($p) => [
            'id' => $p->id,
            'nama' => ($p->product_name_clean ?: $p->product_name),
            'kode' => $p->product_code,
            'stok' => $p->stock,
            'unit' => $p->unit,
            'opnamed' => in_array($p->id, $opnamedIds, true),
        ])->values()->toJson();
        // Ringkasan pengingat opname: sudah vs total SKU aktif.
        $totalAktifOps = $productOptions->count();
        $sudahOpnameOps = $productOptions->whereIn('id', $opnamedIds)->count();
        $namaBulanOps = now()->locale('id')->translatedFormat('F Y');
        $suppliers = Supplier::active()->orderBy('supplier_name')->get(['id', 'supplier_name']);
        $customers = Customer::active()->orderBy('customer_name')->get(['id', 'customer_name']);
        $supplierAddressMap = Supplier::active()->get(['id', 'supplier_address'])
            ->mapWithKeys(fn ($s) => [$s->id => $s->displayAddress()])->all();
        $customerAddressMap = Customer::active()->get(['id', 'customer_address'])
            ->mapWithKeys(fn ($c) => [$c->id => $c->displayAddress()])->all();
        $expeditions = $this->expeditionOptions();
        $psList = $this->psOptions();
        $tahunAda = StockMove::selectRaw('DISTINCT YEAR(created_at) as tahun')
            ->orderByDesc('tahun')->pluck('tahun')->filter()->values();

        return view('users.warehouse.warehouseops', [
            'title' => 'Gudang',
            'tab' => $tab,
            'sjPreviewNo' => app(\App\Services\DocumentNumberService::class)->previewSj(),
            'products' => $products,
            'kebutuhan' => $kebutuhan,
            'forecastBulan' => $forecastBulan,
            'forecastDoi' => $forecastDoi,
            'forecastPct' => $forecastPct,
            'kelolaMoves' => $kelolaMoves,
            'ringkasan' => $ringkasan,
            'qtyMasuk' => $qtyMasuk,
            'qtyKeluar' => $qtyKeluar,
            'trxMasuk' => $trxMasuk,
            'trxKeluar' => $trxKeluar,
            'produkAktif' => $produkAktif,
            'productOptions' => $productOptions,
            'productOptionsJson' => $productOptionsJson,
            'belumOpnameOps' => $totalAktifOps - $sudahOpnameOps,
            'totalAktifOps' => $totalAktifOps,
            'namaBulanOps' => $namaBulanOps,
            'suppliers' => $suppliers,
            'customers' => $customers,
            'supplierAddressMap' => $supplierAddressMap,
            'customerAddressMap' => $customerAddressMap,
            'expeditions' => $expeditions,
            'psList' => $psList,
            'purposes' => WsMutation::PURPOSES,
            'tahunAda' => $tahunAda,
            'canManage' => $canManage,
            'isMasuk' => $isMasuk,
        ]);
    }

    /** Set stok dari tab produk: adjustment otomatis tervalidasi. */
    public function setOpeningStock(Request $request)
    {
        if (!$this->canManageStock()) {
            abort(403, 'Hanya pengelola stok yang bisa mengatur stok.');
        }
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'counted_qty' => ['required', 'numeric', 'min:0'],
        ]);

        $product = Product::findOrFail($validated['product_id']);
        $diff = round((float) $validated['counted_qty'] - (float) $product->stock, 2);
        if (abs($diff) < 0.005) {
            return back()->with('success', "Stok {$product->product_name} sudah sesuai ({$product->stock}). Tidak ada penyesuaian.");
        }

        $svc = app(\App\Services\StockMoveService::class);
        try {
            $move = $svc->createMove([
                'product_id' => $product->id,
                'qty' => abs($diff),
                'type' => StockMove::TYPE_ADJUSTMENT,
                'from_location' => $diff > 0 ? StockMove::LOC_ADJUSTMENT : StockMove::LOC_STOCK,
                'to_location' => $diff > 0 ? StockMove::LOC_STOCK : StockMove::LOC_ADJUSTMENT,
                'reason' => "Stok awal: tercatat {$product->stock}, ditetapkan {$validated['counted_qty']}",
            ], Auth::user());
            $svc->validateMove($move, Auth::user());
        } catch (\Throwable $e) {
            report($e);
            $errMsg = $e instanceof \InvalidArgumentException ? $e->getMessage() : 'Terjadi kesalahan sistem. Coba lagi atau hubungi admin.';

            return back()->with('error', $errMsg)->withInput();
        }

        return back()->with('success', "Stok {$product->product_name} ditetapkan {$validated['counted_qty']} (selisih {$diff} otomatis disesuaikan).");
    }

    /** Detail 1 dokumen SJ (JSON untuk popup): header + baris + berkas. */
    public function documentDetail(WsMutation $doc)
    {
        if (!$this->hasAnySalesAccess()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $doc->load(['customer', 'supplier', 'creator']);
        $lines = StockMove::with('product')
            ->where('ref_type', 'ws_mutation')->where('ref_id', $doc->id)
            ->orderBy('id')->get()
            ->map(fn ($m) => [
                'id' => $m->id,
                'barang' => $m->product->product_name_clean ?? $m->product->product_name ?? '-',
                'kode' => $m->product->product_code ?? '-',
                'qty' => (float) $m->qty,
                'unit' => $m->uom ?? $m->product->unit ?? '',
                'batch_no' => $m->batch_no ?? '-',
                'expiry_date' => $m->expiry_date ? \Carbon\Carbon::parse($m->expiry_date)->format('d M Y') : '-',
                'keterangan' => $m->reason ?? '-',
            ])->values();

        return response()->json(['success' => true, 'data' => [
            'doc_no' => $doc->doc_no,
            'direction' => $doc->direction,
            'mutation_date' => $doc->mutation_date ? \Carbon\Carbon::parse($doc->mutation_date)->format('d M Y') : '-',
            'pihak' => $doc->partyLabel(),
            'purpose' => $doc->purpose ?? '-',
            'po_no' => $doc->po_no ?? '-',
            'ship_address' => $doc->ship_address ?? '-',
            'expedition' => $doc->expedition ?? '-',
            'status' => $doc->status,
            'source' => $doc->source,
            'creator' => $doc->creator->name ?? 'System',
            'lines' => $lines,
        ]]);
    }

    /** Export PDF rekap mutasi (mengikuti filter aktif). */
    public function exportMutasiPdf(Request $request)
    {
        if (!$this->hasAnySalesAccess()) {
            abort(403, 'Unauthorized');
        }

        // Samakan dengan layar Data Mutation Ops: tanpa baris hasil Stock Opname.
        $q = $this->applyMoveFilters($this->excludeOpnameMoves($this->baseMoveQuery()), $request);
        if (in_array($request->get('kelola_arah'), ['masuk', 'keluar'], true)) {
            $this->applyArahScope($q, $request->get('kelola_arah'));
        }

        $rows = $q->orderBy('stock_moves.created_at')->orderBy('stock_moves.id')
            ->limit(1500)
            ->get();

        $isMasuk = fn ($m) => $m->type === StockMove::TYPE_RECEIPT
            || ($m->type === StockMove::TYPE_ADJUSTMENT
                && $m->to_location === StockMove::LOC_STOCK
                && $m->from_location !== StockMove::LOC_STOCK);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.warehouse.mutasi', [
            'rows' => $rows,
            'isMasuk' => $isMasuk,
            'filter' => $request->only(['search', 'product_id', 'tanggal', 'bulan', 'tahun', 'kelola_arah']),
            'printedBy' => Auth::user()->name ?? 'System',
        ])->setPaper('a4', 'landscape');

        return $pdf->download('Rekap_Mutasi_' . date('Ymd_His') . '.pdf');
    }

    /** Export PDF rekap surat jalan (mengikuti filter aktif). */
    public function exportSjPdf(Request $request)
    {
        if (!$this->hasAnySalesAccess()) {
            abort(403, 'Unauthorized');
        }

        $direction = null;
        if ($request->get('kelola_arah') === 'masuk') $direction = WsMutation::DIRECTION_IN;
        if ($request->get('kelola_arah') === 'keluar') $direction = WsMutation::DIRECTION_OUT;

        $docs = WsMutation::with(['customer', 'supplier'])->withCount('moves')
            ->applyFilters([
                'search' => $request->input('search'),
                'direction' => $direction,
                'customer_id' => $request->input('customer_id'),
                'date_from' => $request->input('date_from'),
                'date_to' => $request->input('date_to'),
            ])
            ->orderByDesc('mutation_date')->orderByDesc('id')->limit(1000)->get();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.warehouse.sj', [
            'docs' => $docs,
            'printedBy' => Auth::user()->name ?? 'System',
        ])->setPaper('a4', 'landscape');

        return $pdf->download('Rekap_Surat_Jalan_' . date('Ymd_His') . '.pdf');
    }

    /** Batasi query mutasi ke satu arah (masuk = receipt/adjustment-masuk). */
    private function applyArahScope($query, string $arah): void
    {
        $query->where(function ($q) use ($arah) {
            if ($arah === 'masuk') {
                $q->where('type', StockMove::TYPE_RECEIPT)
                    ->orWhere(function ($w) {
                        $w->where('type', StockMove::TYPE_ADJUSTMENT)
                            ->where('to_location', StockMove::LOC_STOCK)
                            ->where('from_location', '<>', StockMove::LOC_STOCK);
                    });
            } else {
                $q->whereIn('type', [StockMove::TYPE_DELIVERY, StockMove::TYPE_SCRAP])
                    ->orWhere(function ($w) {
                        $w->where('type', StockMove::TYPE_ADJUSTMENT)
                            ->where('from_location', StockMove::LOC_STOCK);
                    });
            }
        });
    }
}

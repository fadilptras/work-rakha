<?php

namespace App\Http\Controllers\Warehouse;

use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\StockMove;
use App\Models\StockOpname;
use App\Models\Supplier;
use App\Models\WsMutation;
use App\Services\DocumentNumberService;
use App\Services\ForecastService;
use App\Services\StockMoveService;
use App\Services\StockOpnameService;
use App\Support\PackagingCatalog;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Controller Warehouse Operations & Pencatatan Mutasi Stok.
 */
class WsMutationController extends WsBaseController
{
    /** Redirect URL lama Pencatatan Barang ke Warehouse Operations. */
    public function pencatatanRedirect(Request $request)
    {
        $tab = $request->get('tab') === 'masuk' ? 'masuk' : 'mutasi';

        return redirect()->route('warehouse.index', ['tab' => $tab]);
    }

    /** Simpan satu dokumen mutasi beserta rincian barang. */
    public function pencatatanStore(Request $request)
    {
        if (! $this->canManageStock()) {
            abort(403, 'Hanya pengelola stok yang bisa mencatat barang.');
        }

        $validated = $request->validate([
            'tanggal' => ['required', 'date', 'before_or_equal:today'],
            'asal' => ['nullable', 'string', 'max:10'],
            'doc_no' => ['nullable', 'string', 'max:100'],
            'po_no' => ['nullable', 'string', 'max:100'],
            'purpose' => ['nullable', 'string', 'max:50'],
            'ps_name' => ['nullable', 'string', 'max:100'],
            'customer_id' => ['nullable'],
            'supplier_id' => ['nullable'],
            'partner_name' => ['nullable', 'string', 'max:150'],
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
        ], [
            'tanggal.required' => 'Tanggal mutasi tidak boleh kosong.',
            'tanggal.date' => 'Format tanggal mutasi tidak valid.',
            'tanggal.before_or_equal' => 'Tanggal mutasi tidak boleh melebihi tanggal hari ini.',
            'product_id.required' => 'Tabel barang masih kosong. Silakan tambahkan minimal 1 barang.',
            'product_id.min' => 'Tabel barang masih kosong. Silakan tambahkan minimal 1 barang.',
            'product_id.*.exists' => 'Barang yang dipilih tidak cocok atau tidak terdaftar di master produk.',
            'arah.required' => 'Arah mutasi tidak valid.',
            'qty.required' => 'Jumlah Qty wajib diisi.',
            'qty.*.required' => 'Jumlah Qty pada setiap baris barang wajib diisi.',
            'qty.*.numeric' => 'Jumlah Qty harus berupa angka yang valid.',
            'qty.*.min' => 'Jumlah Qty harus lebih dari 0.',
        ]);

        // Arah transaksi
        $arahList = array_values(array_unique(array_filter($validated['arah'] ?? [])));
        if (count($arahList) !== 1 || ! in_array($arahList[0], ['masuk', 'keluar'], true)) {
            return back()->with('error', 'Satu dokumen hanya untuk satu arah (masuk atau keluar).')->withInput();
        }
        $arah = $arahList[0];
        $direction = $arah === 'masuk' ? WsMutation::DIRECTION_IN : WsMutation::DIRECTION_OUT;
        $fromOps = ($validated['asal'] ?? '') === 'ops';

        // Validasi kesesuaian array baris barang
        $pids = $validated['product_id'] ?? [];
        $qtys = $validated['qty'] ?? [];
        if (count($pids) !== count($qtys) || empty($pids)) {
            return back()->with('error', 'Jumlah baris barang dan kuantitas tidak serasi. Silakan periksa kembali formulir.')->withInput();
        }

        // Kunci atomik anti double-submit
        $submitSig = md5(Auth::id().'_'.$validated['tanggal'].'_'.$arah.'_'.trim((string) ($validated['doc_no'] ?? '')).'_'.json_encode($validated['product_id']).'_'.json_encode($validated['qty']).'_'.json_encode($validated['batch_no'] ?? []));
        $lockKey = 'ws_store_lock_'.$submitSig;
        if (! Cache::add($lockKey, true, 30)) {
            $redirectTarget = ($fromOps || ! $request->is('warehouse/stock/surat-jalan*'))
                ? redirect()->route('warehouse.index', ['tab' => $arah])
                : redirect()->route('warehouse.sj.index');
            return $redirectTarget->with('error', 'Transaksi sedang diproses. Harap tunggu dan tidak mengklik simpan berulang kali.');
        }

        // Cek duplikasi transaksi identik
        $recentDoc = WsMutation::where('created_by', Auth::id())
            ->where('direction', $direction)
            ->where('mutation_date', $validated['tanggal'])
            ->where('created_at', '>=', now()->subSeconds(60))
            ->when(! empty($validated['doc_no']), fn ($q) => $q->where('doc_no', trim($validated['doc_no'])))
            ->latest('id')
            ->first();

        if ($recentDoc) {
            $existingMoves = $recentDoc->activeMoves()->get();
            if ($existingMoves->count() === count($validated['product_id'])) {
                $identical = true;
                foreach ($validated['product_id'] as $idx => $pid) {
                    $matched = $existingMoves->first(fn ($m) => (int) $m->product_id === (int) $pid && abs((float) $m->qty - (float) ($validated['qty'][$idx] ?? 0)) < 0.005);
                    if (! $matched) {
                        $identical = false;
                        break;
                    }
                }
                if ($identical) {
                    $redirectTarget = ($fromOps || ! $request->is('warehouse/stock/surat-jalan*'))
                        ? redirect()->route('warehouse.index', ['tab' => $arah])
                        : redirect()->route('warehouse.sj.index');
                    return $redirectTarget->with('success', 'Transaksi telah tersimpan sebelumnya (duplikasi klik berhasil dicegah).');
                }
            }
        }

        // Nomor dokumen
        if ($arah === 'keluar' && ! $fromOps) {
            $docNo = app(DocumentNumberService::class)->nextSj();
        } else {
            $docNo = trim((string) ($validated['doc_no'] ?? ''));
        }
        $docNo = $docNo !== '' ? $docNo : null;

        // Keterangan & tujuan mutasi
        if ($arah === 'masuk') {
            $rawPurpose = trim((string) ($validated['purpose'] ?? ''));
            $purpose = $rawPurpose !== '' ? $rawPurpose : WsMutation::PURPOSE_PURCHASE;
        } else {
            $purpose = WsMutation::normalizeOutPurpose($validated['purpose'] ?? null);
            if ($purpose === null) {
                $rawOut = trim((string) ($validated['purpose'] ?? ''));
                if ($rawOut !== '') {
                    $purpose = $rawOut;
                } else {
                    return back()->with('error', 'Keterangan barang keluar wajib diisi.')->withInput();
                }
            }
        }
        $customerId = !empty($validated['customer_id']) ? (int) $validated['customer_id'] : null;
        $supplierId = !empty($validated['supplier_id']) ? (int) $validated['supplier_id'] : null;
        $partnerName = trim((string) ($validated['partner_name'] ?? ''));

        // Fallback jika form barang masuk mengirim input dengan name="customer_id" (akibat switch JS sebelumnya)
        if ($arah === 'masuk') {
            if (! $supplierId && $customerId) {
                if (\App\Models\Supplier::where('id', $customerId)->exists()) {
                    $supplierId = $customerId;
                }
            }
            $customerId = null;
        } elseif ($purpose === WsMutation::PURPOSE_RETURN) {
            if (! $supplierId && $customerId) {
                if (\App\Models\Supplier::where('id', $customerId)->exists()) {
                    $supplierId = $customerId;
                    $customerId = null;
                }
            }
        }

        // Pastikan ID valid sesuai tabel masing-masing
        if ($customerId && ! \App\Models\Customer::where('id', $customerId)->exists()) {
            $customerId = null;
        }
        if ($supplierId && ! \App\Models\Supplier::where('id', $supplierId)->exists()) {
            $supplierId = null;
        }
        // Customer/supplier opsional: boleh dikosongkan dan dilengkapi lain waktu.
        // Sample: pihak = nama PS (boleh tanpa customer).
        $psName = ($arah === 'keluar' && $purpose === WsMutation::PURPOSE_SAMPLE) ? trim((string) ($validated['ps_name'] ?? '')) : '';
        if ($arah === 'keluar' && $purpose === WsMutation::PURPOSE_SAMPLE && ! $customerId && $psName === '') {
            return back()->with('error', 'Sample wajib pilih nama PS.')->withInput();
        }

        $poNo = trim((string) ($validated['po_no'] ?? ''));
        $ship = trim((string) ($validated['ship_address'] ?? ''));
        $expedition = trim((string) ($validated['expedition'] ?? ''));

        // Ketik nama baru -> simpan/restore master + alamat agar bisa dipakai lagi.
        // Nama kosong tidak memaksa pemilihan: transaksi tetap bisa disimpan.
        $customer = null;
        $supplier = null;
        try {
            if ($arah === 'masuk' || $purpose === WsMutation::PURPOSE_RETURN) {
                if ($supplierId || $partnerName !== '') {
                    $supplier = $this->resolveSupplier($supplierId, $partnerName, $ship);
                }
            } elseif ($purpose === WsMutation::PURPOSE_SALE) {
                if ($customerId || $partnerName !== '') {
                    $customer = $this->resolveCustomer($customerId, $partnerName, $ship);
                }
            } elseif ($purpose === WsMutation::PURPOSE_SAMPLE && ($customerId || $partnerName !== '')) {
                $customer = $this->resolveCustomer($customerId, $partnerName, $ship);
            }
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }

        $customerId = $customer?->id;
        $supplierId = $supplier?->id;
        if ($ship === '') {
            $ship = $customer?->displayAddress() ?: ($supplier?->displayAddress() ?? '');
        }
        $partnerName = match (true) {
            $purpose === WsMutation::PURPOSE_SAMPLE => ($customer !== null && $psName !== '') ? "{$customer->customer_name} (PS: {$psName})" : ($customer?->customer_name ?: ($psName !== '' ? $psName : $partnerName)),
            $supplier !== null => (string) $supplier->supplier_name,
            $customer !== null => (string) $customer->customer_name,
            default => $partnerName,
        };

        $svc = app(StockMoveService::class);
        try {
            $count = DB::transaction(function () use ($svc, $validated, $arah, $direction, $docNo, $purpose, $partnerName, $customerId, $supplierId, $poNo, $ship, $expedition) {
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
                    if ($qty <= 0) {
                        continue;
                    }
                    $reason = self::buildMutationReason($arah, $purpose, $partnerName, $validated['tanggal'], $docNo, $ket !== '' ? $ket : null);
                    $move = $svc->createMove([
                        'product_id' => $pid,
                        'qty' => $qty,
                        'type' => $arah === 'masuk' ? StockMove::TYPE_RECEIPT : StockMove::TYPE_DELIVERY,
                        'to_location' => ($arah === 'keluar' && $purpose === WsMutation::PURPOSE_RETURN) ? 'Vendor' : null,
                        'batch_no' => $batch !== '' ? $batch : null,
                        'doc_no' => $docNo,
                        'partner' => $partnerName !== '' ? $partnerName : null,
                        'po_no' => $poNo !== '' ? $poNo : null,
                        'purpose' => $purpose !== '' ? $purpose : null,
                        'ship_address' => $ship !== '' ? $ship : null,
                        'expedition' => $expedition !== '' ? $expedition : null,
                        'expiry_date' => $ed !== '' ? $ed : null,
                        'occurred_at' => $validated['tanggal'],
                        'reason' => $reason,
                        'ref_type' => 'ws_mutation',
                        'ref_id' => $doc->id,
                    ], Auth::user());
                    $svc->validateMove($move, Auth::user(), true);
                    $n++;
                }
                if ($n === 0) {
                    throw new \InvalidArgumentException('Tidak ada baris valid untuk disimpan.');
                }

                return $n;
            });
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal menyimpan dokumen: '.$e->getMessage())->withInput();
        }

        $docLabel = $docNo ?: 'tanpa No. SJ';
        $msg = "Dokumen {$docLabel} tersimpan ({$count} baris). Stok & kartu stock ter-update.";
        // Form SJ/Pencatatan keluar -> mendarat di tab arsip Surat Jalan;
        // form Ops (keluar/masuk) -> kembali ke tab Ops masing-masing.
        if ($arah === 'keluar' && ! $fromOps) {
            return redirect()->route('warehouse.sj.index')->with('success', $msg);
        }

        return redirect()->route('warehouse.index', ['tab' => $arah])->with('success', $msg);
    }

    // Update dokumen keluar (Reversal & Re-create)
    public function pencatatanDocUpdate(Request $request, WsMutation $doc)
    {
        if (! $this->canManageStock()) {
            abort(403, 'Hanya pengelola stok yang bisa edit dokumen.');
        }
        if ($doc->direction !== WsMutation::DIRECTION_OUT || $doc->status !== WsMutation::STATUS_POSTED) {
            return back()->with('error', 'Hanya dokumen keluar aktif yang bisa diedit.');
        }

        $updateLockKey = 'ws_update_lock_'.$doc->id.'_'.Auth::id();
        if (! Cache::add($updateLockKey, true, 30)) {
            return redirect()->route('warehouse.sj.index')->with('error', 'Pembaruan dokumen sedang diproses. Harap tunggu.');
        }

        $validated = $request->validate([
            'tanggal' => ['required', 'date', 'before_or_equal:today'],
            'doc_no' => ['nullable', 'string', 'max:100'],
            'po_no' => ['nullable', 'string', 'max:100'],
            'purpose' => ['required', 'string', 'max:50'],
            'ps_name' => ['nullable', 'string', 'max:100'],
            'customer_id' => ['nullable', 'exists:customers,id'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'partner_name' => ['nullable', 'string', 'max:150'],
            'ship_address' => ['nullable', 'string', 'max:2000'],
            'expedition' => ['nullable', 'string', 'max:100'],
            'product_id' => ['required', 'array', 'min:1'],
            'product_id.*' => ['required', 'exists:products,id'],
            'qty' => ['required', 'array', 'min:1'],
            'qty.*' => ['required', 'numeric', 'min:0.01'],
            'batch_no' => ['nullable', 'array'],
            'batch_no.*' => ['nullable', 'string', 'max:100'],
            'expiry_date' => ['nullable', 'array'],
            'expiry_date.*' => ['nullable', 'date'],
            'keterangan' => ['nullable', 'array'],
            'keterangan.*' => ['nullable', 'string', 'max:1000'],
        ], [
            'tanggal.required' => 'Tanggal mutasi tidak boleh kosong.',
            'tanggal.date' => 'Format tanggal mutasi tidak valid.',
            'tanggal.before_or_equal' => 'Tanggal mutasi tidak boleh melebihi tanggal hari ini.',
            'purpose.required' => 'Keterangan mutasi barang keluar wajib dipilih.',
            'product_id.required' => 'Tabel barang masih kosong. Silakan tambahkan minimal 1 barang.',
            'product_id.min' => 'Tabel barang masih kosong. Silakan tambahkan minimal 1 barang.',
            'product_id.*.exists' => 'Barang yang dipilih tidak cocok atau tidak terdaftar di master produk.',
            'qty.*.required' => 'Jumlah Qty pada setiap baris barang wajib diisi.',
            'qty.*.numeric' => 'Jumlah Qty harus berupa angka yang valid.',
            'qty.*.min' => 'Jumlah Qty harus lebih dari 0.',
        ]);

        $purpose = WsMutation::normalizeOutPurpose($validated['purpose']);
        if ($purpose === null) {
            $rawOut = trim((string) ($validated['purpose'] ?? ''));
            if ($rawOut !== '') {
                $purpose = $rawOut;
            } else {
                return back()->with('error', 'Keterangan barang keluar wajib diisi.')->withInput();
            }
        }

        $docNo = trim((string) ($validated['doc_no'] ?? ''));
        $docNo = $docNo !== '' ? $docNo : $doc->doc_no;
        $poNo = trim((string) ($validated['po_no'] ?? ''));
        $ship = trim((string) ($validated['ship_address'] ?? ''));
        $expedition = trim((string) ($validated['expedition'] ?? ''));
        $customerId = !empty($validated['customer_id']) ? (int) $validated['customer_id'] : null;
        $supplierId = !empty($validated['supplier_id']) ? (int) $validated['supplier_id'] : null;
        $partnerName = trim((string) ($validated['partner_name'] ?? ''));
        $psName = $purpose === WsMutation::PURPOSE_SAMPLE ? trim((string) ($validated['ps_name'] ?? '')) : '';

        if ($purpose === WsMutation::PURPOSE_SAMPLE && ! $customerId && $psName === '') {
            return back()->with('error', 'Sample wajib pilih nama PS.')->withInput();
        }

        try {
            $customer = null;
            $supplier = null;
            if ($purpose === WsMutation::PURPOSE_RETURN) {
                if ($supplierId || $partnerName !== '') {
                    $supplier = $this->resolveSupplier($supplierId, $partnerName, $ship);
                }
            } elseif ($purpose === WsMutation::PURPOSE_SAMPLE) {
                $supplierId = null;
                $customer = ($customerId || $partnerName !== '') ? $this->resolveCustomer($customerId, $partnerName, $ship) : null;
            } else {
                if ($customerId || $partnerName !== '') {
                    $customer = $this->resolveCustomer($customerId, $partnerName, $ship);
                }
                $supplierId = null;
            }
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }

        $customerId = $customer?->id;
        $supplierId = $supplier?->id;
        if ($ship === '') {
            $ship = $customer?->displayAddress() ?: ($supplier?->displayAddress() ?? '');
        }
        $partner = match (true) {
            $purpose === WsMutation::PURPOSE_SAMPLE => ($customer !== null && $psName !== '') ? "{$customer->customer_name} (PS: {$psName})" : ($customer?->customer_name ?: ($psName !== '' ? $psName : $partnerName)),
            $supplier !== null => (string) $supplier->supplier_name,
            $customer !== null => (string) $customer->customer_name,
            default => $partnerName,
        };

        $svc = app(StockMoveService::class);
        try {
            $count = DB::transaction(function () use ($svc, $doc, $validated, $docNo, $poNo, $ship, $expedition, $purpose, $partner, $customerId, $supplierId) {
                $oldMoves = $doc->activeMoves()->get();
                foreach ($oldMoves as $old) {
                    $svc->reverseMove($old, Auth::user(), "Edit dokumen #{$doc->id} ({$old->reason})");
                    $old->update(['status' => StockMove::STATUS_CANCELLED]);
                }

                $doc->update([
                    'doc_no' => $docNo ?: null,
                    'mutation_date' => $validated['tanggal'],
                    'customer_id' => $customerId ?: null,
                    'supplier_id' => $supplierId ?: null,
                    'po_no' => $poNo !== '' ? $poNo : null,
                    'purpose' => $purpose,
                    'ship_address' => $ship !== '' ? $ship : null,
                    'expedition' => $expedition !== '' ? $expedition : null,
                    'status' => WsMutation::STATUS_POSTED,
                ]);

                $n = 0;
                foreach ($validated['product_id'] as $i => $pid) {
                    $qty = (float) ($validated['qty'][$i] ?? 0);
                    if ($qty <= 0) {
                        continue;
                    }
                    $batch = trim((string) ($validated['batch_no'][$i] ?? ''));
                    $ed = trim((string) ($validated['expiry_date'][$i] ?? ''));
                    $ket = trim((string) ($validated['keterangan'][$i] ?? ''));
                    $reason = self::buildMutationReason('keluar', $purpose, $partner, $validated['tanggal'], $docNo ?: null, $ket !== '' ? $ket : null);

                    $move = $svc->createMove([
                        'product_id' => $pid,
                        'qty' => $qty,
                        'type' => StockMove::TYPE_DELIVERY,
                        'to_location' => $purpose === WsMutation::PURPOSE_RETURN ? 'Vendor' : null,
                        'batch_no' => $batch !== '' ? $batch : null,
                        'doc_no' => $docNo ?: null,
                        'partner' => $partner !== '' ? $partner : null,
                        'po_no' => $poNo !== '' ? $poNo : null,
                        'purpose' => $purpose,
                        'ship_address' => $ship !== '' ? $ship : null,
                        'expedition' => $expedition !== '' ? $expedition : null,
                        'expiry_date' => $ed !== '' ? $ed : null,
                        'occurred_at' => $validated['tanggal'],
                        'reason' => $reason,
                        'ref_type' => 'ws_mutation',
                        'ref_id' => $doc->id,
                    ], Auth::user());
                    $svc->validateMove($move, Auth::user(), true);
                    $n++;
                }

                if ($n === 0) {
                    throw new \InvalidArgumentException('Dokumen harus punya minimal 1 baris.');
                }
                $this->refreshMutationStatus($doc->activeMoves()->first());

                return $n;
            });
        } catch (\Throwable $e) {
            report($e);

            return back()->with('error', 'Gagal menyimpan perubahan: '.$e->getMessage())->withInput();
        }

        $msg = 'Dokumen '.($docNo ?: 'tanpa No. SJ')." diperbarui ({$count} baris).";
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => $msg]);
        }
        if ($request->input('asal') === 'ops') {
            $tab = in_array($request->input('tab'), ['mutasi', 'keluar', 'masuk', 'produk']) ? $request->input('tab') : 'mutasi';
            return redirect()->route('warehouse.index', ['tab' => $tab])->with('success', $msg);
        }

        return redirect()->route('warehouse.sj.index')->with('success', $msg);
    }

    // Update satu baris mutasi (Reversal & Koreksi)
    public function pencatatanUpdate(Request $request, StockMove $move)
    {
        if (! $this->canManageStock()) {
            abort(403, 'Hanya pengelola stok yang bisa edit pencatatan.');
        }
        // Validasi status & tipe baris
        if (str_starts_with((string) $move->reason, 'Opname:') || str_starts_with((string) $move->reason, 'Opname (')) {
            return back()->with('error', 'Baris opname hanya bisa dikoreksi lewat menu Catat Opname.');
        }
        if (str_starts_with((string) $move->reason, 'Perubahan Stok Awal') || $move->ref_type === 'master_stock' || $move->ref_type === 'master_stock_edit') {
            return back()->with('error', 'Stok awal produk dikelola melalui menu Data Product.');
        }
        if ($move->status !== StockMove::STATUS_DONE) {
            return back()->with('error', 'Hanya baris berstatus selesai yang bisa diedit.');
        }

        $validated = $request->validate([
            'tanggal' => ['required', 'date', 'before_or_equal:today'],
            'qty' => ['required', 'numeric', 'not_in:0'],
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

        $svc = app(StockMoveService::class);
        try {
            $inputQty = abs((float) $validated['qty']);
            $qtyChanged = abs((float) $move->qty - $inputQty) >= 0.005;
            $dateChanged = Carbon::parse($move->created_at)->toDateString() !== $validated['tanggal'];
            $edChanged = ($move->expiry_date?->toDateString() ?? '') !== ($validated['expiry_date'] ?? '');

            // Sanitasi No. Dokumen
            $newDocNo = trim((string) ($validated['doc_no'] ?? ''));
            $newDocNo = $newDocNo !== '' ? $newDocNo : null;

            $msg = DB::transaction(function () use ($svc, $move, $validated, $doc, $newDocNo, $qtyChanged, $dateChanged, $edChanged, $inputQty) {
                $user = Auth::user();
                $oldDocNo = $doc ? $doc->doc_no : $move->doc_no;

                if ($doc) {
                    $doc->update([
                        'doc_no' => $newDocNo,
                        'mutation_date' => $validated['tanggal'],
                        'po_no' => trim((string) ($validated['po_no'] ?? '')) ?: null,
                        'ship_address' => trim((string) ($validated['ship_address'] ?? '')) !== '' ? trim((string) $validated['ship_address']) : null,
                        'expedition' => trim((string) ($validated['expedition'] ?? '')) !== '' ? trim((string) $validated['expedition']) : null,
                    ]);
                    // Sinkronisasi snapshot dokumen ke baris terkait
                    $siblings = StockMove::where('ref_type', 'ws_mutation')->where('ref_id', $doc->id)->get();
                    $currentDocNo = $doc->fresh()->doc_no;
                    foreach ($siblings as $sib) {
                        $attrs = [
                            'doc_no' => $currentDocNo,
                            'po_no' => $doc->fresh()->po_no,
                            'ship_address' => $doc->fresh()->ship_address,
                            'expedition' => $doc->fresh()->expedition,
                        ];
                        if ($oldDocNo && str_starts_with((string) $sib->reason, "[{$oldDocNo}] ")) {
                            $rest = substr((string) $sib->reason, strlen($oldDocNo) + 3);
                            $attrs['reason'] = $currentDocNo ? "[{$currentDocNo}] {$rest}" : $rest;
                        }
                        $sib->update($attrs);
                    }
                    $move = $move->fresh();
                }

                $currentDocNo = $doc ? $doc->fresh()->doc_no : $newDocNo;
                $reason = self::buildMutationReason($this->directionLabel($move), $move->purpose, $move->partner, $validated['tanggal'], $currentDocNo, (string) $validated['keterangan']);

                if (! $qtyChanged && ! $dateChanged && ! $edChanged) {
                    $move->update([
                        'batch_no' => trim((string) ($validated['batch_no'] ?? '')) ?: null,
                        'reason' => $reason,
                    ]);

                    return "Baris #{$move->id} diperbarui (tanpa perubahan stok).";
                }

                $svc->reverseMove($move, $user, "Koreksi pencatatan #{$move->id}");
                $move->update(['status' => StockMove::STATUS_CANCELLED]);
                $isDoc = $doc !== null;
                $baru = $svc->createMove([
                    'product_id' => $move->product_id,
                    'qty' => $inputQty,
                    'type' => $move->type,
                    'from_location' => $move->from_location,
                    'to_location' => $move->to_location,
                    'batch_no' => trim((string) ($validated['batch_no'] ?? '')) ?: null,
                    'doc_no' => $isDoc ? $doc->fresh()->doc_no : ($newDocNo ?: null),
                    'po_no' => $isDoc ? $doc->fresh()->po_no : (trim((string) ($validated['po_no'] ?? '')) ?: null),
                    'partner' => $move->partner,
                    'purpose' => $move->purpose,
                    'ship_address' => $isDoc ? $doc->fresh()->ship_address : null,
                    'expedition' => $isDoc ? $doc->fresh()->expedition : null,
                    'expiry_date' => !empty($validated['expiry_date']) ? $validated['expiry_date'] : null,
                    'occurred_at' => $validated['tanggal'],
                    'reason' => $reason,
                    'ref_type' => $isDoc ? 'ws_mutation' : $move->ref_type,
                    'ref_id' => $isDoc ? $doc->id : $move->id,
                ], $user);
                $svc->validateMove($baru, $user, true);
                if ($doc) {
                    $this->refreshMutationStatus($baru);
                }

                return "Baris #{$move->id} dikoreksi via reversal + mutasi baru #{$baru->id}.";
            });
        } catch (\Throwable $e) {
            report($e);
            $errMsg = $e instanceof \InvalidArgumentException ? $e->getMessage() : 'Terjadi kesalahan sistem. Coba lagi atau hubungi admin.';

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $errMsg], 422);
            }

            return back()->with('error', $errMsg)->withInput();
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => $msg]);
        }

        return back()->with('success', $msg);
    }

    // Generator format keterangan mutasi
    public static function buildMutationReason(string $arah, ?string $purpose, ?string $partner, ?string $tanggal = null, ?string $docNo = null, ?string $note = null): string
    {
        $purp = trim((string) $purpose);
        $part = trim((string) $partner);
        $tglFmt = '';
        if ($tanggal) {
            try {
                $tglFmt = Carbon::parse($tanggal)->format('d/m/Y');
            } catch (\Throwable $e) {}
        }

        $purpLower = strtolower($purp);
        if ($arah === 'masuk') {
            if ($purpLower === 'produksi') {
                $mainText = 'Produksi'.($part !== '' ? " ({$part})" : '');
            } elseif ($purpLower === 'pembelian') {
                $mainText = 'Pembelian'.($part !== '' ? " dari {$part}" : '');
            } elseif ($purp !== '') {
                $mainText = $purp.($part !== '' ? " dari {$part}" : '');
            } else {
                $mainText = 'Barang Masuk'.($part !== '' ? " dari {$part}" : '');
            }
        } else {
            if ($purpLower === 'sample' || $purpLower === 'sampel') {
                $mainText = 'Sample'.($part !== '' ? " untuk {$part}" : '');
            } elseif ($purpLower === 'penjualan' || $purpLower === 'sale') {
                $mainText = 'Penjualan'.($part !== '' ? " ke {$part}" : '');
            } elseif (str_contains($purpLower, 'retur')) {
                $mainText = 'Retur Barang'.($part !== '' ? " ke {$part}" : '');
            } elseif ($purpLower === 'internal') {
                $mainText = 'Pemakaian Internal'.($part !== '' ? " ({$part})" : '');
            } elseif ($purp !== '') {
                $mainText = $purp.($part !== '' ? " - {$part}" : '');
            } else {
                $mainText = 'Pengeluaran Barang'.($part !== '' ? " ke {$part}" : '');
            }
        }

        $text = $mainText;
        if ($note !== null && trim($note) !== '') {
            $n = trim($note);
            if (! str_contains(strtolower($n), strtolower($mainText))) {
                $text .= ' - '.$n;
            } else {
                $text = $n;
            }
        }

        if ($tglFmt !== '') {
            $text .= " ({$tglFmt})";
        }

        if ($docNo !== null && trim($docNo) !== '') {
            $text = '['.trim($docNo).'] '.$text;
        }

        return $text;
    }

    // Format keterangan fallback
    private function composeReason(?string $docNo, string $base, string $notes = ''): string
    {
        $base = trim($base);
        $notes = trim($notes);
        if ($base === '') {
            $base = 'Pencatatan';
        }
        $text = $base;
        if ($notes !== '' && ! str_contains($text, $notes)) {
            $text .= ' - '.$notes;
        }

        return $docNo ? "[{$docNo}] {$text}" : $text;
    }

    private function directionLabel(StockMove $move): string
    {
        return $move->type === StockMove::TYPE_RECEIPT ? 'masuk' : 'keluar';
    }

    // Resolve data customer & alamat
    private function resolveCustomer(?int $id, string $name, string $address): Customer
    {
        $name = trim($name);
        $address = trim($address);
        $customer = null;

        // Pencarian via ID
        if ($id) {
            $found = Customer::find($id);
            if ($found && ($name === '' || mb_strtolower(trim($found->customer_name)) === mb_strtolower($name))) {
                $customer = $found;
            }
        }

        // Pencarian via nama
        if (! $customer && $name !== '') {
            $customer = Customer::whereRaw('LOWER(customer_name) = ?', [mb_strtolower($name)])->first();
        }

        // Buat data baru jika belum ada
        if (! $customer && $name !== '') {
            $initialAddress = [];
            if ($address !== '') {
                $initialAddress[] = [
                    'label' => 'Kantor',
                    'address' => $address,
                    'city' => '',
                    'phone' => '',
                    'is_default' => true,
                ];
            }
            $customer = Customer::create([
                'customer_name' => $name,
                'customer_type' => 'Lainnya',
                'customer_address' => $initialAddress,
            ]);
        }

        if (! $customer) {
            throw new \InvalidArgumentException('Customer wajib dipilih atau diketik.');
        }

        if ($customer->is_deleted) {
            $customer->restore();
        }

        // Simpan / update alamat default
        if ($address !== '') {
            $list = array_values(array_filter((array) ($customer->customer_address ?? []), 'is_array'));
            $targetIndex = null;
            foreach ($list as $i => $a) {
                if (($a['label'] ?? '') === 'Kantor') {
                    $targetIndex = $i;
                    break;
                }
            }
            if ($targetIndex === null) {
                foreach ($list as $i => $a) {
                    if (! empty($a['is_default'])) {
                        $targetIndex = $i;
                        break;
                    }
                }
            }
            if ($targetIndex === null && ! empty($list)) {
                $targetIndex = 0;
            }

            if ($targetIndex !== null) {
                $list[$targetIndex]['address'] = $address;
                $list[$targetIndex]['is_default'] = true;
                foreach ($list as $k => $item) {
                    if ($k !== $targetIndex) {
                        $list[$k]['is_default'] = false;
                    }
                }
            } else {
                $list[] = [
                    'label' => 'Kantor',
                    'address' => $address,
                    'city' => '',
                    'phone' => '',
                    'is_default' => true,
                ];
            }
            $customer->update(['customer_address' => array_values($list)]);
            $customer->refresh();
        }

        return $customer;
    }

    // Resolve data supplier & alamat
    private function resolveSupplier(?int $id, string $name, string $address): Supplier
    {
        $name = trim($name);
        $address = trim($address);
        $supplier = null;

        // Pencarian via ID
        if ($id) {
            $found = Supplier::find($id);
            if ($found && ($name === '' || mb_strtolower(trim($found->supplier_name)) === mb_strtolower($name))) {
                $supplier = $found;
            }
        }

        // Pencarian via nama
        if (! $supplier && $name !== '') {
            $supplier = Supplier::whereRaw('LOWER(supplier_name) = ?', [mb_strtolower($name)])->first();
        }

        // Buat data baru jika belum ada
        if (! $supplier && $name !== '') {
            $initialAddress = [];
            if ($address !== '') {
                $initialAddress[] = [
                    'label' => 'Kantor',
                    'address' => $address,
                    'city' => '',
                    'phone' => '',
                    'is_default' => true,
                ];
            }
            $supplier = Supplier::create([
                'supplier_name' => $name,
                'supplier_type' => 'Lainnya',
                'supplier_address' => $initialAddress,
            ]);
        }

        if (! $supplier) {
            throw new \InvalidArgumentException('Supplier wajib dipilih atau diketik.');
        }

        if ($supplier->is_deleted) {
            $supplier->restore();
        }

        // Simpan / update alamat default
        if ($address !== '') {
            $list = array_values(array_filter((array) ($supplier->supplier_address ?? []), 'is_array'));
            $targetIndex = null;
            foreach ($list as $i => $a) {
                if (($a['label'] ?? '') === 'Kantor') {
                    $targetIndex = $i;
                    break;
                }
            }
            if ($targetIndex === null) {
                foreach ($list as $i => $a) {
                    if (! empty($a['is_default'])) {
                        $targetIndex = $i;
                        break;
                    }
                }
            }
            if ($targetIndex === null && ! empty($list)) {
                $targetIndex = 0;
            }

            if ($targetIndex !== null) {
                $list[$targetIndex]['address'] = $address;
                $list[$targetIndex]['is_default'] = true;
                foreach ($list as $k => $item) {
                    if ($k !== $targetIndex) {
                        $list[$k]['is_default'] = false;
                    }
                }
            } else {
                $list[] = [
                    'label' => 'Kantor',
                    'address' => $address,
                    'city' => '',
                    'phone' => '',
                    'is_default' => true,
                ];
            }
            $supplier->update(['supplier_address' => array_values($list)]);
            $supplier->refresh();
        }

        return $supplier;
    }

    // Daftar batch aktif per produk
    public function productBatches(Request $request)
    {
        if (! $this->hasAnySalesAccess()) {
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

    // Batalkan baris mutasi (Reversal)
    public function pencatatanDestroy(Request $request, StockMove $move)
    {
        if (! $this->canManageStock()) {
            abort(403, 'Hanya pengelola stok yang bisa hapus pencatatan.');
        }
        // Validasi status & tipe baris
        if (str_starts_with((string) $move->reason, 'Opname:') || str_starts_with((string) $move->reason, 'Opname (')) {
            return back()->with('error', 'Baris opname hanya bisa dikoreksi lewat menu Catat Opname.');
        }
        if (str_starts_with((string) $move->reason, 'Perubahan Stok Awal') || $move->ref_type === 'master_stock' || $move->ref_type === 'master_stock_edit') {
            return back()->with('error', 'Stok awal produk dikelola melalui menu Data Product.');
        }
        // Idempoten: baris yang sudah dibatalkan tidak bisa dibatalkan lagi
        if ($move->status === StockMove::STATUS_CANCELLED || str_contains((string) $move->reason, '[dibatalkan]')) {
            return back()->with('error', "Baris #{$move->id} sudah dibatalkan sebelumnya.");
        }
        if ($move->status !== StockMove::STATUS_DONE) {
            return back()->with('error', 'Hanya baris berstatus selesai yang bisa dihapus.');
        }

        $svc = app(StockMoveService::class);
        try {
            $lawan = DB::transaction(function () use ($svc, $move) {
                $lawan = $svc->reverseMove($move, Auth::user(), "Hapus pencatatan #{$move->id} ({$move->reason})");
                $move->update(['status' => StockMove::STATUS_CANCELLED]);
                $this->refreshMutationStatus($move);

                return $lawan;
            });
            $msg = "Baris #{$move->id} dibatalkan via reversal #{$lawan->id}. Stok dikembalikan.";
        } catch (\Throwable $e) {
            report($e);
            $errMsg = $e instanceof \InvalidArgumentException ? $e->getMessage() : 'Terjadi kesalahan sistem. Coba lagi atau hubungi admin.';

            return back()->with('error', $errMsg);
        }

        return back()->with('success', $msg);
    }

    // Batalkan banyak baris (Bulk Reversal)
    public function pencatatanBulkDestroy(Request $request)
    {
        if (! $this->canManageStock()) {
            abort(403, 'Hanya pengelola stok yang bisa hapus pencatatan.');
        }

        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['exists:stock_moves,id'],
        ]);

        $svc = app(StockMoveService::class);
        $ok = 0;
        $failures = [];
        foreach ($validated['ids'] as $id) {
            $move = StockMove::find($id);
            if (! $move || $move->status !== StockMove::STATUS_DONE || str_contains((string) $move->reason, '[dibatalkan]')) {
                $failures[] = "#{$id}: sudah bukan status selesai.";

                continue;
            }
            if (str_starts_with((string) $move->reason, 'Opname:')) {
                $failures[] = "#{$id}: baris opname hanya bisa dikoreksi lewat halaman Catat Opname.";

                continue;
            }
            try {
                DB::transaction(function () use ($svc, $move) {
                    $svc->reverseMove($move, Auth::user(), "Hapus pencatatan #{$move->id} ({$move->reason})");
                    $move->update(['status' => StockMove::STATUS_CANCELLED]);
                    $this->refreshMutationStatus($move);
                });
                $ok++;
            } catch (\Throwable $e) {
                report($e);
                $failures[] = "#{$id}: ".($e instanceof \InvalidArgumentException ? $e->getMessage() : 'kesalahan sistem.');
            }
        }

        if ($ok === 0) {
            return back()->with('error', 'Tidak ada baris dibatalkan. '.implode(' ', $failures));
        }
        $msg = "{$ok} baris dibatalkan via reversal, stok dikembalikan.";
        if (! empty($failures)) {
            $msg .= ' Sebagian gagal: '.implode(' ', $failures);
        }

        return redirect()->route('warehouse.index', ['tab' => 'mutasi'])
            ->with(empty($failures) ? 'success' : 'warning', $msg);
    }

    // Base query mutasi selesai beserta relasi dokumen
    protected function baseMoveQuery()
    {
        return StockMove::with(['product', 'user'])
            ->visible()
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
            ->where(function ($q) {
                $q->whereNull('d.status')->orWhere('d.status', WsMutation::STATUS_POSTED);
            })
            ->addSelect(['stock_moves.*'])
            ->addSelect([
                'd.id as d_id', 'd.doc_no as d_doc', 'd.purpose as d_purpose',
                'd.ship_address as d_ship', 'd.expedition as d_exped',
                'd.po_no as d_po', 'cu.customer_name as cu_name', 'su.supplier_name as su_name',
                'd.source as d_source', 'd.status as d_status',
            ]);
    }

    // Mutasi opname diizinkan tampil di data mutasi agar tercatat transparan saat ada penyesuaian/perubahan
    protected function excludeOpnameMoves($query)
    {
        return $query;
    }

    // Filter parameter mutasi
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

    // Agregasi statistik mutasi masuk/keluar
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
            if (! isset($perBarang[$pid])) {
                $perBarang[$pid] = ['masuk' => 0, 'keluar' => 0, 'trx' => 0];
            }
            if ($isMasuk($m)) {
                $perBarang[$pid]['masuk'] += $qv;
            } else {
                $perBarang[$pid]['keluar'] += $qv;
            }
            $perBarang[$pid]['trx']++;
        }

        return [$qtyMasuk, $qtyKeluar, $trxMasuk, $trxKeluar, $perBarang];
    }

    // Halaman Warehouse Operations

    public function warehouse(Request $request)
    {
        if (! $this->hasAnySalesAccess()) {
            abort(403, 'Anda tidak memiliki hak akses ke halaman Gudang.');
        }

        $rawTab = $request->get('tab');
        if ($rawTab === 'barang') {
            $rawTab = 'data-product';
        }
        $tab = in_array($rawTab, ['data-product', 'produk', 'mutasi', 'keluar', 'masuk'], true)
            ? $rawTab : 'produk';
        $canManage = $this->canManageStock();

        $isMasuk = fn ($m) => $m->type === StockMove::TYPE_RECEIPT
            || ($m->type === StockMove::TYPE_ADJUSTMENT
                && $m->to_location === StockMove::LOC_STOCK
                && $m->from_location !== StockMove::LOC_STOCK);

        // Tab Data Product: Master produk + kemasan + stok realtime
        $productItems = collect();
        $searchProduct = trim((string) ($request->get('search') ?? $request->get('search_product', '')));
        $packCatalog = PackagingCatalog::toArray();

        if ($tab === 'data-product') {
            $pq = Product::active()
                ->when($searchProduct !== '', function ($query) use ($searchProduct) {
                    $query->where(function ($q) use ($searchProduct) {
                        $q->where('product_code', 'like', '%' . $searchProduct . '%')
                            ->orWhere('product_name', 'like', '%' . $searchProduct . '%');
                    });
                })
                ->orderByRaw('LOWER(COALESCE(NULLIF(TRIM(product_name), ""), product_code))');

            $productsList = $pq->get();
            $productIds = $productsList->pluck('id')->all();
            $stockAwalMap = $this->resolveStockAwalMap($productIds);

            $opService = app(StockOpnameService::class);
            $productItems = $productsList->map(function (Product $product) use ($opService, $stockAwalMap) {
                $pcs = (int) ($product->pcs_per_unit ?? 0);
                $isSingle = $pcs <= 1;
                $fill = $product->fill_unit ?: 'Pcs';
                $stok = $opService->syncProductStock($product);
                $info = $stockAwalMap[$product->id] ?? [
                    'stok_awal' => 0.0,
                    'has_opname' => false,
                ];

                return [
                    'id' => $product->id,
                    'kode' => $product->product_code ?? '-',
                    'nama' => $product->product_name,
                    'satuan' => $product->unit ?: $fill,
                    'kemasan' => $product->unit,
                    'stok' => $stok,
                    'stok_awal' => $info['stok_awal'],
                    'has_opname' => $info['has_opname'],
                    'pcs_per_unit' => $pcs,
                    'fill_unit' => $fill,
                    'is_single' => $isSingle,
                    'kemasan_full' => PackagingCatalog::packagingPreview($product->unit, $product->pcs_per_unit, $fill),
                ];
            });
        }

        // Tab Produk: Query SKU, Forecast & Sinkronisasi Stok
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
            $products = $pq->get();
            // Data forecast bulanan
            $forecastMapped = ['map' => [], 'bulan' => '', 'doi' => 30, 'percentage' => 20];
            try {
                $forecastMapped = app(ForecastService::class)->buildMapped(now()->year);
            } catch (\Throwable $e) {
                $forecastMapped = ['map' => [], 'bulan' => '', 'doi' => 30, 'percentage' => 20];
            }
            $forecastBulan = $forecastMapped['bulan'] ?? '';
            $forecastDoi = $forecastMapped['doi'] ?? 30;
            $forecastPct = $forecastMapped['percentage'] ?? 20;

            $stockAwalMap = $this->resolveStockAwalMap($products->pluck('id')->all());

            // Sinkronisasi stok & forecast produk
            $fcMap = $forecastMapped['map'] ?? [];
            $opService = app(StockOpnameService::class);
            $products->each(function ($p) use ($fcMap, $stockAwalMap, $opService) {
                $key = strtolower(trim((string) $p->product_name));
                $alt = strtolower(trim((string) ($p->product_name_clean ?? '')));
                $p->forecast = $fcMap[$key] ?? ($alt !== '' ? ($fcMap[$alt] ?? null) : null);

                $realtime = $opService->calculateRealtimeStock($p->id);
                if (abs((float) $p->stock - $realtime) >= 0.005) {
                    DB::table('products')->where('id', $p->id)->update(['stock' => $realtime]);
                    $p->stock = $realtime;
                }

                $info = $stockAwalMap[$p->id] ?? [
                    'stok_awal' => 0.0,
                    'has_opname' => false,
                    'is_opnamed_this_month' => false,
                ];

                $p->stock_awal_opname = $info['stok_awal'];
                $p->is_opnamed_this_month = $info['is_opnamed_this_month'];
            });
            // Filter kebutuhan order
            $kebutuhan = $request->get('butuh') === 'order' ? 'order' : 'semua';
            if ($kebutuhan === 'order') {
                $products = $products->filter(fn ($p) => $p->forecast && $p->forecast['order_qty'] > 0)->values();
            }
        }

        // Tab Mutasi: Filter & Statistik
        $qtyMasuk = 0;
        $qtyKeluar = 0;
        $trxMasuk = 0;
        $trxKeluar = 0;
        $produkAktif = 0;
        $ringkasan = collect();
        $kelolaMoves = collect();
        if ($tab === 'mutasi') {
            $base = $this->applyMoveFilters($this->excludeOpnameMoves($this->baseMoveQuery()), $request);

            [$qtyMasuk, $qtyKeluar, $trxMasuk, $trxKeluar, $perBarang] = $this->moveStats($base, $isMasuk);
            $produkAktif = count($perBarang);

            $kq = (clone $base);
            if (in_array($request->get('kelola_arah'), ['masuk', 'keluar'], true)) {
                $this->applyArahScope($kq, $request->get('kelola_arah'));
            }
            $kelolaMoves = $kq->orderByDesc('stock_moves.created_at')->orderByDesc('stock_moves.id')
                ->get(['stock_moves.*', 'd.id as d_id', 'd.doc_no as d_doc', 'd.purpose as d_purpose', 'd.ship_address as d_ship', 'd.expedition as d_exped', 'd.po_no as d_po', 'cu.customer_name as cu_name', 'su.supplier_name as su_name', 'd.source as d_source', 'd.status as d_status']);
        }

        // Data Master Form & Dropdown
        $productOptions = Product::active()
            ->orderByRaw('LOWER(COALESCE(NULLIF(TRIM(product_name), ""), product_code))')
            ->get(['id', 'product_code', 'product_code_clean', 'product_name', 'product_name_clean', 'unit', 'stock', 'created_at']);
        $opnamedIds = [];
        try {
            $opnamedIds = StockOpname::where('period', now()->format('Y-m'))
                ->where('status', StockOpname::STATUS_DONE)
                ->where(function ($q) {
                    $q->whereNull('note')->orWhere('note', 'not like', '%stok awal%');
                })
                ->distinct()->pluck('product_id')->map(fn ($id) => (int) $id)->all();
        } catch (\Throwable $e) {
            $opnamedIds = [];
        }
        $productOptionsData = $productOptions->map(fn ($p) => [
            'id' => $p->id,
            'nama' => $p->product_name ?: ($p->product_name_clean ?: '-'),
            'nama_clean' => $p->product_name_clean,
            'nama_raw' => $p->product_name,
            'kode' => $p->product_code ?: ($p->product_code_clean ?: '-'),
            'kode_clean' => $p->product_code_clean,
            'stok' => $p->stock,
            'unit' => $p->unit,
            'opnamed' => in_array($p->id, $opnamedIds, true),
        ])->values()->all();

        $productOptionsJson = json_encode($productOptionsData);
        // Produk baru bulan ini tidak wajib diopname bulan ini (baru diopname bulan berikutnya)
        $startOfCurrentMonth = now()->startOfMonth();
        $productsSubjectToOpname = $productOptions->filter(fn ($p) => $p->created_at < $startOfCurrentMonth || in_array($p->id, $opnamedIds, true));
        $totalAktifOps = $productsSubjectToOpname->count();
        $sudahOpnameOps = $productsSubjectToOpname->whereIn('id', $opnamedIds)->count();
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

        return view('users.warehouse.warehouseops.index', [
            'title' => 'Warehouse Operations',
            'tab' => $tab,
            'sjPreviewNo' => app(DocumentNumberService::class)->previewSj(),
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
            'productOptionsData' => $productOptionsData,
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
            'canManageStock' => $canManage,
            'isMasuk' => $isMasuk,
            'productItems' => $productItems,
            'searchProduct' => $searchProduct,
            'packCatalog' => $packCatalog,
        ]);
    }

    // Set stok produk (Adjustment)
    public function setOpeningStock(Request $request)
    {
        if (! $this->canManageStock()) {
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

        $svc = app(StockMoveService::class);
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

            // Rekam ke stock opname periode berjalan
            try {
                app(StockOpnameService::class)->recordOpeningOpname($product->fresh(), (float) $validated['counted_qty'], Auth::user());
            } catch (\Throwable $e) {
                report($e);
            }
        } catch (\Throwable $e) {
            report($e);
            $errMsg = $e instanceof \InvalidArgumentException ? $e->getMessage() : 'Terjadi kesalahan sistem. Coba lagi atau hubungi admin.';

            return back()->with('error', $errMsg)->withInput();
        }

        return back()->with('success', "Stok {$product->product_name} ditetapkan {$validated['counted_qty']} (selisih {$diff} otomatis disesuaikan).");
    }

    // Detail dokumen JSON (Popup)
    public function documentDetail(WsMutation $doc)
    {
        if (! $this->hasAnySalesAccess()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $doc->load(['customer', 'supplier', 'creator']);
        $lines = $doc->activeMoves()->with('product')
            ->orderBy('id')->get()
            ->map(fn ($m) => [
                'id' => $m->id,
                'product_id' => $m->product_id,
                'barang' => $m->product->product_name ?? $m->product->product_name_clean ?? '-',
                'kode' => $m->product->product_code ?? '-',
                'qty' => (float) $m->qty,
                'unit' => $m->uom ?? $m->product->unit ?? '',
                'batch_no' => trim((string) $m->batch_no) !== '' ? trim((string) $m->batch_no) : '-',
                'batch_no_raw' => trim((string) $m->batch_no) !== '' ? trim((string) $m->batch_no) : '',
                'expiry_date' => $m->expiry_date ? Carbon::parse($m->expiry_date)->format('d M Y') : '-',
                'expiry_date_raw' => $m->expiry_date ? Carbon::parse($m->expiry_date)->toDateString() : '',
                'keterangan' => $this->lineNote($m->reason) ?: '-',
                'keterangan_raw' => $this->lineNote($m->reason) ?: '',
            ])->values();

        $canEdit = $doc->status === WsMutation::STATUS_POSTED
            && $doc->direction === WsMutation::DIRECTION_OUT
            && $this->canManageStock()
            && $lines->isNotEmpty();

        $psName = $doc->purpose === 'Sample'
            ? (string) ($doc->activeMoves->first()?->partner ?? '')
            : '';

        return response()->json(['success' => true, 'data' => [
            'id' => $doc->id,
            'doc_no' => $doc->doc_no,
            'direction' => $doc->direction,
            'mutation_date' => $doc->mutation_date ? Carbon::parse($doc->mutation_date)->format('d M Y') : '-',
            'mutation_date_raw' => $doc->mutation_date ? Carbon::parse($doc->mutation_date)->toDateString() : '',
            'pihak' => $doc->partyLabel(),
            'customer_id' => $doc->customer_id,
            'supplier_id' => $doc->supplier_id,
            'partner_name' => $doc->customer?->customer_name ?? $doc->supplier?->supplier_name ?? $doc->partner ?? '',
            'ps_name' => $psName,
            'purpose' => $doc->purpose ?? '-',
            'purpose_raw' => $doc->purpose ?? '',
            'po_no' => $doc->po_no ?? '',
            'ship_address' => $doc->ship_address ?? '',
            'expedition' => $doc->expedition ?? '',
            'status' => $doc->status,
            'source' => $doc->source,
            'creator' => $doc->creator->name ?? 'System',
            'can_edit' => $canEdit,
            'edit_url' => $canEdit ? route('warehouse.mutation.doc.edit', $doc) : null,
            'update_url' => $canEdit ? route('warehouse.mutation.doc.update', $doc) : null,
            'lines' => $lines,
        ]]);
    }

    // Sanitasi keterangan baris
    private function lineNote(?string $reason): string
    {
        $text = preg_replace('/^\[[^\]]+\]\s*/', '', (string) $reason);
        $text = preg_replace('/^Pencatatan (masuk|keluar)\s+\d{4}-\d{2}-\d{2}\s*/i', '', (string) $text);

        return trim(preg_replace('/^[-–]\s*/', '', (string) $text));
    }

    // Export PDF mutasi
    public function exportMutasiPdf(Request $request)
    {
        if (! $this->hasAnySalesAccess()) {
            abort(403, 'Unauthorized');
        }

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

        $pdf = Pdf::loadView('pdf.warehouse.mutasi', [
            'rows' => $rows,
            'isMasuk' => $isMasuk,
            'filter' => $request->only(['search', 'product_id', 'tanggal', 'bulan', 'tahun', 'kelola_arah']),
            'printedBy' => Auth::user()->name ?? 'System',
        ])->setPaper('a4', 'landscape');

        return $pdf->download('Rekap_Mutasi_'.date('Ymd_His').'.pdf');
    }

    // Export PDF surat jalan
    public function exportSjPdf(Request $request)
    {
        if (! $this->hasAnySalesAccess()) {
            abort(403, 'Unauthorized');
        }

        $direction = null;
        if ($request->get('kelola_arah') === 'masuk') {
            $direction = WsMutation::DIRECTION_IN;
        }
        if ($request->get('kelola_arah') === 'keluar') {
            $direction = WsMutation::DIRECTION_OUT;
        }

        $docs = WsMutation::with(['customer', 'supplier'])->withCount([
            'moves as moves_count' => fn ($q) => $q->where('status', '!=', StockMove::STATUS_CANCELLED),
        ])
            ->applyFilters([
                'search' => $request->input('search'),
                'direction' => $direction,
                'customer_id' => $request->input('customer_id'),
                'date_from' => $request->input('date_from'),
                'date_to' => $request->input('date_to'),
            ])
            ->orderByDesc('mutation_date')->orderByDesc('id')->limit(1000)->get();

        $pdf = Pdf::loadView('pdf.warehouse.sj', [
            'docs' => $docs,
            'printedBy' => Auth::user()->name ?? 'System',
        ])->setPaper('a4', 'landscape');

        return $pdf->download('Rekap_Surat_Jalan_'.date('Ymd_His').'.pdf');
    }

    // Scope arah mutasi
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

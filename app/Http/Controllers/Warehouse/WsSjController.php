<?php

namespace App\Http\Controllers\Warehouse;

use App\Models\Customer;
use App\Models\Product;
use App\Models\StockMove;
use App\Models\Supplier;
use App\Models\WsMutation;
use App\Services\DocumentNumberService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Halaman Surat Jalan: daftar dokumen ws_mutation + arsip berkas PDF.
 * Murni tampilan & arsip — stok hanya bergerak lewat Mutation.
 * Route: warehouse.sj.*
 */
class WsSjController extends WsBaseController
{
    public function index(Request $request)
    {
        if (! $this->hasAnySalesAccess()) {
            abort(403, 'Anda tidak memiliki hak akses ke halaman Surat Jalan.');
        }

        // Tab: keluar (form input) | sj (rekap arsip, default).
        $tab = $request->get('tab') === 'keluar' ? 'keluar' : 'sj';

        $productOptions = Product::active()
            ->orderByRaw('LOWER(COALESCE(NULLIF(TRIM(product_name_clean), ""), product_name))')
            ->get(['id', 'product_code', 'product_name', 'product_name_clean', 'unit', 'stock']);

        $suppliers = Supplier::active()->orderBy('supplier_name')->get(['id', 'supplier_name']);
        $customers = Customer::active()->orderBy('customer_name')->get(['id', 'customer_name']);
        $supplierAddressMap = Supplier::active()->get(['id', 'supplier_address'])
            ->mapWithKeys(fn ($s) => [$s->id => $s->displayAddress()])->all();
        $customerAddressMap = Customer::active()->get(['id', 'customer_address'])
            ->mapWithKeys(fn ($c) => [$c->id => $c->displayAddress()])->all();
        $expeditions = $this->expeditionOptions();

        return view('users.warehouse.surat-jalan', [
            'title' => 'Delivery Notes',
            'tab' => $tab,
            'docs' => $this->docsQuery($request),
            'canManage' => $this->canManageStock(),
            // Preview No. SJ otomatis (readonly di form; nomor final dibuat atomik saat simpan)
            'sjPreviewNo' => app(DocumentNumberService::class)->previewSj(),
            'productOptionsJson' => $productOptions->map(fn ($p) => [
                'id' => $p->id,
                'nama' => ($p->product_name_clean ?: $p->product_name),
                'kode' => $p->product_code,
                'stok' => $p->stock,
                'unit' => $p->unit,
            ])->values()->toJson(),
            'suppliers' => $suppliers,
            'supplierAddressMap' => $supplierAddressMap,
            'customers' => $customers,
            'customerAddressMap' => $customerAddressMap,
            'expeditions' => $expeditions,
            'psList' => $this->psOptions(),
            'purposes' => WsMutation::PURPOSES,
            'isEdit' => false,
            'editDoc' => null,
            'editLines' => [],
            'editPsName' => '',
        ]);
    }

    /** Form edit utuh dokumen keluar (header + semua baris). */
    public function edit(Request $request, WsMutation $doc)
    {
        if (! $this->hasAnySalesAccess()) {
            abort(403, 'Anda tidak memiliki hak akses ke halaman Surat Jalan.');
        }
        if (! $this->canManageStock()) {
            abort(403, 'Hanya pengelola stok yang bisa edit dokumen.');
        }
        if ($doc->direction !== WsMutation::DIRECTION_OUT || $doc->status !== WsMutation::STATUS_POSTED) {
            abort(404, 'Dokumen keluar aktif tidak ditemukan.');
        }

        $doc->load(['customer', 'supplier', 'activeMoves.product']);
        if ($doc->activeMoves->isEmpty()) {
            abort(404, 'Dokumen tidak memiliki baris aktif.');
        }

        $productOptions = Product::active()
            ->orderByRaw('LOWER(COALESCE(NULLIF(TRIM(product_name_clean), ""), product_name))')
            ->get(['id', 'product_code', 'product_name', 'product_name_clean', 'unit', 'stock']);

        $suppliers = Supplier::active()->orderBy('supplier_name')->get(['id', 'supplier_name']);
        $customers = Customer::active()->orderBy('customer_name')->get(['id', 'customer_name']);
        $supplierAddressMap = Supplier::active()->get(['id', 'supplier_address'])
            ->mapWithKeys(fn ($s) => [$s->id => $s->displayAddress()])->all();
        $customerAddressMap = Customer::active()->get(['id', 'customer_address'])
            ->mapWithKeys(fn ($c) => [$c->id => $c->displayAddress()])->all();

        $editLines = $doc->activeMoves->map(fn ($m) => [
            'id' => $m->id,
            'product_id' => $m->product_id,
            'qty' => (float) $m->qty,
            'batch_no' => $m->batch_no,
            'expiry_date' => $m->expiry_date?->toDateString(),
            'keterangan' => $this->lineNote($m->reason),
        ])->values()->all();

        $psName = $doc->purpose === 'Sample'
            ? (string) ($doc->activeMoves->first()->partner ?? '')
            : '';

        return view('users.warehouse.surat-jalan', [
            'title' => 'Edit Delivery Note',
            'tab' => 'keluar',
            'docs' => $this->docsQuery($request),
            'canManage' => $this->canManageStock(),
            'sjPreviewNo' => $doc->doc_no ?: app(DocumentNumberService::class)->previewSj(),
            'productOptionsJson' => $productOptions->map(fn ($p) => [
                'id' => $p->id,
                'nama' => ($p->product_name_clean ?: $p->product_name),
                'kode' => $p->product_code,
                'stok' => $p->stock,
                'unit' => $p->unit,
            ])->values()->toJson(),
            'suppliers' => $suppliers,
            'supplierAddressMap' => $supplierAddressMap,
            'customers' => $customers,
            'customerAddressMap' => $customerAddressMap,
            'expeditions' => $this->expeditionOptions(),
            'psList' => $this->psOptions(),
            'purposes' => WsMutation::PURPOSES,
            'isEdit' => true,
            'editDoc' => $doc,
            'editLines' => $editLines,
            'editPsName' => $psName,
        ]);
    }

    /** Query daftar dokumen (dipakai index). */
    private function docsQuery(Request $request)
    {
        return $this->applyFilters(
            WsMutation::with([
                'customer', 'supplier', 'creator',
                'moves' => fn ($q) => $q->where('status', '!=', StockMove::STATUS_CANCELLED)->with('product'),
            ])->withCount([
                'moves as moves_count' => fn ($q) => $q->where('status', '!=', StockMove::STATUS_CANCELLED),
            ]),
            $request
        )
            ->orderByDesc('mutation_date')->orderByDesc('id')
            ->paginate(20)->withQueryString();
    }

    /** Terapkan filter tabel SJ (dipakai daftar + export). Arsip SJ khusus barang keluar. */
    private function applyFilters($query, Request $request)
    {
        return $query->applyFilters([
            'search' => $request->input('search'),
            'customer_id' => $request->input('customer_id'),
            'date_from' => $request->input('date_from'),
            'date_to' => $request->input('date_to'),
        ]);
    }

    /** Export PDF rekap SJ mengikuti filter aktif. */
    public function exportPdf(Request $request)
    {
        if (! $this->hasAnySalesAccess()) {
            abort(403, 'Anda tidak memiliki hak akses ke halaman Surat Jalan.');
        }

        $docs = $this->applyFilters(
            WsMutation::with([
                'customer', 'supplier', 'creator',
                'moves' => fn ($q) => $q->where('status', '!=', StockMove::STATUS_CANCELLED)->with('product'),
            ]),
            $request
        )
            ->orderByDesc('mutation_date')->orderByDesc('id')
            ->limit(1000)
            ->get();

        $customerName = '-';
        if ($request->filled('customer_id')) {
            $customerName = $request->input('customer_id') === '__other'
                ? 'Bukan customer'
                : (Customer::where('id', $request->input('customer_id'))->value('customer_name') ?? '-');
        }

        $pdf = Pdf::loadView('pdf.warehouse.sj-rekap', [
            'docs' => $docs,
            'filter' => [
                'search' => trim((string) $request->input('search', '')),
                'date_from' => $request->input('date_from'),
                'date_to' => $request->input('date_to'),
                'customer' => $customerName,
            ],
            'printedBy' => Auth::user()->name ?? 'System',
        ])->setPaper('a4', 'landscape');

        return $pdf->download('Rekap_SJ_'.date('Ymd_His').'.pdf');
    }

    /** Cetak PDF surat jalan per dokumen (hasil cetak + stok sudah terpotong saat simpan). */
    public function print(WsMutation $doc)
    {
        if (! $this->hasAnySalesAccess()) {
            abort(403, 'Anda tidak memiliki hak akses ke halaman Surat Jalan.');
        }
        if ($doc->direction !== WsMutation::DIRECTION_OUT) {
            abort(404, 'Dokumen bukan surat jalan keluar.');
        }

        $doc->load(['customer', 'supplier', 'creator', 'activeMoves.product']);
        if ($doc->activeMoves->isEmpty()) {
            abort(404, 'Dokumen tidak memiliki baris aktif.');
        }

        $pihak = $doc->customer?->customer_name ?? $doc->supplier?->supplier_name ?? 'Pemakaian Internal';
        $alamat = $doc->ship_address
            ?: $doc->customer?->displayAddress()
            ?: $doc->supplier?->displayAddress()
            ?: '-';

        $lines = $doc->activeMoves->map(fn ($m) => [
            'barang' => $m->product->product_name_clean ?? $m->product->product_name ?? '-',
            'qty' => rtrim(rtrim(number_format((float) $m->qty, 2, '.', ''), '0'), '.'),
            'unit' => $m->uom ?? $m->product->unit ?? '',
            'batch_no' => trim((string) $m->batch_no) !== '' ? trim((string) $m->batch_no) : '-',
            'expiry_date' => $m->expiry_date ? Carbon::parse($m->expiry_date)->format('d/m/Y') : '-',
            'keterangan' => $this->lineNote($m->reason),
        ])->values()->all();

        $pdf = Pdf::loadView('pdf.warehouse.sj-doc', [
            'doc' => $doc,
            'pihak' => $pihak,
            'alamat' => $alamat,
            'lines' => $lines,
            'dibuat' => $doc->creator?->name ?? 'System',
        ])->setPaper('a4', 'portrait');

        $filename = 'SJ_'.str_replace(['/', '\\'], '-', $doc->doc_no ?: ('DOK-'.$doc->id)).'.pdf';

        return $pdf->download($filename);
    }

    /** Keterangan baris tanpa prefix No. dokumen & label pencatatan. */
    private function lineNote(?string $reason): string
    {
        $text = preg_replace('/^\[[^\]]+\]\s*/', '', (string) $reason);
        $text = preg_replace('/^Pencatatan (masuk|keluar)\s+\d{4}-\d{2}-\d{2}\s*/i', '', (string) $text);

        return trim(preg_replace('/^[-–]\s*/', '', (string) $text));
    }
}

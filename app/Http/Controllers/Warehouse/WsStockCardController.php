<?php

namespace App\Http\Controllers\Warehouse;

use App\Models\Product;
use App\Models\StockMove;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
        if (!$this->hasAnySalesAccess()) {
            abort(403, 'Anda tidak memiliki hak akses ke Kartu Stock.');
        }

        $search = trim((string) $request->get('search', ''));
        // Selalu muat SEMUA produk (tanpa pagination & tanpa filter server):
        // filter murni client-side (live search) supaya mengosongkan search
        // selalu mengembalikan seluruh daftar, bukan subset hasil filter lama.
        $products = Product::active()
            ->orderByRaw('LOWER(COALESCE(NULLIF(TRIM(product_name_clean), ""), product_name))')
            ->get();

        return view('users.warehouse.card-index', [
            'title' => 'Kartu Stock',
            'products' => $products,
            'search' => $search,
            'canManage' => $this->canManageStock(),
        ]);
    }

    /** Detail kartu satu produk + form Masuk/Keluar langsung tervalidasi. */
    public function cardShow(Request $request, Product $product)
    {
        if (!$this->hasAnySalesAccess()) {
            abort(403, 'Anda tidak memiliki hak akses ke Kartu Stock.');
        }

        $viewData = array_merge($this->buildCardData($request, $product), [
            'title' => 'Kartu Stock — ' . ($product->product_name_clean ?: $product->product_name),
            'canManage' => $this->canManageStock(),
            'embed' => $request->boolean('embed'),
        ]);

        // Mode embed: isi popup modal (tanpa layout).
        if ($request->boolean('embed')) {
            return view('users.warehouse.card-detail', $viewData);
        }

        return view('users.warehouse.card-show', $viewData);
    }

    /** Cetak browser isi dokumen export yang sama (bukan halaman kartu). */
    public function cardPrint(Request $request, Product $product)
    {
        if (!$this->hasAnySalesAccess()) {
            abort(403, 'Anda tidak memiliki hak akses ke Kartu Stock.');
        }

        $data = $this->buildCardData($request, $product);

        // Logo sebagai data-URI agar tampil di browser (path lokal tak bisa dibaca browser).
        $logoDataUri = null;
        $logoPath = public_path('images/logo-kop.png');
        if (is_file($logoPath)) {
            $logoDataUri = 'data:' . (mime_content_type($logoPath) ?: 'image/png') . ';base64,' . base64_encode((string) file_get_contents($logoPath));
        }

        return view('pdf.warehouse.card', array_merge($data, [
            'printedBy' => Auth::user()->name ?? 'System',
            'tipeBarang' => '-',
            'noAkdAkl' => '-',
            'noKartu' => 'KS-' . str_pad((string) $product->id, 5, '0', STR_PAD_LEFT),
            'forPrint' => true,
            'logoDataUri' => $logoDataUri,
        ]));
    }

    /** Export PDF Kartu Stock satu produk (periode From s.d. To). */
    public function cardExportPdf(Request $request, Product $product)
    {
        if (!$this->hasAnySalesAccess()) {
            abort(403, 'Anda tidak memiliki hak akses ke Kartu Stock.');
        }

        $data = $this->buildCardData($request, $product);

        $pdf = Pdf::loadView('pdf.warehouse.card', array_merge($data, [
            'printedBy' => Auth::user()->name ?? 'System',
            'tipeBarang' => '-', // TODO: kolom tipe_barang sendiri (menyusul)
            'noAkdAkl' => '-',
            'noKartu' => 'KS-' . str_pad((string) $product->id, 5, '0', STR_PAD_LEFT),
        ]))->setPaper('a4', 'portrait');

        $filename = 'Kartu_Stock_' . ($product->product_code ?? $product->id) . '_' . date('Ymd_His') . '.pdf';

        return $pdf->download($filename);
    }

    // buildCardData() pindah ke WsBaseController (dipakai juga tab Kartu di WsMutationController).

    /**
     * Edit baris pencatatan Kartu Stock (semua kolom kecuali Sisa).
     * - Keterangan/Batch saja berubah -> update langsung (tanpa efek stok).
     * - Qty/Tanggal berubah -> reversal + mutasi koreksi tervalidasi (jejak audit utuh).
     */
    public function cardMoveUpdate(Request $request, StockMove $move)
    {
        if (!$this->canManageStock()) {
            abort(403, 'Hanya pengelola stok yang bisa edit Kartu Stock.');
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
            'qty' => ['required', 'numeric', 'min:0.01'],
            'batch_no' => ['nullable', 'string', 'max:100'],
            'keterangan' => ['required', 'string', 'max:1000'],
        ]);

        $qtyChanged = abs((float) $move->qty - (float) $validated['qty']) >= 0.005;
        $dateChanged = \Carbon\Carbon::parse($move->created_at)->toDateString() !== $validated['tanggal'];

        $svc = app(\App\Services\StockMoveService::class);
        try {
            if (! $qtyChanged && ! $dateChanged) {
                $move->update([
                    'batch_no' => $validated['batch_no'] ?? null,
                    'reason' => $validated['keterangan'],
                ]);
                $okMsg = "Baris #{$move->id} diperbarui (tanpa perubahan stok).";
            } else {
                $okMsg = DB::transaction(function () use ($svc, $move, $validated) {
                    $user = Auth::user();
                    $svc->reverseMove($move, $user, "Koreksi baris #{$move->id} ({$move->reason})");
                    $baru = $svc->createMove([
                        'product_id' => $move->product_id,
                        'qty' => (float) $validated['qty'],
                        'type' => $move->type,
                        'from_location' => $move->from_location,
                        'to_location' => $move->to_location,
                        'batch_no' => $validated['batch_no'] ?? null,
                        'expiry_date' => $move->expiry_date?->toDateString(),
                        'occurred_at' => $validated['tanggal'],
                        'reason' => $validated['keterangan'],
                        'ref_type' => 'stock_move_correction',
                        'ref_id' => $move->id,
                    ], $user);
                    $svc->validateMove($baru, $user);

                    return "Baris #{$move->id} dikoreksi via reversal + mutasi baru #{$baru->id}.";
                });
            }
        } catch (\Throwable $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }

            return back()->with('error', $e->getMessage())->withInput();
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => $okMsg]);
        }

        return back()->with('success', $okMsg);
    }

    /**
     * Hapus baris pencatatan = reversal (stok kembali, jejak audit tetap ada).
     * Baris asli ditandai [dibatalkan] agar terbaca di tabel.
     */
    public function cardMoveDestroy(Request $request, StockMove $move)
    {
        if (!$this->canManageStock()) {
            abort(403, 'Hanya pengelola stok yang bisa hapus baris Kartu Stock.');
        }
        if ($move->status !== StockMove::STATUS_DONE) {
            $msg = 'Hanya baris berstatus selesai yang bisa dihapus.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }

            return back()->with('error', $msg);
        }

        $svc = app(\App\Services\StockMoveService::class);
        try {
            $lawan = $svc->reverseMove($move, Auth::user(), "Hapus baris #{$move->id} ({$move->reason})");
            if (! str_contains((string) $move->reason, '[dibatalkan]')) {
                $move->update(['reason' => trim($move->reason . ' [dibatalkan]')]);
            }
            $okMsg = "Baris #{$move->id} dibatalkan via reversal #{$lawan->id}. Stok dikembalikan.";
        } catch (\Throwable $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }

            return back()->with('error', $e->getMessage());
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => $okMsg]);
        }

        return back()->with('success', $okMsg);
    }
}

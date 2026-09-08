<?php

namespace App\Http\Controllers\Sales;

use App\Models\SphQuotation;
use App\Exports\SphExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class SalesSphController extends BaseSalesController
{
    /**
     * Daftar riwayat SPH (dipakai tab History, dimuat via fetch dari blade).
     *
     * - hasFullSalesAccess()  -> lihat semua riwayat SPH.
     * - hasAnySalesAccess() saja -> hanya lihat SPH di mana dia terdaftar
     *   sebagai Contact Person (ps), dicocokkan ke nama akunnya.
     */
    public function index(Request $request)
    {
        if (!$this->hasAnySalesAccess()) {
            abort(403, 'Anda tidak memiliki hak akses ke halaman SPH.');
        }

        $query = SphQuotation::active()->orderByDesc('created_at');

        if (!$this->hasFullSalesAccess()) {
            $query->whereRaw('LOWER(TRIM(ps)) = ?', [$this->currentUserPsName()]);
        }

        $quotations = $query->get()->map(function ($q) {
            return [
                'id' => $q->id,
                'sphNumber' => $q->sph_number,
                'date' => $q->date->translatedFormat('d F Y'),
                'customerName' => $q->customer_name,
                'customerCompany' => $q->customer_company ?? '-',
                'selectedPs' => $q->ps ?? '-',
                'psPhone' => $q->ps_phone ?? '-',
                'ppnOption' => $q->vat_percent,
                'items' => $q->items,
                'grandTotal' => (float) $q->grand_total,
            ];
        });

        return response()->json($quotations);
    }

    /**
     * Simpan SPH baru. Hanya untuk yang punya hasFullSalesAccess
     * (SPH Form cuma boleh dipakai full access, sesuai tab di halaman pricing).
     */
    public function store(Request $request)
    {
        $this->authorizeFullAccess();

        $validated = $this->validateSph($request);

        $quotation = SphQuotation::createWithNumber([
            'date' => now()->toDateString(),
            'customer_name' => $validated['customerName'] ?? null,
            'customer_company' => $validated['customerCompany'] ?? null,
            'ps' => $validated['selectedPs'] ?? null,
            'ps_phone' => $validated['psPhone'] ?? null,
            'vat_percent' => $validated['ppnOption'] ?? 11,
            'items' => $validated['items'],
            'grand_total' => $validated['grandTotal'],
            'user_id' => Auth::id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Dokumen SPH berhasil dibuat dan disimpan ke History!',
            'data' => [
                'id' => $quotation->id,
                'sphNumber' => $quotation->sph_number,
                'date' => $quotation->date->translatedFormat('d F Y'),
            ],
        ]);
    }

    /**
     * Update SPH yang sudah ada (mode edit). Hanya hasFullSalesAccess.
     */
    public function update(Request $request, SphQuotation $sph)
    {
        $this->authorizeFullAccess();

        $validated = $this->validateSph($request);

        $sph->update([
            'customer_name' => $validated['customerName'] ?? null,
            'customer_company' => $validated['customerCompany'] ?? null,
            'ps' => $validated['selectedPs'] ?? null,
            'ps_phone' => $validated['psPhone'] ?? null,
            'vat_percent' => $validated['ppnOption'] ?? 11,
            'items' => $validated['items'],
            'grand_total' => $validated['grandTotal'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Dokumen SPH berhasil diperbarui!',
        ]);
    }

    /**
     * Halaman detail SPH. Bisa dibuka semua akses sales, tapi untuk
     * non-full-access hanya SPH miliknya sendiri (ps cocok dengan akunnya).
     */
    public function show(SphQuotation $sph)
    {
        $this->authorizeView($sph);

        return view('users.sales.sph-detail', [
            'sph' => $sph,
            'title' => 'Detail SPH #' . $sph->sph_number,
            'hasFullAccess' => $this->hasFullSalesAccess(),
        ]);
    }

    /**
     * Hapus riwayat SPH. Hanya hasFullSalesAccess.
     * Pakai soft delete supaya rekap dokumen tetap utuh di DB.
     *
     * Setelah dihapus, seluruh SPH aktif yang berada "di bawahnya"
     * (sph_sequence lebih besar) diturunkan 1 sehingga nomor surat kembali
     * berurutan tanpa lubang (pola sistem surat-menyurat).
     */
    public function destroy(SphQuotation $sph)
    {
        $this->authorizeFullAccess();

        DB::transaction(function () use ($sph) {
            $deletedSequence = (int) $sph->sph_sequence;

            $below = SphQuotation::active()
                ->where('sph_sequence', '>', $deletedSequence)
                ->orderBy('sph_sequence', 'asc')
                ->get()
                ->map(function ($item) {
                    // Simpan nomor & sequence lama sebelum diubah (untuk
                    // mempertahankan akhiran "/Sales/RAKHA/.../tahun").
                    $newSequence = ((int) $item->sph_sequence) - 1;

                    return [
                        'id'          => $item->id,
                        'new_sequence' => $newSequence,
                        'new_number'  => SphQuotation::sequenceToNumber($item->sph_number, $newSequence),
                    ];
                });

            // 1) Soft delete + tandai tombstone supaya nomornya "skip" dan
            //    tidak menghalangi unique sph_number saat renumber di bawahnya.
            $sph->softDelete();
            $sph->update(['sph_number' => "SKIP-{$sph->id}-{$sph->sph_number}"]);

            // 2) Kosongkan nomor semua calon pakai nilai sementara dulu,
            //    supaya tidak bentrok unique saat update bertahap.
            foreach ($below as $row) {
                DB::table('sph_quotations')
                    ->where('id', $row['id'])
                    ->update(['sph_number' => "TMP-{$row['id']}"]);
            }

            // 3) Pasang sequence & nomor final (turun 1, dari yang terkecil).
            foreach ($below as $row) {
                DB::table('sph_quotations')
                    ->where('id', $row['id'])
                    ->update([
                        'sph_sequence' => $row['new_sequence'],
                        'sph_number'   => $row['new_number'],
                    ]);
            }
        });

        return response()->json(['success' => true]);
    }

    /**
     * Export SPH sebagai PDF (DomPDF).
     * Ikut aturan pembatasan akses yang sama dengan halaman detail (show).
     */
    public function exportPdf(SphQuotation $sph)
    {
        $this->authorizeView($sph);

        $pdf = Pdf::loadView('pdf.sales.sph-document', compact('sph'));
        $pdf->setPaper('a4', 'portrait');

        $filename = 'SPH_' . str_replace('/', '_', $sph->sph_number) . '.pdf';
        return $pdf->download($filename);
    }

    /**
     * Export SPH sebagai Excel (Maatwebsite/Laravel-Excel).
     * Ikut aturan pembatasan akses yang sama dengan halaman detail (show).
     */
    public function exportExcel(SphQuotation $sph)
    {
        $this->authorizeView($sph);

        $filename = 'SPH_' . str_replace('/', '_', $sph->sph_number) . '.xlsx';
        return Excel::download(new SphExport($sph), $filename);
    }

    /**
     * Guard khusus method yang cuma boleh diakses hasFullSalesAccess
     * (create/update/delete SPH -> menyesuaikan tab "SPH Form" yang
     * di frontend juga cuma ditampilkan untuk hasFullSalesAccess).
     */
    /**
     * Guard akses dokumen SPH — dipakai show() dan export (PDF/Excel).
     * - Harus punya akses sales apa pun.
     * - Non-full-access hanya untuk SPH yang dirinya terdaftar sebagai ps.
     */
    private function authorizeView(SphQuotation $sph): void
    {
        if (!$this->hasAnySalesAccess()) {
            abort(403, 'Anda tidak memiliki hak akses ke halaman SPH.');
        }

        if (!$this->hasFullSalesAccess() && strtolower(trim($sph->ps ?? '')) !== $this->currentUserPsName()) {
            abort(403, 'Anda tidak memiliki akses ke dokumen SPH ini.');
        }
    }

    private function authorizeFullAccess(): void
    {
        if (!$this->hasFullSalesAccess()) {
            abort(403, 'Hanya pemegang akses penuh yang dapat membuat/mengubah/menghapus dokumen SPH.');
        }
    }

    /**
     * Nama user yang sedang login, dinormalisasi (lowercase + trim)
     * supaya bisa dicocokkan ke kolom ps (Contact Person) di SPH.
     *
     * NOTE: sesuaikan `name` di bawah ini kalau kolom nama user di
     * tabel users berbeda (mis. `nama`, `nama_lengkap`, dst), dan
     * pastikan penulisannya konsisten dengan daftar psList di blade.
     */
    private function currentUserPsName(): string
    {
        $user = Auth::user();
        return strtolower(trim($user->name ?? ''));
    }

    private function validateSph(Request $request): array
    {
        return $request->validate([
            'customerName' => 'nullable|string|max:255',
            'customerCompany' => 'required|string|max:255',
            'selectedPs' => 'nullable|string|max:255',
            'psPhone' => 'nullable|string|max:50',
            'ppnOption' => 'nullable|integer|in:0,11',
            'items' => 'required|array|min:1',
            'items.*.product_code' => 'nullable|string',
            'items.*.product_name' => 'required|string',
            'items.*.presentation' => 'nullable|string',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.base_price' => 'nullable|numeric|min:0',
            'items.*.pack_qty' => 'nullable|integer|min:1',
            'items.*.discount' => 'nullable|numeric|min:0|max:100',
            'items.*.qty' => 'required|integer|min:1',
            'grandTotal' => 'required|numeric|min:0',
        ]);
    }
}

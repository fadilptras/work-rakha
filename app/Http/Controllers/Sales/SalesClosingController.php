<?php

namespace App\Http\Controllers\Sales;

use App\Models\Sales;
use App\Models\SalesClosing;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

/**
 * CRUD tabel sales_closings (closing penjualan + rencana pembelian).
 * Satu method satu tanggung jawab; semua response JSON {success, message, data}.
 */
class SalesClosingController extends BaseSalesController
{
    /**
     * Check if sales_closings table exists, return graceful error if not.
     */
    private function ensureTableExists(): ?array
    {
        if (!Schema::hasTable('sales_closings')) {
            return ['error' => 'Tabel closing belum dimigrasi di server ini.', 'code' => 503];
        }
        return null;
    }

    public function index(Request $request)
    {
        if (!$this->hasAnySalesAccess()) {
            return response()->json(['error' => 'Unauthorized.'], 403);
        }

        $tableCheck = $this->ensureTableExists();
        if ($tableCheck) return response()->json($tableCheck, $tableCheck['code']);

        $validated = $request->validate([
            'year' => 'nullable|integer',
            'month' => 'nullable|string|max:20',
            'type' => 'nullable|in:sales,purchase',
            'only_manual' => 'nullable|boolean',
        ]);

        $isFull = $this->hasFullSalesAccess();
        $rows = SalesClosing::query()
            ->when(isset($validated['year']), fn ($q) => $q->where('year', $validated['year']))
            ->when(isset($validated['month']), fn ($q) => $q->whereRaw('LOWER(month) = ?', [strtolower($validated['month'])]))
            ->when(isset($validated['type']), fn ($q) => $q->where('type', $validated['type']))
            ->when(!$isFull, fn ($q) => $q->where(function ($qq) {
                $qq->whereRaw("LOWER(ps) != 'office'")->orWhereNull('ps');
            }))
            ->orderByDesc('updated_at')
            ->limit(500)
            ->get();

        // only_manual: hanya entri baru dari form (pasangan ps|customer-nya tidak ada di sales bulan itu).
        // Cek per PS, bukan global customer — customer yg sama beda PS tetap manual untuk PS-nya.
        // Baris tanpa PS (null) dicek global per customer.
        if (!empty($validated['only_manual']) && isset($validated['year'], $validated['month'])) {
            $salesPairsQuery = Sales::whereYear('date', $validated['year'])
                ->whereRaw('LOWER(month) = ?', [strtolower($validated['month'])])
                ->whereNotNull('customer_name');
            if (!$isFull) {
                $salesPairsQuery->where(function ($qq) {
                    $qq->whereRaw("LOWER(ps) != 'office'")->orWhereNull('ps');
                });
            }
            $salesPairs = $salesPairsQuery->select('ps', 'customer_name')->get();
            $salesPsCust = $salesPairs
                ->map(fn ($s) => strtolower(trim((string) ($s->ps ?? ''))).'|'.strtolower(trim((string) ($s->customer_name ?? ''))))
                ->flip();
            $salesCusts = $salesPairs
                ->map(fn ($s) => strtolower(trim((string) ($s->customer_name ?? ''))))
                ->flip();
            $rows = $rows->filter(function ($r) use ($salesPsCust, $salesCusts) {
                $rCust = strtolower(trim((string) ($r->customer_name ?? '')));
                $rPs = strtolower(trim((string) ($r->ps ?? '')));
                if ($rPs === '') {
                    return !isset($salesCusts[$rCust]);
                }
                return !isset($salesPsCust[$rPs.'|'.$rCust]);
            })->values();
        }

        return response()->json(['success' => true, 'data' => $rows]);
    }

    // ---------- Tambah (upsert per kunci alami) ----------

    public function store(Request $request)
    {
        if (!$this->hasFullSalesAccess()) {
            return response()->json(['error' => 'Unauthorized. Hanya pengguna dengan akses penuh yang dapat menambah closing.'], 403);
        }

        $tableCheck = $this->ensureTableExists();
        if ($tableCheck) return response()->json($tableCheck, $tableCheck['code']);

        $validated = $request->validate([
            'type' => 'nullable|in:sales,purchase',
            'year' => 'required|integer',
            'month' => 'required|string|max:20',
            'ps' => 'nullable|string|max:255',
            'customer_name' => 'nullable|string|max:255',
            'supplier_name' => 'nullable|string|max:255',
            'product_name' => 'nullable|string|max:255',
            'qty' => 'nullable|string|max:50',
            'unit' => 'nullable|string|max:50',
            'amount' => 'required|numeric|min:0',
            'note' => 'nullable|string|max:255',
        ]);

        // amount 0 = hapus (user kembalikan ke 0) — biar tidak nyangkut 0
        if ((float) $validated['amount'] == 0) {
            $existing = $this->findByNaturalKey($validated['year'], $validated['month'], [
                'ps' => $validated['ps'] ?? null,
                'customer_name' => $validated['customer_name'] ?? null,
                'supplier_name' => $validated['supplier_name'] ?? null,
                'product_name' => $validated['product_name'] ?? null,
            ]);
            if ($existing) {
                $existing->delete();
                return response()->json(['success' => true, 'message' => 'Closing dihapus (amount 0).', 'data' => null]);
            }
            return response()->json(['success' => true, 'message' => 'Tidak ada data untuk dihapus.', 'data' => null]);
        }

        $date = $this->resolveDate($validated['year'], $validated['month']);
        if (!$date) {
            return response()->json(['success' => false, 'message' => 'Nama bulan tidak valid.'], 422);
        }

        $attrs = $this->naturalKey($validated);
        $row = SalesClosing::updateOrCreate($attrs, [
            'date' => $date->toDateString(),
            'product_name' => $validated['product_name'] ?? null,
            'qty' => $validated['qty'] ?? null,
            'unit' => $validated['unit'] ?? null,
            'amount' => $validated['amount'],
            'note' => $validated['note'] ?? null,
            'created_by' => Auth::id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => $row->wasRecentlyCreated ? 'Closing berhasil ditambahkan.' : 'Closing diperbarui.',
            'data' => $row,
        ], $row->wasRecentlyCreated ? 201 : 200);
    }

    // ---------- Ubah (pindah kunci alami + nominal) ----------

    public function update(Request $request)
    {
        if (!$this->hasFullSalesAccess()) {
            return response()->json(['error' => 'Unauthorized.'], 403);
        }

        $tableCheck = $this->ensureTableExists();
        if ($tableCheck) return response()->json($tableCheck, $tableCheck['code']);

        $validated = $request->validate([
            'year' => 'required|integer',
            'month' => 'required|string|max:20',
            'old_ps' => 'nullable|string|max:255',
            'old_customer_name' => 'nullable|string|max:255',
            'old_supplier_name' => 'nullable|string|max:255',
            'old_product_name' => 'nullable|string|max:255',
            'ps' => 'nullable|string|max:255',
            'customer_name' => 'nullable|string|max:255',
            'supplier_name' => 'nullable|string|max:255',
            'product_name' => 'nullable|string|max:255',
            'qty' => 'nullable|string|max:50',
            'unit' => 'nullable|string|max:50',
            'amount' => 'nullable|numeric|min:0',
            'note' => 'nullable|string|max:255',
        ]);

        $row = $this->findByNaturalKey($validated['year'], $validated['month'], [
            'ps' => $validated['old_ps'] ?? null,
            'customer_name' => $validated['old_customer_name'] ?? null,
            'supplier_name' => $validated['old_supplier_name'] ?? null,
            'product_name' => $validated['old_product_name'] ?? null,
        ]);
        if (!$row) {
            return response()->json(['success' => false, 'message' => 'Data closing tidak ditemukan.'], 404);
        }

        $newPs = trim((string) ($validated['ps'] ?? ''));
        $newCust = trim((string) ($validated['customer_name'] ?? ''));
        $newSupp = trim((string) ($validated['supplier_name'] ?? ''));
        $newProd = trim((string) ($validated['product_name'] ?? ''));
        if ($newCust === '' && $newSupp === '') {
            return response()->json(['success' => false, 'message' => 'Customer atau supplier wajib diisi.'], 422);
        }

        $clash = $this->findByNaturalKey($validated['year'], $validated['month'], [
            'ps' => $newPs !== '' ? $newPs : null,
            'customer_name' => $newCust !== '' ? $newCust : null,
            'supplier_name' => $newSupp !== '' ? $newSupp : null,
            'product_name' => $newProd !== '' ? $newProd : null,
        ]);
        if ($clash && $clash->id !== $row->id) {
            return response()->json(['success' => false, 'message' => 'Nama tersebut sudah ada — gunakan nama lain.'], 422);
        }

        // Jika amount di-set 0 = hapus (rapi, tidak simpan 0)
        if (array_key_exists('amount', $validated) && $validated['amount'] !== null && (float) $validated['amount'] == 0) {
            $row->delete();
            return response()->json(['success' => true, 'message' => 'Closing dihapus (amount 0).', 'data' => null]);
        }

        $payload = [
            'ps' => $newPs !== '' ? $newPs : null,
            'customer_name' => $newCust !== '' ? $newCust : null,
            'supplier_name' => $newSupp !== '' ? $newSupp : null,
        ];
        foreach (['product_name', 'qty', 'unit', 'note', 'amount'] as $field) {
            if (array_key_exists($field, $validated) && $validated[$field] !== null) {
                $payload[$field] = $validated[$field];
            }
        }
        $row->update($payload);

        return response()->json(['success' => true, 'message' => 'Closing berhasil diubah.', 'data' => $row->fresh()]);
    }

    // ---------- Hapus ----------

    public function destroy(Request $request)
    {
        if (!$this->hasFullSalesAccess()) {
            return response()->json(['error' => 'Unauthorized.'], 403);
        }

        $tableCheck = $this->ensureTableExists();
        if ($tableCheck) return response()->json($tableCheck, $tableCheck['code']);

        $validated = $request->validate([
            'year' => 'required|integer',
            'month' => 'required|string|max:20',
            'ps' => 'nullable|string|max:255',
            'customer_name' => 'nullable|string|max:255',
            'supplier_name' => 'nullable|string|max:255',
            'product_name' => 'nullable|string|max:255',
        ]);

        $row = $this->findByNaturalKey($validated['year'], $validated['month'], [
            'ps' => $validated['ps'] ?? null,
            'customer_name' => $validated['customer_name'] ?? null,
            'supplier_name' => $validated['supplier_name'] ?? null,
            'product_name' => $validated['product_name'] ?? null,
        ]);
        if (!$row) {
            return response()->json(['success' => true, 'message' => 'Data tidak ditemukan (mungkin sudah terhapus).']);
        }
        $row->delete();

        return response()->json(['success' => true, 'message' => 'Closing berhasil dihapus.']);
    }

    // ---------- Helper privat ----------

    private function naturalKey(array $input): array
    {
        $ps = trim((string) ($input['ps'] ?? ''));
        $cust = trim((string) ($input['customer_name'] ?? ''));
        $supp = trim((string) ($input['supplier_name'] ?? ''));
        $prod = trim((string) ($input['product_name'] ?? ''));

        return [
            'type' => $input['type'] ?? SalesClosing::TYPE_SALES,
            'year' => (int) $input['year'],
            'month' => ucfirst(strtolower(trim($input['month']))),
            'ps' => $ps !== '' ? $ps : null,
            'customer_name' => $cust !== '' ? $cust : null,
            'supplier_name' => $supp !== '' ? $supp : null,
            'product_name' => $prod !== '' ? $prod : null,
        ];
    }

    private function findByNaturalKey(int $year, string $month, array $parties): ?SalesClosing
    {
        // Pencocokan case-insensitive + trim, null vs '' dianggap sama (sesuai naturalKey)
        $ps = isset($parties['ps']) ? trim((string) $parties['ps']) : '';
        $cust = isset($parties['customer_name']) ? trim((string) $parties['customer_name']) : '';
        $supp = isset($parties['supplier_name']) ? trim((string) $parties['supplier_name']) : '';
        $prod = isset($parties['product_name']) ? trim((string) $parties['product_name']) : '';
        return SalesClosing::where('year', $year)
            ->whereRaw('LOWER(month) = ?', [strtolower(trim($month))])
            ->when($ps !== '', fn ($q) => $q->whereRaw('LOWER(ps) = ?', [strtolower($ps)]), fn ($q) => $q->whereNull('ps'))
            ->when($cust !== '', fn ($q) => $q->whereRaw('LOWER(customer_name) = ?', [strtolower($cust)]), fn ($q) => $q->whereNull('customer_name'))
            ->when($supp !== '', fn ($q) => $q->whereRaw('LOWER(supplier_name) = ?', [strtolower($supp)]), fn ($q) => $q->whereNull('supplier_name'))
            ->when($prod !== '', fn ($q) => $q->whereRaw('LOWER(product_name) = ?', [strtolower($prod)]), fn ($q) => $q->whereNull('product_name'))
            ->first();
    }

    private function resolveDate(int $year, string $month): ?Carbon
    {
        try {
            $m = Carbon::createFromFormat('!F', ucfirst(strtolower(trim($month))));
        } catch (\Exception $e) {
            return null;
        }
        if (!$m) return null;

        return Carbon::create($year, (int) $m->format('m'), 1)->endOfMonth();
    }
}

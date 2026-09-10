<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductPackaging;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * CRUD master aturan packaging (tabel product_packaging).
 * Dipakai oleh modal di halaman Data Barang. Semua respon JSON.
 */
class AdminProductPackagingController extends Controller
{
    /** Data lengkap untuk modal. */
    public function index()
    {
        $rows = ProductPackaging::orderBy('id')->get();

        return response()->json([
            'data' => $rows->map(fn ($r) => $this->serialize($r)),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);

        $row = ProductPackaging::create([
            'packaging' => $data['packaging'],
            'pack' => $data['pack'],
            'type' => ProductPackaging::typeFromPack($data['pack']),
        ]);

        return response()->json([
            'message' => 'Aturan packaging "' . $row->packaging . '" ditambahkan.',
            'data' => $this->serialize($row),
        ], 201);
    }

    public function update(Request $request, ProductPackaging $packaging)
    {
        $data = $this->validateData($request, $packaging);

        $packaging->update([
            'packaging' => $data['packaging'],
            'pack' => $data['pack'],
            'type' => ProductPackaging::typeFromPack($data['pack']),
        ]);

        return response()->json([
            'message' => 'Aturan packaging "' . $packaging->packaging . '" diperbarui.',
            'data' => $this->serialize($packaging),
        ]);
    }

    /** Hapus permanen (master packaging tidak memakai soft delete). */
    public function destroy(ProductPackaging $packaging)
    {
        $packaging->delete();

        return response()->json([
            'message' => 'Aturan packaging "' . $packaging->packaging . '" dihapus.',
        ]);
    }

    private function validateData(Request $request, ?ProductPackaging $ignore = null): array
    {
        $validated = $request->validate([
            'packaging' => [
                'required', 'string', 'max:100',
                Rule::unique('product_packaging', 'packaging')->ignore($ignore?->id),
            ],
            'pack' => ['present', 'array'],
            'pack.*' => ['required', 'string', 'max:50'],
        ]);

        return [
            'packaging' => trim($validated['packaging']),
            'pack' => array_values(array_filter(
                array_map(fn ($u) => trim((string) $u), $validated['pack'] ?? []),
                fn ($u) => $u !== ''
            )),
        ];
    }

    private function serialize(ProductPackaging $row): array
    {
        return [
            'id' => $row->id,
            'packaging' => $row->packaging,
            'type' => ProductPackaging::typeFromPack($row->pack),
            'pack' => $row->pack,
        ];
    }
}
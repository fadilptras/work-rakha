<?php

namespace App\Models;

use App\Models\Concerns\SoftDeletesFlag;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory, SoftDeletesFlag;

    protected $table = 'products';

    protected $fillable = [
        'product_code',
        'product_code_clean',
        'product_name',
        'product_name_clean',
        'match_key',
        'unit',
        'pcs_per_unit',
        'fill_unit',
        'stock',
        'stock_po',
        'is_deleted',
    ];

    protected $casts = [
        'stock' => 'integer',
        'stock_po' => 'integer',
        'pcs_per_unit' => 'integer',
        'is_deleted' => 'boolean',
    ];

    /**
     * Build a normalized match_key from a product name.
     * Lowercase, strip all non-alphanumeric, collapse.
     */
    public static function buildMatchKey(?string $name): string
    {
        $key = strtolower($name ?? '');
        $key = preg_replace('/[^a-z0-9]/', '', $key);

        return $key;
    }

    /* ============================================================
     * Soft Delete (is_deleted flag) — lihat trait SoftDeletesFlag.
     *
     * products memakai soft-delete: saat barangs dihapus, produk
     * terkait tidak ikut hilang melainkan ditandai is_deleted = 1
     * supaya rekap data tetap utuh. Pendaftaran ulang dengan kode
     * yang sama mengaktifkan kembali tombstone via restore().
     * ============================================================ */

    /**
     * Kolom kurasi yang di-reset saat restore, supaya produk kembali
     * antre untuk dikurasi ulang di halaman Aturan Produk.
     */
    protected function restoreResets(): array
    {
        return [
            'product_code_clean' => null,
            'product_name_clean' => null,
            'pcs_per_unit' => null,
            'fill_unit' => 'Pcs',
        ];
    }

    /**
     * Isi per satuan jual yang dipakai untuk konversi stok.
     * = pcs_per_unit bila diisi (>1), selain itu 1.
     */
    public function packQty(): int
    {
        $pcsPerUnit = (int) ($this->pcs_per_unit ?? 0);

        return $pcsPerUnit >= 1 ? $pcsPerUnit : 1;
    }

    /**
     * Display quantity with pack detail.
     * Example: 300 pcs → "3 Polybag (300 pcs)"
     */
    public function formatStock(): string
    {
        $stock = (int) ($this->stock ?? 0);
        $unit = $this->unit ?: 'pcs';
        $fillUnit = $this->fill_unit ?: 'pcs';
        $pcsPerUnit = $this->packQty();

        if ($pcsPerUnit <= 1) {
            return "{$stock} {$unit}";
        }

        $packQty = intdiv($stock, $pcsPerUnit);
        $remainder = $stock % $pcsPerUnit;

        if ($packQty === 0 || $remainder > 0) {
            return "{$stock} {$fillUnit}";
        }

        return "{$packQty} {$unit} ({$stock} {$fillUnit})";
    }
}
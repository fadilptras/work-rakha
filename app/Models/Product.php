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
        'min_stock',
        'reorder_qty',
        'is_deleted',
    ];

    protected $casts = [
        'stock' => 'integer',
        'stock_po' => 'integer',
        'min_stock' => 'decimal:2',
        'reorder_qty' => 'decimal:2',
        'pcs_per_unit' => 'integer',
        'is_deleted' => 'boolean',
    ];

    /**
     * Buat match_key ternormalisasi dari nama produk.
     * Huruf kecil dan hanya alfanumerik.
     */
    public static function buildMatchKey(?string $name): string
    {
        $key = strtolower($name ?? '');
        $key = preg_replace('/[^a-z0-9]/', '', $key);

        return $key;
    }

    /**
     * Kolom kurasi yang di-reset saat restore.
     * Produk memakai soft-delete (is_deleted), jadi restore
     * mengembalikan produk agar antre kurasi ulang di Aturan Produk.
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
     * Isi per satuan jual untuk konversi stok.
     * Mengembalikan pcs_per_unit jika >= 1, selain itu 1.
     */
    public function packQty(): int
    {
        $pcsPerUnit = (int) ($this->pcs_per_unit ?? 0);

        return $pcsPerUnit >= 1 ? $pcsPerUnit : 1;
    }

    /**
     * Format stok dengan detail kemasan.
     * Contoh: 300 pcs -> "3 Polybag (300 pcs)".
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

    public function batches(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ProductBatch::class);
    }

    public function moves(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(StockMove::class);
    }

    public function reorderingRule(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(ReorderingRule::class);
    }
}
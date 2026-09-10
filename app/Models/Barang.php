<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Barang extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_code',
        'product_name',
        'unit',
        'presentation',
        'stock',
        'stock_po',
        'is_active',
    ];

    /**
     * Isi per kemasan (jumlah satuan terkecil) yang dinyatakan di kolom "sediaan".
     * Contoh: "Bag/ 100 Pcs" -> 100, "Box/ 50 Pasang" -> 50, "Karton/ 500" -> 500.
     * Tanpa tanda "/" (Pcs, Roll, Botol, dll.) dianggap 1.
     */
    public static function packCount(?string $presentation): int
    {
        if ($presentation !== null && str_contains($presentation, '/')) {
            $part = strtolower($presentation);
            if (preg_match('/(\d+)\s*(?:pcs|pasang)?\s*$/', $part, $m)) {
                return max(1, (int) $m[1]);
            }
        }

        return 1;
    }
}
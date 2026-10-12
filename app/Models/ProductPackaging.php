<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Master aturan packaging.
 * User isi `packaging` + `pack`; `type` dihitung otomatis:
 * [] -> single, 1 item -> fixed, 2+ -> flex.
 * Hapus permanen (tanpa soft-delete).
 */
class ProductPackaging extends Model
{
    use HasFactory;

    protected $table = 'product_packaging';

    protected $fillable = [
        'packaging',
        'pack',
        'type',
    ];

    protected $casts = [
        'pack' => 'array',
    ];

    public const TYPE_SINGLE = 'single';
    public const TYPE_FIXED = 'fixed';
    public const TYPE_FLEX = 'flex';

    /** Hitung type dari jumlah isi pack. */
    public static function typeFromPack(array $pack): string
    {
        $count = count(array_values(array_filter($pack, fn ($u) => trim((string) $u) !== '')));

        if ($count === 0) {
            return static::TYPE_SINGLE;
        }
        if ($count === 1) {
            return static::TYPE_FIXED;
        }

        return static::TYPE_FLEX;
    }

    /** Normalisasi kunci: lowercase + trim. */
    public static function normalize(?string $value): string
    {
        return strtolower(trim((string) $value));
    }
}
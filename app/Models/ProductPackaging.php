<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Master aturan packaging — satu sumber kebenaran aturan packaging.
 *
 * Hanya `packaging` (nama kemasan) + `pack` (daftar satuan isi) yang diisi
 * user. `type` dihitung otomatis dari jumlah isi pack:
 *  - pack kosong []          -> 'single' (isi otomatis 1)
 *  - pack 1 item ["Pcs"]     -> 'fixed'  (wajib pilih satuan isi)
 *  - pack 2+ item             -> 'flex'   (bisa pilih salah satu)
 *
 * Penghapusan bersifat permanen (hard delete).
 * Soft delete (is_deleted) tidak dipakai di tabel master ini, hanya di tabel products.
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

    /** Hitung type berdasarkan jumlah satuan isi (pack). */
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

    /** Kunci lookup normal (lowercase+trim) supaya "Polybag" == "polybag". */
    public static function normalize(?string $value): string
    {
        return strtolower(trim((string) $value));
    }
}
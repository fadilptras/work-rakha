<?php

namespace App\Support;

use App\Models\ProductPackaging;

/**
 * Aturan "packaging" / satuan jual produk — satu sumber kebenaran.
 *
 * Sumber data: tabel `product_packaging` (master satuan, data-driven).
 * Menambah/mengubah satuan = insert/update row di tabel, tanpa ubah kode.
 *
 * Tipe satuan:
 *  - single : satuan tunggal (Pcs/Roll/Botol) -> isi otomatis 1, tidak bisa diisi
 *  - flex   : isi bebas (Pack/Box) -> boleh diisi
 *  - fixed  : wajib isi (Polybag/Bag/Pouches/Karton)
 */
class PackagingCatalog
{
    /** Preset cepat isi per satuan (UI; global, bukan per-packaging). */
    public const PRESETS = [1, 10, 100, 1000];

    /** Cache dalam satu request (tabel master kecil, cukup di-muat sekali). */
    private static ?array $entries = null;

    /**
     * Semua packaging aktif sebagai array:
     * ['polybag' => ['type' => 'fixed', 'units' => ['Pcs']], ...]
     * key dinormalisasi lowercase, nilai siap JSON untuk Alpine.
     */
    public static function catalog(): array
    {
        $catalog = [];
        foreach (self::entries() as $entry) {
            $key = ProductPackaging::normalize($entry->packaging);
            $catalog[$key] = [
                'type' => ProductPackaging::typeFromPack($entry->pack),
                'units' => $entry->pack,
            ];
        }

        return $catalog;
    }

    /** Daftar satuan yang disarankan (packaging aktif, urutan insert). */
    public static function suggestions(): array
    {
        return array_map(fn ($e) => $e->packaging, self::entries());
    }

    public static function presets(): array
    {
        return static::PRESETS;
    }

    /**
     * Metadata satuan (dari tabel product_packaging).
     * Satuan tak dikenal dianggap 'flex' dengan isi 'Pcs' (perilaku default).
     */
    public static function metaFor(string $unit): array
    {
        $key = ProductPackaging::normalize($unit);

        return self::catalog()[$key] ?? ['type' => 'flex', 'units' => ['Pcs']];
    }

    public static function typeFor(string $unit): string
    {
        return static::metaFor($unit)['type'];
    }

    public static function unitsFor(string $unit): array
    {
        return static::metaFor($unit)['units'] ?? ['Pcs'];
    }

    public static function isSingle(string $unit): bool
    {
        return static::typeFor($unit) === 'single';
    }

    /** Satuan fixed (polybag/bag/pouches/karton) wajib punya isi. */
    public static function needsContent(string $unit): bool
    {
        return static::typeFor($unit) === 'fixed';
    }

    /** Bisakah diisi? Gunakan untuk memutuskan apakah input isi perlu ditampilkan. */
    public static function canFill(string $unit): bool
    {
        return !static::isSingle($unit);
    }

    /**
     * pack_qty / pcs_per_unit.
     * = isi (>=1) jika satuan bisa diisi, selain itu 1.
     */
    public static function resolvePackQty(?string $unit, ?int $pcsPerUnit): int
    {
        $isi = (int) ($pcsPerUnit ?? 0);
        $canFill = $unit !== null && static::canFill($unit);

        return ($canFill && $isi >= 1) ? $isi : 1;
    }

    /**
     * Format tampilan packaging yang disimpan.
     * Contoh: 'General', 'Roll', 'Polybag/ 100 Pcs', 'Box/ 50 Pasang'.
     */
    public static function packagingPreview(?string $unit, ?int $pcsPerUnit, ?string $fillUnit): string
    {
        $pkg = trim((string) $unit);
        $isi = (int) ($pcsPerUnit ?? 0);
        $fill = $fillUnit ?: 'Pcs';

        if ($pkg === '') {
            return $isi > 0 ? "{$isi} {$fill}" : 'General';
        }

        if (static::isSingle($pkg) || $isi < 1) {
            return $pkg;
        }

        return "{$pkg}/ {$isi} {$fill}";
    }

    /**
     * Parsing balik string packaging jadi komponen.
     * Contoh: 'Polybag/ 100 Pcs' -> {unit, pcs_per_unit, fill_unit}.
     */
    public static function parse(?string $presentation): array
    {
        if ($presentation === null || trim($presentation) === '') {
            return ['unit' => '', 'pcs_per_unit' => null, 'fill_unit' => 'Pcs'];
        }

        $idx = strpos($presentation, '/');
        if ($idx === false) {
            return ['unit' => trim($presentation), 'pcs_per_unit' => null, 'fill_unit' => 'Pcs'];
        }

        $unit = trim(substr($presentation, 0, $idx));
        if (preg_match('/(\d+)\s*([a-zA-Z]*)/', trim(substr($presentation, $idx + 1)), $m)) {
            return [
                'unit' => $unit,
                'pcs_per_unit' => (int) $m[1],
                'fill_unit' => ($m[2] !== '') ? $m[2] : 'Pcs',
            ];
        }

        return ['unit' => $unit, 'pcs_per_unit' => null, 'fill_unit' => 'Pcs'];
    }

    /**
     * Data siap JSON untuk Alpine (dipakai view form Aturan Produk & pricing).
     */
    public static function toArray(): array
    {
        return [
            'catalog' => static::catalog(),
            'suggestions' => static::suggestions(),
            'presets' => static::PRESETS,
        ];
    }

    /** Reset cache dalam request (dipakai test/seed). */
    public static function flush(): void
    {
        self::$entries = null;
    }

    private static function entries(): array
    {
        if (self::$entries === null) {
            self::$entries = ProductPackaging::query()
                ->orderBy('id')
                ->get()
                ->all();
        }

        return self::$entries;
    }
}
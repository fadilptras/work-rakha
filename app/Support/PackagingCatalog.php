<?php

namespace App\Support;

use App\Models\ProductPackaging;

/**
 * Aturan packaging produk (sumber: tabel product_packaging).
 * Ubah satuan via DB, tanpa ubah kode.
 * Tipe: single (isi 1), fixed (wajib isi), flex (boleh isi).
 */
class PackagingCatalog
{
    /** Preset isi cepat untuk UI. */
    public const PRESETS = [1, 10, 100, 1000];

    /** Cache request (master kecil, muat sekali). */
    private static ?array $entries = null;

    /** Semua packaging aktif (key lowercase, siap JSON). */
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

    /** Daftar nama packaging aktif. */
    public static function suggestions(): array
    {
        return array_map(fn ($e) => $e->packaging, self::entries());
    }

    public static function presets(): array
    {
        return static::PRESETS;
    }

    /** Meta satuan; tak dikenal = flex + Pcs. */
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

    /** Satuan fixed wajib isi. */
    public static function needsContent(string $unit): bool
    {
        return static::typeFor($unit) === 'fixed';
    }

    /** True bila input isi perlu ditampilkan. */
    public static function canFill(string $unit): bool
    {
        return !static::isSingle($unit);
    }

    /** Isi per kemasan; 1 bila single / kosong. */
    public static function resolvePackQty(?string $unit, ?int $pcsPerUnit): int
    {
        $isi = (int) ($pcsPerUnit ?? 0);
        $canFill = $unit !== null && static::canFill($unit);

        return ($canFill && $isi >= 1) ? $isi : 1;
    }

    /** Teks tampil: 'Polybag/ 100 Pcs'. */
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

    /** Parse 'Polybag/ 100 Pcs' -> unit, pcs, fill. */
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

    /** Data JSON untuk Alpine. */
    public static function toArray(): array
    {
        return [
            'catalog' => static::catalog(),
            'suggestions' => static::suggestions(),
            'presets' => static::PRESETS,
        ];
    }

    /** Reset cache (test/seed). */
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
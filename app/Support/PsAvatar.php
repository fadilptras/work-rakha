<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Satu pintu resolusi foto personel (PS) dari nama lengkap.
 *
 * Nama PS di data transaksional (sales.ps, sales_targets.ps) selalu nama
 * lengkap yang sama persis dengan users.name, sehingga pencocokan cukup
 * deterministik (tanpa fuzzy token yang berisiko salah orang):
 *  1. persis (case-sensitive),
 *  2. case-insensitive + trim,
 *  3. alias eksplisit (mis. 'rusiman'/'hendra' -> 'Rusiman Hendra Dipraja').
 *
 * Bila tidak ketemu atau file tidak ada, kembalikan fallback yang rapi
 * (bukan broken-image).
 */
class PsAvatar
{
    /**
     * Deklarasi alias nama -> nama lengkap users.name.
     * Key dinormalisasi (lowercase + trim) saat pencocokan.
     */
    private const ALIASES = [
        'rusiman' => 'Rusiman Hendra Dipraja',
        'hendra'  => 'Rusiman Hendra Dipraja',
    ];

    /**
     * Override foto eksplisit (nama lengkap lowercase -> path storage).
     * Dipanggil langsung TANPA cek file_exists — dipakai bila file dipastikan
     * ada di production meski tidak ada di lokal/dev.
     */
    private const PHOTO_OVERRIDES = [
        'rusiman hendra dipraja' => 'profile_pictures/6a705b0e0db66.png',
    ];

    public static function fallback(string $name, string $background = '0ea5e9'): string
    {
        return 'https://ui-avatars.com/api/?name=' . urlencode($name)
            . '&background=' . $background . '&color=fff&rounded=true&bold=true';
    }

    public static function photoUrl(?User $user): ?string
    {
        $path = $user?->profile_picture
            ? public_path('storage/' . $user->profile_picture)
            : null;

        if ($path && file_exists($path)) {
            return asset('storage/' . $user->profile_picture);
        }

        return null;
    }

    public static function resolveUser(?string $psName, ?Collection $users = null): ?User
    {
        $psName = trim((string) ($psName ?? ''));
        if ($psName === '') {
            return null;
        }

        $users ??= User::get(['name', 'profile_picture']);

        $exact = $users->firstWhere('name', $psName);
        if ($exact) {
            return $exact;
        }

        $norm = mb_strtolower($psName);

        // Alias yang dideklarasikan eksplisit (prioritas tertinggi setelah persis)
        if (isset(self::ALIASES[$norm])) {
            $aliased = $users->firstWhere('name', self::ALIASES[$norm]);
            if ($aliased) {
                return $aliased;
            }
        }

        foreach ($users as $u) {
            if (mb_strtolower(trim((string) $u->name)) === $norm) {
                return $u;
            }
        }

        return null;
    }

    /**
     * Peta nama PS -> URL foto, siap di-@json ke Blade.
     * Key = nama persis seperti di data sumber (dipakai JS untuk lookup).
     */
    public static function map(array $psNames): array
    {
        $users = User::get(['name', 'profile_picture']);
        $map = [];

        foreach ($psNames as $psName) {
            $psName = (string) $psName;
            $user = self::resolveUser($psName, $users);
            $overrideKey = mb_strtolower(trim($user?->name ?? $psName));
            if (isset(self::PHOTO_OVERRIDES[$overrideKey])) {
                $map[$psName] = asset('storage/' . self::PHOTO_OVERRIDES[$overrideKey]);
                continue;
            }
            $url = self::photoUrl($user);
            $map[$psName] = $url ?? self::fallback($psName);
        }

        return $map;
    }
}

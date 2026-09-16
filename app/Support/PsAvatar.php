<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Satu pintu resolusi foto personel (PS) dari tulisan nama bebas.
 *
 * Masalah yang dipecahkan: nama di tabel transaksional (mis. sales.ps)
 * diketik manual sehingga varian tulisan muncul ('rusiman',
 * ' RUSIMAN ', 'Hendra'), sementara foto tersimpan di users.profile_picture.
 * Helper ini mencocokkan berlapis supaya foto tetap ketemu:
 *  1. persis (case-sensitive),
 *  2. case-insensitive + trim,
 *  3. token nama depan,
 *  4. token mana pun ('hendra' -> 'Rusiman Hendra Dipraja').
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

    public static function resolveUser(string $psName, ?Collection $users = null): ?User
    {
        $users ??= User::get(['name', 'profile_picture']);

        $exact = $users->firstWhere('name', $psName);
        if ($exact) {
            return $exact;
        }

        $norm = mb_strtolower(trim($psName));

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

        $tokensOf = fn(string $s): array => array_values(array_filter(
            explode(' ', preg_replace('/\s+/', ' ', mb_strtolower(trim($s))))
        ));

        $first = $tokensOf($psName)[0] ?? '';
        if ($first !== '') {
            foreach ($users as $u) {
                if (($tokensOf((string) $u->name)[0] ?? '') === $first) {
                    return $u;
                }
            }
        }

        foreach ($users as $u) {
            if (in_array($norm, $tokensOf((string) $u->name), true)) {
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
            $url = self::photoUrl(self::resolveUser($psName, $users));
            $map[$psName] = $url ?? self::fallback($psName);
        }

        return $map;
    }
}

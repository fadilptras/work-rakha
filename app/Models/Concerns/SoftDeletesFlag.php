<?php

namespace App\Models\Concerns;

/**
 * Soft delete sederhana memakai kolom is_deleted (flag boolean).
 *
 * Berbeda dengan trait bawaan SoftDeletes (yang mengisi deleted_at),
 * pola ini memakai is_deleted = 1 untuk menandai baris yang "dihapus"
 * supaya datanya tetap tersimpan utuh untuk rekap / restore.
 *
 * Setiap model pemakai trait wajib punya kolom `is_deleted`.
 */
trait SoftDeletesFlag
{
    /** Scope: hanya baris aktif (belum di-soft-delete). */
    public function scopeActive($query)
    {
        return $query->where('is_deleted', 0);
    }

    /** Scope: hanya baris yang sudah di-soft-delete (tombstone). */
    public function scopeTrashed($query)
    {
        return $query->where('is_deleted', 1);
    }

    /** Apakah baris sedang dalam status dihapus? */
    public function isDeleted(): bool
    {
        return (bool) $this->is_deleted;
    }

    /** Soft delete: colok flag is_deleted = 1. */
    public function softDelete(): bool
    {
        $this->is_deleted = 1;

        return $this->save();
    }

    /**
     * Hidupkan kembali tombstone (is_deleted = 0) dengan sinkron data opsional.
     *
     * @param  array  $sync  kolom tambahan yang ikut diset saat restore.
     */
    public function restore(array $sync = []): bool
    {
        $this->forceFill(array_merge([
            'is_deleted' => 0,
        ], $this->restoreResets(), $sync));

        return $this->save();
    }

    /**
     * Kolom yang di-reset ketika restore dipanggil.
     * Override di model bila perlu (mis. reset kolom kurasi).
     */
    protected function restoreResets(): array
    {
        return [];
    }
}
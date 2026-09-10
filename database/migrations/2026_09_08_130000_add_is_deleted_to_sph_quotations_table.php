<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Soft delete untuk dokumen SPH (tabel sph_quotations).
 *
 * is_deleted = 1 menandakan riwayat SPH dihapus dari History namun datanya
 * tetap tersimpan utuh untuk rekap. Riwayat aktif hanya menampilkan is_deleted = 0.
 *
 * Catatan: skema dibuat manual via bootstrap script (bukan artisan migrate).
 * Migration ini sebagai sumber kebenaran skema.
 */
return new class extends Migration
{
    public function up(): void
    {
        $cols = array_column(DB::select('SHOW COLUMNS FROM sph_quotations'), 'Field');

        if (!in_array('is_deleted', $cols)) {
            DB::statement('ALTER TABLE sph_quotations ADD COLUMN is_deleted TINYINT(1) NOT NULL DEFAULT 0 AFTER user_id');
        }
    }

    public function down(): void
    {
        $cols = array_column(DB::select('SHOW COLUMNS FROM sph_quotations'), 'Field');

        if (in_array('is_deleted', $cols)) {
            DB::statement('ALTER TABLE sph_quotations DROP COLUMN is_deleted');
        }
    }
};
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Drop kolom client_interactions yang tidak terpakai:
 *  - location     : hanya dipakai form ENTERTAIN, 0 baris terisi
 *  - participants : hanya dipakai form ENTERTAIN, 0 baris terisi
 *  - user_id      : pencatat selalu = pemilik klien (0 mismatch),
 *                   otorisasi memakai clients.user_id, bukan kolom ini
 *
 * ENTERTAIN mengikuti pola USAGE: deskripsi disimpan sebagai prefix di
 * product_name ("ENTERTAIN : ..."), notes menyimpan deskripsi kegiatan.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('client_interactions')) {
            return;
        }

        // FK user_id memakai nama warisan (interactions_user_id_foreign) karena
        // DB awal dibangun via bootstrap manual — cari nama constraint aktual.
        if (Schema::hasColumn('client_interactions', 'user_id')) {
            $fk = DB::selectOne(
                "SELECT CONSTRAINT_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
                 WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'client_interactions'
                 AND COLUMN_NAME = 'user_id' AND REFERENCED_TABLE_NAME IS NOT NULL"
            );
            if ($fk) {
                DB::statement('ALTER TABLE `client_interactions` DROP FOREIGN KEY `' . $fk->CONSTRAINT_NAME . '`');
            }
            DB::statement('ALTER TABLE `client_interactions` DROP COLUMN `user_id`');
        }

        foreach (['location', 'participants'] as $col) {
            if (Schema::hasColumn('client_interactions', $col)) {
                DB::statement('ALTER TABLE `client_interactions` DROP COLUMN `' . $col . '`');
            }
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('client_interactions')) {
            return;
        }

        Schema::table('client_interactions', function (Blueprint $table) {
            if (! Schema::hasColumn('client_interactions', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('id')
                    ->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('client_interactions', 'location')) {
                $table->string('location')->nullable()->after('product_name');
            }
            if (! Schema::hasColumn('client_interactions', 'participants')) {
                $table->string('participants')->nullable()->after('location');
            }
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ekspedisi pengiriman per dokumen (tertinggal dari migrasi awal).
     */
    public function up(): void
    {
        if (!Schema::hasColumn('ws_mutation', 'expedition')) {
            Schema::table('ws_mutation', function (Blueprint $table) {
                $table->string('expedition', 100)->nullable()->after('ship_address');
            });
            echo "OK ws_mutation: kolom expedition ditambah\n";
        } else {
            echo "SKIP ws_mutation.expedition: sudah ada\n";
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('ws_mutation', 'expedition')) {
            Schema::table('ws_mutation', function (Blueprint $table) {
                $table->dropColumn('expedition');
            });
        }
        echo "ROLLBACK ws_mutation.expedition\n";
    }
};

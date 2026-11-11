<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Idempoten + tanpa after(): kolom acuan lama ('satuan') sudah di-rename
        // menjadi 'unit' di skema saat ini, dan posisi kolom tidak fungsional.
        if (! Schema::hasColumn('barangs', 'stok')) {
            Schema::table('barangs', function (Blueprint $table) {
                $table->integer('stok')->default(0);
            });
        }
        if (! Schema::hasColumn('barangs', 'stok_po')) {
            Schema::table('barangs', function (Blueprint $table) {
                $table->integer('stok_po')->default(0);
            });
        }
        if (! Schema::hasColumn('barangs', 'is_active')) {
            Schema::table('barangs', function (Blueprint $table) {
                $table->boolean('is_active')->default(true);
            });
        }
    }

    public function down(): void
    {
        $names = ['stok', 'stok_po', 'is_active'];
        $existing = array_values(array_filter($names, fn ($c) => Schema::hasColumn('barangs', $c)));
        if (empty($existing)) {
            return;
        }
        Schema::table('barangs', function (Blueprint $table) use ($existing) {
            $table->dropColumn($existing);
        });
    }
};

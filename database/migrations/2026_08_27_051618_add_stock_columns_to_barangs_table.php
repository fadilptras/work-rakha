<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('barangs', function (Blueprint $table) {
            $table->integer('stok')->default(0)->after('satuan');
            $table->integer('stok_po')->default(0)->after('stok');
            $table->boolean('is_active')->default(true)->after('stok_po');
        });
    }

    public function down(): void
    {
        Schema::table('barangs', function (Blueprint $table) {
            $table->dropColumn(['stok', 'stok_po', 'is_active']);
        });
    }
};

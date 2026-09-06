<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sph_quotations', function (Blueprint $table) {
            $table->unsignedBigInteger('sph_sequence')->after('id')->nullable();
        });

        // Backfill: set sequence untuk data existing berdasarkan urutan created_at.
        $rows = DB::table('sph_quotations')
            ->orderBy('created_at', 'asc')
            ->get(['id', 'sph_number']);

        $start = 310;
        foreach ($rows as $index => $row) {
            DB::table('sph_quotations')
                ->where('id', $row->id)
                ->update(['sph_sequence' => $start + $index]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sph_quotations', function (Blueprint $table) {
            $table->dropColumn('sph_sequence');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('suppliers', 'email')) {
            Schema::table('suppliers', function (Blueprint $table) {
                $table->string('email')->nullable()->after('nama_supplier');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('suppliers', 'email')) {
            Schema::table('suppliers', function (Blueprint $table) {
                $table->dropColumn('email');
            });
        }
    }
};
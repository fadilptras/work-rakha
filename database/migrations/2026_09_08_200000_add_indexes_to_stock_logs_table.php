<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Optimasi filter & paginasi halaman History Stock:
 * - (status, created_at)  -> mendukung WHERE status = ? ORDER BY created_at DESC
 * - created_at            -> mendukung paginasi default (tanpa filter status)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_logs', function (Blueprint $table) {
            $table->index(['status', 'created_at'], 'stock_logs_status_created_at_index');
            $table->index('created_at', 'stock_logs_created_at_index');
        });
    }

    public function down(): void
    {
        Schema::table('stock_logs', function (Blueprint $table) {
            $table->dropIndex('stock_logs_status_created_at_index');
            $table->dropIndex('stock_logs_created_at_index');
        });
    }
};
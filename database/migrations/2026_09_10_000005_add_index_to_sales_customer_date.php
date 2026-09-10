<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Index komposit untuk fetch CRM (filter ps klien -> customer -> bulan)
 * dan dropdown customer di halaman detail klien.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('sales')) {
            return;
        }

        $exists = DB::selectOne(
            "SELECT COUNT(*) AS c FROM INFORMATION_SCHEMA.STATISTICS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'sales'
             AND INDEX_NAME = 'sales_customer_name_date_index'"
        );

        if (! $exists || (int) $exists->c === 0) {
            Schema::table('sales', function (Blueprint $table) {
                $table->index(['customer_name', 'date']);
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('sales')) {
            return;
        }

        Schema::table('sales', function (Blueprint $table) {
            $table->dropIndex(['customer_name', 'date']);
        });
    }
};

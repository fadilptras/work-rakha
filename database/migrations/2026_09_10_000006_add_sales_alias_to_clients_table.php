<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Alias nama customer di Sales Command Center untuk tiap klien.
 * Nama klien (customer_name) tidak selalu sama persis dengan penamaan
 * di data sales (mis. "RS Permata Jonggol" vs "RS AMALIAH HUSADA JONGGOL"),
 * sehingga fetch exact-match sering nol. Alias diisi sekali per klien,
 * lalu fetch memakai alias tersebut.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('clients') && ! Schema::hasColumn('clients', 'sales_customer_name')) {
            Schema::table('clients', function (Blueprint $table) {
                $table->string('sales_customer_name', 255)->nullable()->after('customer_name');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('clients') && Schema::hasColumn('clients', 'sales_customer_name')) {
            Schema::table('clients', function (Blueprint $table) {
                $table->dropColumn('sales_customer_name');
            });
        }
    }
};

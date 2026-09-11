<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('clients')
            && Schema::hasColumn('clients', 'contact_name')
            && ! Schema::hasColumn('clients', 'client_name')) {
            Schema::table('clients', function (Blueprint $table) {
                $table->renameColumn('contact_name', 'client_name');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('clients')
            && Schema::hasColumn('clients', 'client_name')
            && ! Schema::hasColumn('clients', 'contact_name')) {
            Schema::table('clients', function (Blueprint $table) {
                $table->renameColumn('client_name', 'contact_name');
            });
        }
    }
};

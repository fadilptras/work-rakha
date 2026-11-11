<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * No. SJ opsional: izinkan doc_no kosong (NULL) pada dokumen mutasi.
     * Unique (direction, doc_no) tetap aman karena MySQL/SQLite mengizinkan banyak NULL.
     */
    public function up(): void
    {
        Schema::table('ws_mutation', function (Blueprint $table) {
            $table->string('doc_no', 100)->nullable()->change();
        });
        echo "OK ws_mutation.doc_no sekarang nullable\n";
    }

    public function down(): void
    {
        Schema::table('ws_mutation', function (Blueprint $table) {
            $table->string('doc_no', 100)->nullable(false)->change();
        });
        echo "ROLLBACK ws_mutation.doc_no NOT NULL\n";
    }
};

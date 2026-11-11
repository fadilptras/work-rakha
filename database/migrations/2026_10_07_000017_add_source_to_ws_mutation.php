<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Penanda asal input dokumen: manual (form pencatatan) atau faktur.
     * + nomor referensi asal bila ada.
     */
    public function up(): void
    {
        Schema::table('ws_mutation', function (Blueprint $table) {
            if (!Schema::hasColumn('ws_mutation', 'source')) {
                $table->string('source', 20)->default('manual')->after('purpose');
            }
            if (!Schema::hasColumn('ws_mutation', 'source_no')) {
                $table->string('source_no', 100)->nullable()->after('source');
            }
        });
        echo "OK ws_mutation: kolom source/source_no dipastikan\n";
    }

    public function down(): void
    {
        Schema::table('ws_mutation', function (Blueprint $table) {
            $drop = [];
            foreach (['source', 'source_no'] as $col) {
                if (Schema::hasColumn('ws_mutation', $col)) {
                    $drop[] = $col;
                }
            }
            if (!empty($drop)) {
                $table->dropColumn($drop);
            }
        });
        echo "ROLLBACK ws_mutation.source/source_no\n";
    }
};

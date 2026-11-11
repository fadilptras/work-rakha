<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * No. SJ pada mutasi Ops bersifat opsional dan tidak harus unik.
     * Satu No. SJ boleh dipakai beberapa dokumen, dan boleh kosong.
     *
     * Idempoten: aman dijalankan walau sebagian perubahan sudah diterapkan.
     */
    public function up(): void
    {
        if (! Schema::hasTable('ws_mutation')) {
            return;
        }

        if (Schema::hasIndex('ws_mutation', 'ws_mutation_direction_doc_unique')) {
            Schema::table('ws_mutation', function (Blueprint $table) {
                $table->dropUnique('ws_mutation_direction_doc_unique');
            });
            echo "OK ws_mutation: unique (direction, doc_no) dihapus\n";
        } else {
            echo "SKIP ws_mutation: unique (direction, doc_no) sudah tidak ada\n";
        }

        Schema::table('ws_mutation', function (Blueprint $table) {
            $table->string('doc_no', 100)->nullable()->change();
        });
        echo "OK ws_mutation.doc_no nullable\n";
    }

    public function down(): void
    {
        // Sengaja tidak mengembalikan unique/NOT NULL:
        // data duplikat atau No. SJ kosong mungkin sudah tersimpan.
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Dokumen mutasi gudang (1 surat jalan = 1 baris).
     * Baris barang tetap di stock_moves (ref_type='ws_mutation').
     *
     * Aturan:
     * - 1 dokumen = 1 arah (in/out), sesuai tab form.
     * - doc_no diketik manual, unik per arah.
     * - customer wajib kecuali Internal & Retur; supplier wajib khusus Retur.
     * - Internal: keduanya null (tampil 'Pemakaian Internal').
     * - status posted/void (void = seluruh baris direversal).
     *
     * Idempoten: lewati bila tabel sudah ada.
     */
    public function up(): void
    {
        if (Schema::hasTable('ws_mutation')) {
            echo "SKIP ws_mutation: tabel sudah ada\n";
            return;
        }

        Schema::create('ws_mutation', function (Blueprint $table) {
            $table->id();
            $table->string('doc_no', 100);
            $table->enum('direction', ['in', 'out']);
            $table->date('mutation_date');
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->text('ship_address')->nullable();
            $table->string('po_no', 100)->nullable();
            $table->string('purpose', 50)->nullable();
            $table->enum('status', ['posted', 'void'])->default('posted');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['direction', 'doc_no'], 'ws_mutation_direction_doc_unique');
            $table->index(['direction', 'mutation_date'], 'ws_mutation_direction_date_index');
            $table->index(['customer_id'], 'ws_mutation_customer_id_index');
            $table->index(['supplier_id'], 'ws_mutation_supplier_id_index');
        });
        echo "OK ws_mutation: dibuat\n";
    }

    public function down(): void
    {
        Schema::dropIfExists('ws_mutation');
        echo "DROP tabel ws_mutation\n";
    }
};

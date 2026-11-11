<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fondasi Inventory ala Odoo (Fase 1-3):
     * - warehouses: 1 gudang default (siap multi-gudang tanpa refactor).
     * - stock_moves: dokumen mutasi (receipt/delivery/transfer/adjustment/scrap)
     *   status waiting -> ready -> done/cancelled. Audit trail stok.
     * - product_batches: batch per penerimaan + expiry_date (FEFO, tanpa lot/serial).
     * - products.min_stock + products.reorder_qty (nullable, aman untuk data lama).
     * - reordering_rules: min/max per produk -> pemicu draft pengajuan barang.
     *
     * Idempoten: semua pembuatan dicek hasTable/hasColumn lebih dulu.
     */
    public function up(): void
    {
        if (! Schema::hasTable('warehouses')) {
            Schema::create('warehouses', function (Blueprint $table) {
                $table->id();
                $table->string('code', 20)->unique();
                $table->string('name', 100);
                $table->string('address', 255)->nullable();
                $table->boolean('is_default')->default(false);
                $table->timestamps();
            });
            echo "OK warehouses: dibuat\n";
        } else {
            echo "SKIP warehouses: tabel sudah ada\n";
        }

        if (DB::table('warehouses')->count() === 0) {
            DB::table('warehouses')->insert([
                'code' => 'WH',
                'name' => 'Gudang Utama',
                'is_default' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            echo "OK warehouses: seed WH\n";
        } else {
            echo "SKIP warehouses: seed sudah ada\n";
        }

        if (! Schema::hasTable('stock_moves')) {
            Schema::create('stock_moves', function (Blueprint $table) {
                $table->id();
                $table->foreignId('warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
                $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
                $table->decimal('qty', 15, 2)->default(0);
                $table->string('uom', 20)->nullable();
                $table->string('from_location', 50)->default('Stock');
                $table->string('to_location', 50)->default('Stock');
                $table->enum('type', ['receipt', 'delivery', 'transfer', 'adjustment', 'scrap'])->default('adjustment');
                $table->enum('status', ['waiting', 'ready', 'done', 'cancelled'])->default('waiting');
                $table->string('ref_type', 50)->nullable();
                $table->unsignedBigInteger('ref_id')->nullable();
                $table->date('expiry_date')->nullable();
                $table->text('reason')->nullable();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('validated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('validated_at')->nullable();
                $table->timestamps();
                $table->index(['product_id', 'status']);
                $table->index(['status', 'type']);
            });
            echo "OK stock_moves: dibuat\n";
        } else {
            echo "SKIP stock_moves: tabel sudah ada\n";
        }

        if (! Schema::hasTable('product_batches')) {
            Schema::create('product_batches', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
                $table->date('receipt_date')->nullable();
                $table->date('expiry_date')->nullable();
                $table->decimal('qty_initial', 15, 2)->default(0);
                $table->decimal('qty_current', 15, 2)->default(0);
                $table->timestamps();
                $table->index(['product_id', 'expiry_date']);
            });
            echo "OK product_batches: dibuat\n";
        } else {
            echo "SKIP product_batches: tabel sudah ada\n";
        }

        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'min_stock')) {
                $table->decimal('min_stock', 15, 2)->nullable()->after('stock_po');
            }
            if (! Schema::hasColumn('products', 'reorder_qty')) {
                $table->decimal('reorder_qty', 15, 2)->nullable()->after('min_stock');
            }
        });
        echo "OK products: kolom min_stock/reorder_qty dipastikan\n";

        if (! Schema::hasTable('reordering_rules')) {
            Schema::create('reordering_rules', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->unique()->constrained('products')->cascadeOnDelete();
                $table->decimal('min_qty', 15, 2)->default(0);
                $table->decimal('max_qty', 15, 2)->nullable();
                $table->boolean('active')->default(true);
                $table->timestamps();
            });
            echo "OK reordering_rules: dibuat\n";
        } else {
            echo "SKIP reordering_rules: tabel sudah ada\n";
        }
    }

    /**
     * Reverse the migrations.
     * Catatan: seed WH ikut terhapus bersama tabelnya.
     */
    public function down(): void
    {
        foreach (['reordering_rules', 'product_batches', 'stock_moves', 'warehouses'] as $table) {
            if (Schema::hasTable($table)) {
                Schema::dropIfExists($table);
                echo "DROP tabel {$table}\n";
            }
        }

        Schema::table('products', function (Blueprint $table) {
            $drop = [];
            if (Schema::hasColumn('products', 'min_stock')) {
                $drop[] = 'min_stock';
            }
            if (Schema::hasColumn('products', 'reorder_qty')) {
                $drop[] = 'reorder_qty';
            }
            if (! empty($drop)) {
                $table->dropColumn($drop);
            }
        });
        echo "DROP kolom min_stock/reorder_qty (bila ada)\n";
    }
};

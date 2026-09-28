<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

return new class extends Migration
{
    /**
     * Tabel khusus closing (penjualan & rencana pembelian).
     * Kolom amount tunggal menggantikan pasangan legacy closing_rate/closing_count.
     * type=sales  : estimasi/rencana closing penjualan (customer_name)
     * type=purchase: rencana pembelian, diinput via manage (supplier_name)
     */
    public function up(): void
    {
        Schema::create('sales_closings', function (Blueprint $table) {
            $table->id();
            $table->string('type', 20)->default('sales');
            $table->integer('year');
            $table->string('month', 20);
            $table->date('date')->nullable();
            $table->string('ps', 255)->nullable();
            $table->string('customer_name', 255)->nullable();
            $table->string('supplier_name', 255)->nullable();
            $table->string('product_name', 255)->nullable();
            $table->integer('qty')->nullable();
            $table->string('unit', 50)->nullable();
            $table->decimal('amount', 15, 2)->default(0);
            $table->string('note', 255)->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index(['type', 'year', 'month'], 'sales_closings_type_ym_index');
        });

        // Pindah data closing outlet lama (amount = closing_count ?? closing_rate)
        if (Schema::hasTable('sales_outlet_closings')) {
            $rows = DB::table('sales_outlet_closings')->get();
            $grouped = [];
            foreach ($rows as $r) {
                $key = implode('|', ['sales', $r->year, strtolower(trim($r->month)), strtolower(trim($r->ps ?? '')), strtolower(trim($r->customer_name))]);
                $amount = (float) ($r->closing_count ?? $r->closing_rate ?? 0);
                if (!isset($grouped[$key])) {
                    $grouped[$key] = [
                        'type' => 'sales',
                        'year' => $r->year,
                        'month' => $r->month,
                        'date' => self::monthEnd($r->year, $r->month),
                        'ps' => $r->ps,
                        'customer_name' => $r->customer_name,
                        'supplier_name' => null,
                        'product_name' => null,
                        'qty' => null,
                        'unit' => null,
                        'amount' => 0,
                        'note' => 'Migrasi sales_outlet_closings',
                        'created_by' => null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
                $grouped[$key]['amount'] += $amount;
            }
            foreach (array_values($grouped) as $row) {
                DB::table('sales_closings')->insert($row);
            }
        }

        // Pindah baris estimasi di tabel sales (is_estimate=1)
        if (Schema::hasTable('sales') && Schema::hasColumn('sales', 'is_estimate')) {
            $estimates = DB::table('sales')->where('is_estimate', true)->get();
            foreach ($estimates as $e) {
                DB::table('sales_closings')->insert([
                    'type' => 'sales',
                    'year' => $e->date ? Carbon::parse($e->date)->year : (int) date('Y'),
                    'month' => $e->month,
                    'date' => $e->date,
                    'ps' => $e->ps,
                    'customer_name' => $e->customer_name,
                    'supplier_name' => null,
                    'product_name' => $e->product_name,
                    'qty' => $e->qty,
                    'unit' => $e->unit,
                    'amount' => $e->net_price ?? 0,
                    'note' => $e->estimate_note ?: 'Migrasi baris estimasi sales',
                    'created_by' => null,
                    'created_at' => $e->created_at,
                    'updated_at' => $e->updated_at,
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_closings');
    }

    private static function monthEnd($year, $month): ?string
    {
        try {
            $m = Carbon::createFromFormat('!F', ucfirst(strtolower(trim((string) $month))));
            if (!$m) return null;
            return Carbon::create((int) $year, (int) $m->format('m'), 1)->endOfMonth()->toDateString();
        } catch (\Exception $e) {
            return null;
        }
    }
};

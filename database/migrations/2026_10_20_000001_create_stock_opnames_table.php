<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Sumber data resmi Stock Opname.
     * Satu baris current per produk + periode (YYYY-MM). Jejak mutasi stok
     * tetap disimpan di stock_moves saat opname diselesaikan.
     */
    public function up(): void
    {
        if (! Schema::hasTable('stock_opnames')) {
            Schema::create('stock_opnames', function (Blueprint $table) {
                $table->id();
                $table->foreignId('warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();
                $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
                $table->string('period', 7)->index(); // YYYY-MM
                $table->date('opname_date');
                $table->dateTime('opname_at')->nullable();
                $table->string('status', 10)->default('draft')->index(); // draft | done
                $table->decimal('recorded_qty', 15, 2)->default(0);
                $table->decimal('counted_qty', 15, 2)->default(0);
                $table->decimal('diff_qty', 15, 2)->default(0);
                $table->text('note')->nullable();
                $table->string('ba_creator', 100)->nullable();
                $table->string('ba_checker', 100)->nullable();
                $table->foreignId('stock_move_id')->nullable()->constrained('stock_moves')->nullOnDelete();
                $table->unsignedInteger('revision')->default(1);
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('validated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('validated_at')->nullable();
                $table->timestamps();

                $table->unique(['product_id', 'period']);
                $table->index(['period', 'status']);
            });
        }

        $this->backfillFromLegacyMoves();
    }

    /**
     * Bangun baris opname dari data lama yang masih disimpan di reason
     * stock_moves ("Opname: tercatat X, terhitung Y..."). Idempoten.
     */
    private function backfillFromLegacyMoves(): void
    {
        if (! Schema::hasTable('stock_moves') || ! Schema::hasColumn('stock_moves', 'reason')) {
            return;
        }

        $moves = DB::table('stock_moves')
            ->where('type', 'adjustment')
            ->where('reason', 'like', 'Opname:%')
            ->orderBy('id')
            ->get();

        if ($moves->isEmpty()) {
            return;
        }

        $latest = [];
        foreach ($moves as $move) {
            $period = substr((string) $move->created_at, 0, 7);
            $key = $move->product_id . '|' . $period;
            $latest[$key] = $move; // id terakhir menang (query orderBy id)
        }

        foreach ($latest as $move) {
            $period = substr((string) $move->created_at, 0, 7);
            $parsed = self::parseLegacyReason($move->reason);
            $recorded = $parsed['tercatat'];
            $counted = $parsed['terhitung'];
            $diff = ($recorded !== null && $counted !== null)
                ? round($counted - $recorded, 2)
                : 0;

            $exists = DB::table('stock_opnames')
                ->where('product_id', $move->product_id)
                ->where('period', $period)
                ->exists();
            if ($exists) {
                continue;
            }

            DB::table('stock_opnames')->insert([
                'warehouse_id' => $move->warehouse_id ?? null,
                'product_id' => $move->product_id,
                'period' => $period,
                'opname_date' => substr((string) $move->created_at, 0, 10),
                'opname_at' => $move->created_at,
                'status' => $move->status === 'done' ? 'done' : 'draft',
                'recorded_qty' => $recorded ?? 0,
                'counted_qty' => $counted ?? 0,
                'diff_qty' => $diff,
                'note' => $parsed['catatan'],
                'ba_creator' => $parsed['pembuat'],
                'ba_checker' => $parsed['pemeriksa'],
                'stock_move_id' => $move->status === 'done' ? $move->id : null,
                'revision' => 1,
                'created_by' => $move->user_id ?? null,
                'validated_by' => $move->validated_by ?? null,
                'validated_at' => $move->validated_at ?? null,
                'created_at' => $move->created_at,
                'updated_at' => $move->updated_at ?? $move->created_at,
            ]);
        }
    }

    /** Urai reason lama; dipakai hanya saat migrasi data. */
    private static function parseLegacyReason(?string $reason): array
    {
        $out = ['tercatat' => null, 'terhitung' => null, 'catatan' => null, 'pembuat' => null, 'pemeriksa' => null];
        if (! is_string($reason) || ! str_starts_with($reason, 'Opname:')) {
            return $out;
        }
        if (preg_match('/tercatat\s+(-?[\d.,]+)/i', $reason, $m)) {
            $out['tercatat'] = (float) preg_replace('/[^0-9.\-]/', '', $m[1]);
        }
        if (preg_match('/terhitung\s+(-?[\d.,]+)/i', $reason, $m)) {
            $out['terhitung'] = (float) preg_replace('/[^0-9.\-]/', '', $m[1]);
        }
        if (preg_match('/;\s*BA oleh:\s*(.+?)(?:\s*;\s*Diperiksa:.*)?\s*$/s', $reason, $m)) {
            $out['pembuat'] = trim($m[1]) ?: null;
        }
        if (preg_match('/;\s*Diperiksa:\s*(.+?)\s*$/s', $reason, $m)) {
            $out['pemeriksa'] = trim($m[1]) ?: null;
        }
        if (preg_match('/; Catatan:\s*(.+?)(?:\s*;\s*BA oleh:.*)?\s*$/s', $reason, $m)) {
            $out['catatan'] = trim($m[1]) ?: null;
        }

        return $out;
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_opnames');
    }
};

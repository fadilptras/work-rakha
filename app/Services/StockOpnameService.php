<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockMove;
use App\Models\StockOpname;
use App\Models\User;
use App\Models\Warehouse;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Alur Stock Opname dua tahap:
 * - simpan hasil = draf (stok belum berubah)
 * - selesaikan = stok pada tanggal opname diset sesuai hasil hitung + BA terbit
 *
 * Rumus mutasi bertanda (sama dengan Kartu Stok):
 * receipt +, adjustment masuk +, transfer 0, selain itu -.
 */
class StockOpnameService
{
    public const STATUS_DRAFT = StockOpname::STATUS_DRAFT;

    public const STATUS_DONE = StockOpname::STATUS_DONE;

    /** Simpan/perbarui hasil hitung sebagai draf. */
    public function saveResult(
        Product $product,
        string $opnameDate,
        float $countedQty,
        ?string $note,
        ?User $user = null
    ): StockOpname {
        $period = substr($opnameDate, 0, 7);
        $counted = round($countedQty, 2);

        return DB::transaction(function () use ($product, $opnameDate, $period, $counted, $note, $user) {
            $row = StockOpname::where('product_id', $product->id)
                ->where('period', $period)
                ->lockForUpdate()
                ->first();

            $now = now();
            if ($row) {
                // Tanggal opname dikunci sejak hasil pertama disimpan.
                $opnameAt = $row->opname_at ?: Carbon::parse($row->opname_date)->endOfDay();
                $recorded = (float) $row->recorded_qty;
                $wasDone = $row->isDone();

                $row->fill([
                    'counted_qty' => $counted,
                    'diff_qty' => round($counted - $recorded, 2),
                    'note' => $note ?: null,
                    'status' => StockOpname::STATUS_DRAFT,
                    'revision' => $wasDone ? ((int) $row->revision + 1) : (int) $row->revision,
                    'validated_by' => null,
                    'validated_at' => null,
                ]);
                // Nama BA terakhir dipertahankan agar Selesaikan berikutnya
                // tidak perlu menanyakan pembuat/pemeriksa lagi.
                if (! $row->created_by) {
                    $row->created_by = $user?->id;
                }
                $row->save();
            } else {
                $opnameAt = Carbon::parse($opnameDate)->endOfDay();
                $recorded = round((float) $product->stock, 2);
                $row = StockOpname::create([
                    'warehouse_id' => Warehouse::default()?->id,
                    'product_id' => $product->id,
                    'period' => $period,
                    'opname_date' => $opnameDate,
                    'opname_at' => $opnameAt,
                    'status' => StockOpname::STATUS_DRAFT,
                    'recorded_qty' => $recorded,
                    'counted_qty' => $counted,
                    'diff_qty' => round($counted - $recorded, 2),
                    'note' => $note ?: null,
                    'revision' => 1,
                    'created_by' => $user?->id,
                ]);
            }

            $this->deleteLegacyDrafts($product->id, $period);

            return $row->fresh(['product', 'creator']);
        });
    }

    /**
     * Selesaikan seluruh draf pada satu periode.
     * Stok pada tanggal opname diset ke counted_qty; mutasi setelah tanggal itu
     * tetap dipertahankan. Mengembalikan jumlah baris yang diposting.
     */
    public function finishPeriod(
        string $period,
        string $baCreator,
        string $baChecker,
        User $user,
        StockMoveService $moves,
        DocumentNumberService $docs
    ): int {
        return DB::transaction(function () use ($period, $baCreator, $baChecker, $user, $moves, $docs) {
            $drafts = StockOpname::with('product')
                ->where('period', $period)
                ->where('status', StockOpname::STATUS_DRAFT)
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            $done = 0;
            foreach ($drafts as $row) {
                $product = Product::lockForUpdate()->find($row->product_id);
                if (! $product) {
                    continue;
                }

                $opnameAt = $row->opname_at ?: Carbon::parse($row->opname_date)->endOfDay();
                $counted = (float) $row->counted_qty;

                // Stok sistem pada tanggal opname: stok kini dikurangi mutasi setelahnya.
                $stockAtOpname = $this->stockAtDate($product->id, $opnameAt);
                $delta = round($counted - $stockAtOpname, 2);

                $move = null;
                if (abs($delta) >= 0.005) {
                    $docNo = $docs->formatSop($docs->nextSequence('SOP'), $opnameAt);
                    $reason = $this->buildLegacyReason($row, $baCreator, $baChecker);

                    $move = $moves->createMove([
                        'product_id' => $product->id,
                        'qty' => abs($delta),
                        'type' => StockMove::TYPE_ADJUSTMENT,
                        'from_location' => $delta > 0 ? StockMove::LOC_ADJUSTMENT : StockMove::LOC_STOCK,
                        'to_location' => $delta > 0 ? StockMove::LOC_STOCK : StockMove::LOC_ADJUSTMENT,
                        'reason' => $reason,
                        'doc_no' => $docNo,
                        'occurred_at' => $opnameAt->toDateTimeString(),
                    ], $user);

                    $moves->validateMove($move, $user, true);

                    // Samakan timestamp dengan tanggal opname agar perhitungan
                    // stok pada tanggal itu konsisten saat revisi berikutnya.
                    $move->timestamps = false;
                    $move->created_at = $opnameAt;
                    $move->updated_at = $opnameAt;
                    $move->save();
                }

                $row->update([
                    'status' => StockOpname::STATUS_DONE,
                    'ba_creator' => $baCreator,
                    'ba_checker' => $baChecker,
                    'stock_move_id' => $move?->id,
                    'validated_by' => $user->id,
                    'validated_at' => now(),
                ]);

                $this->deleteLegacyDrafts($product->id, $period);
                $done++;
            }

            return $done;
        });
    }

    /** Stok sistem pada suatu titik waktu (sebelum mutasi setelahnya). */
    public function stockAtDate(int $productId, CarbonInterface $at): float
    {
        $product = Product::find($productId);
        if (! $product) {
            return 0;
        }

        $netAfter = DB::table('stock_moves')
            ->where('product_id', $productId)
            ->where('status', StockMove::STATUS_DONE)
            ->where('created_at', '>', $at)
            ->selectRaw(self::signedNetSql().' as net')
            ->value('net');

        return round((float) $product->stock - (float) ($netAfter ?? 0), 2);
    }

    /** Ekspresi SQL net mutasi bertanda. */
    public static function signedNetSql(): string
    {
        return "SUM(CASE
            WHEN type = 'receipt' THEN qty
            WHEN type = 'transfer' THEN 0
            WHEN type = 'adjustment' AND to_location = 'Stock' AND from_location <> 'Stock' THEN qty
            ELSE -qty
        END)";
    }

    /** BA hanya boleh diunduh bila periode sudah selesai dan tidak ada draf. */
    public function baAvailable(string $period): bool
    {
        $done = StockOpname::where('period', $period)
            ->where('status', StockOpname::STATUS_DONE)->count();
        if ($done === 0) {
            return false;
        }

        return ! StockOpname::where('period', $period)
            ->where('status', StockOpname::STATUS_DRAFT)->exists();
    }

    /** Ringkasan status per periode. */
    public function periodStats(string $period): array
    {
        $rows = StockOpname::where('period', $period)->get(['product_id', 'status', 'diff_qty']);

        return [
            'tersimpan' => $rows->pluck('product_id')->unique()->count(),
            'selesai' => $rows->where('status', StockOpname::STATUS_DONE)->pluck('product_id')->unique()->count(),
            'menunggu' => $rows->where('status', StockOpname::STATUS_DRAFT)->pluck('product_id')->unique()->count(),
            'sesuai' => $rows->where('status', StockOpname::STATUS_DONE)
                ->filter(fn ($r) => abs((float) $r->diff_qty) < 0.005)
                ->pluck('product_id')->unique()->count(),
            'selisih' => $rows->where('status', StockOpname::STATUS_DONE)
                ->filter(fn ($r) => abs((float) $r->diff_qty) >= 0.005)
                ->pluck('product_id')->unique()->count(),
            'entries' => $rows->count(),
        ];
    }

    /** Revisi stok lama hanya boleh lewat alur opname, bukan mutasi biasa. */
    public function deleteLegacyDrafts(int $productId, string $period): void
    {
        try {
            StockMove::where('type', StockMove::TYPE_ADJUSTMENT)
                ->where('status', StockMove::STATUS_WAITING)
                ->where('reason', 'like', 'Opname:%')
                ->where('product_id', $productId)
                ->whereYear('created_at', substr($period, 0, 4))
                ->whereMonth('created_at', (int) substr($period, 5, 2))
                ->delete();
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /** Reason lama dipertahankan untuk kompatibilitas Kartu Stok & audit. */
    private function buildLegacyReason(StockOpname $row, string $baCreator, string $baChecker): string
    {
        $reason = "Opname: tercatat {$row->recorded_qty}, terhitung {$row->counted_qty}";
        if (! empty($row->note)) {
            $reason .= "; Catatan: {$row->note}";
        }

        return $reason."; BA oleh: {$baCreator}; Diperiksa: {$baChecker}";
    }

    /** Lempar error yang ramah untuk controller. */
    public static function guard(bool $condition, string $message): void
    {
        if (! $condition) {
            throw new InvalidArgumentException($message);
        }
    }
}

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
use Illuminate\Support\Facades\Schema;
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

    public function __construct(
        protected ?StockMoveService $moves = null,
        protected ?DocumentNumberService $docs = null
    ) {
        $this->moves = $moves ?? app(StockMoveService::class);
        $this->docs = $docs ?? app(DocumentNumberService::class);
    }

    /** Simpan/perbarui hasil hitung sebagai draf atau langsung selesai bila sudah final. */
    public function saveResult(
        Product $product,
        string $opnameDate,
        float $countedQty,
        ?string $note,
        ?User $user = null,
        ?float $recordedQty = null,
        ?string $period = null,
        ?StockMoveService $moves = null,
        ?DocumentNumberService $docs = null
    ): StockOpname {
        $period = $period ?: substr($opnameDate, 0, 7);
        $counted = round($countedQty, 2);
        $recordedOverride = $recordedQty !== null ? round($recordedQty, 2) : null;
        $svcMoves = $moves ?? $this->moves ?? app(StockMoveService::class);
        $svcDocs = $docs ?? $this->docs ?? app(DocumentNumberService::class);

        return DB::transaction(function () use ($product, $opnameDate, $period, $counted, $note, $user, $recordedOverride, $svcMoves, $svcDocs) {
            $row = StockOpname::where('product_id', $product->id)
                ->where('period', $period)
                ->lockForUpdate()
                ->first();

            $now = now();
            $recorded = $recordedOverride ?? ($row ? (float) $row->recorded_qty : round((float) $product->stock, 2));
            $diff = round($counted - $recorded, 2);
            $cleanNote = trim((string) $note);
            $diffStr = $diff > 0 ? "+{$diff}" : "{$diff}";
            $finalNote = ($cleanNote === '' || $cleanNote === '-')
                ? (abs($diff) >= 0.005 ? "Selisih {$diffStr}" : null)
                : $cleanNote;
            if ($row) {
                $opnameAt = Carbon::parse($opnameDate)->endOfDay();
                if ($opnameAt->gt(now())) {
                    $opnameAt = now();
                }
                $recorded = $recordedOverride ?? (float) $row->recorded_qty;
                $wasDone = $row->isDone();
                $newRevision = $wasDone ? ((int) $row->revision + 1) : (int) $row->revision;

                if ($wasDone) {
                    // Edit pada opname yang sudah pernah selesai: sesuaikan mutasi adjustment opname yang sudah ada in-place
                    // agar tidak membuat baris mutasi baru/bertumpuk di Kartu Stok.
                    $existingMoveId = $row->stock_move_id;
                    $baseStockBeforeOpname = DB::table('stock_moves')
                        ->where('product_id', $product->id)
                        ->where('status', StockMove::STATUS_DONE)
                        ->when($existingMoveId, fn ($q) => $q->where('id', '!=', $existingMoveId))
                        ->where(fn ($q) => $q->whereNull('ref_type')->orWhere('ref_type', '!=', 'stock_move_reversal'))
                        ->where(fn ($q) => $q->whereNull('reason')->orWhere('reason', 'not like', '%[dibatalkan]%'))
                        ->where('created_at', '<=', $opnameAt)
                        ->selectRaw(self::signedNetSql() . ' as net')
                        ->value('net');

                    $baseStockBeforeOpname = (float) ($baseStockBeforeOpname ?? 0);
                    $delta = round($counted - $baseStockBeforeOpname, 2);

                    $targetMoveId = $existingMoveId;
                    if (abs($delta) >= 0.005) {
                        $diffDeltaStr = $delta > 0 ? "+{$delta}" : "{$delta}";
                        $reason = "Opname (Revisi #{$newRevision}): tercatat {$recorded}, terhitung {$counted} (selisih {$diffDeltaStr})";
                        if (! empty($finalNote)) {
                            $reason .= "; Catatan: {$finalNote}";
                        }
                        if ($row->ba_creator || $row->ba_checker) {
                            $reason .= "; BA oleh: " . ($row->ba_creator ?: '-') . "; Diperiksa: " . ($row->ba_checker ?: '-');
                        }

                        $fromLoc = $delta > 0 ? StockMove::LOC_ADJUSTMENT : StockMove::LOC_STOCK;
                        $toLoc = $delta > 0 ? StockMove::LOC_STOCK : StockMove::LOC_ADJUSTMENT;

                        if ($existingMoveId && ($exMove = StockMove::find($existingMoveId))) {
                            // Update mutasi opname yang ada in-place (tanpa baris baru)
                            $exMove->update([
                                'qty' => abs($delta),
                                'type' => StockMove::TYPE_ADJUSTMENT,
                                'from_location' => $fromLoc,
                                'to_location' => $toLoc,
                                'reason' => $reason,
                            ]);
                            $targetMoveId = $exMove->id;
                        } else {
                            $docNo = $svcDocs->formatSop($svcDocs->nextSequence('SOP'), $opnameAt);
                            $newMove = $svcMoves->createMove([
                                'product_id' => $product->id,
                                'qty' => abs($delta),
                                'type' => StockMove::TYPE_ADJUSTMENT,
                                'from_location' => $fromLoc,
                                'to_location' => $toLoc,
                                'reason' => $reason,
                                'doc_no' => $docNo,
                                'occurred_at' => $opnameAt->toDateTimeString(),
                            ], $user);

                            $svcMoves->validateMove($newMove, $user, true);

                            $newMove->timestamps = false;
                            $newMove->created_at = $opnameAt;
                            $newMove->updated_at = $opnameAt;
                            $newMove->save();
                            $targetMoveId = $newMove->id;
                        }
                    } else {
                        // Tidak ada selisih, hapus mutasi adjustment lama jika ada
                        if ($existingMoveId) {
                            StockMove::where('id', $existingMoveId)->delete();
                        }
                        $targetMoveId = null;
                    }

                    $row->fill([
                        'opname_date' => $opnameDate,
                        'opname_at' => $opnameAt,
                        'recorded_qty' => $recorded,
                        'counted_qty' => $counted,
                        'diff_qty' => round($counted - $recorded, 2),
                        'note' => $finalNote,
                        'status' => StockOpname::STATUS_DONE,
                        'revision' => $newRevision,
                        'stock_move_id' => $targetMoveId,
                        'validated_by' => $user?->id ?? $row->validated_by,
                        'validated_at' => now(),
                    ]);
                    if (! $row->created_by) {
                        $row->created_by = $user?->id;
                    }
                    $row->save();

                    // Sinkronkan products.stock seketika
                    $this->syncProductStock($product);
                } else {
                    $row->fill([
                        'opname_date' => $opnameDate,
                        'opname_at' => $opnameAt,
                        'recorded_qty' => $recorded,
                        'counted_qty' => $counted,
                        'diff_qty' => round($counted - $recorded, 2),
                        'note' => $finalNote,
                        'status' => StockOpname::STATUS_DRAFT,
                        'revision' => (int) $row->revision,
                        'validated_by' => null,
                        'validated_at' => null,
                    ]);
                    if (! $row->created_by) {
                        $row->created_by = $user?->id;
                    }
                    $row->save();
                }
            } else {
                $opnameAt = Carbon::parse($opnameDate)->endOfDay();
                if ($opnameAt->gt(now())) {
                    $opnameAt = now();
                }
                $recorded = $recordedOverride ?? round((float) $product->stock, 2);

                $existingPeriodBa = StockOpname::where('period', $period)
                    ->where('status', StockOpname::STATUS_DONE)
                    ->whereNotNull('ba_creator')
                    ->first();

                if ($existingPeriodBa) {
                    $stockAtOpname = $this->stockAtDate($product->id, $opnameAt);
                    $delta = round($counted - $stockAtOpname, 2);

                    $newMove = null;
                    if (abs($delta) >= 0.005) {
                        $docNo = $svcDocs->formatSop($svcDocs->nextSequence('SOP'), $opnameAt);
                        $diffDeltaStr = $delta > 0 ? "+{$delta}" : "{$delta}";
                        $reason = "Opname: tercatat {$recorded}, terhitung {$counted} (selisih {$diffDeltaStr})";
                        if (! empty($finalNote)) {
                            $reason .= "; Catatan: {$finalNote}";
                        }
                        $reason .= "; BA oleh: {$existingPeriodBa->ba_creator}; Diperiksa: {$existingPeriodBa->ba_checker}";

                        $newMove = $svcMoves->createMove([
                            'product_id' => $product->id,
                            'qty' => abs($delta),
                            'type' => StockMove::TYPE_ADJUSTMENT,
                            'from_location' => $delta > 0 ? StockMove::LOC_ADJUSTMENT : StockMove::LOC_STOCK,
                            'to_location' => $delta > 0 ? StockMove::LOC_STOCK : StockMove::LOC_ADJUSTMENT,
                            'reason' => $reason,
                            'doc_no' => $docNo,
                            'occurred_at' => $opnameAt->toDateTimeString(),
                        ], $user);

                        $svcMoves->validateMove($newMove, $user, true);

                        $newMove->timestamps = false;
                        $newMove->created_at = $opnameAt;
                        $newMove->updated_at = $opnameAt;
                        $newMove->save();
                    }

                    $row = StockOpname::create([
                        'warehouse_id' => Warehouse::default()?->id,
                        'product_id' => $product->id,
                        'period' => $period,
                        'opname_date' => $opnameDate,
                        'opname_at' => $opnameAt,
                        'status' => StockOpname::STATUS_DONE,
                        'recorded_qty' => $recorded,
                        'counted_qty' => $counted,
                        'diff_qty' => round($counted - $recorded, 2),
                        'note' => $finalNote,
                        'revision' => 1,
                        'ba_creator' => $existingPeriodBa->ba_creator,
                        'ba_checker' => $existingPeriodBa->ba_checker,
                        'stock_move_id' => $newMove?->id,
                        'created_by' => $user?->id,
                        'validated_by' => $user?->id,
                        'validated_at' => now(),
                    ]);
                } else {
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
                        'note' => $finalNote,
                        'revision' => 1,
                        'created_by' => $user?->id,
                    ]);
                }
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

    /**
     * Catat opname otomatis untuk produk baru yang baru didaftarkan dengan stok awal,
     * atau saat stok awal pertama kali ditetapkan.
     * Supaya periode opname bulan berjalan langsung sinkron (tercatat = terhitung = stok awal, selisih 0),
     * dan statusnya langsung 'done' (sesuai) sehingga pengguna tidak perlu edit/catat opname lagi.
     */
    public function recordOpeningOpname(Product $product, float $stock, ?User $user = null): ?StockOpname
    {
        $period = now()->format('Y-m');
        $stock = round($stock, 2);

        // Ambil data opname periode ini (bukan rekaman stok awal) agar nama BA selaras
        $existingPeriodOpname = StockOpname::where('period', $period)
            ->where('status', StockOpname::STATUS_DONE)
            ->whereNotNull('opname_date')
            ->where(function ($q) {
                $q->whereNull('note')->orWhere('note', 'not like', '%stok awal%');
            })
            ->orderBy('opname_date')
            ->first();

        // Tanggal penetapan stok awal adalah tanggal sekarang, bukan akhir bulan
        $opnameDate = now()->toDateString();
        $opnameAt = now();
        $baCreator = $existingPeriodOpname?->ba_creator ?: ($user?->name ?: 'Staff Gudang');
        $baChecker = $existingPeriodOpname?->ba_checker ?: 'Kepala Gudang';

        return StockOpname::updateOrCreate(
            [
                'product_id' => $product->id,
                'period' => $period,
            ],
            [
                'warehouse_id' => Warehouse::default()?->id,
                'opname_date' => $opnameDate,
                'opname_at' => $opnameAt,
                'status' => StockOpname::STATUS_DONE,
                'recorded_qty' => $stock,
                'counted_qty' => $stock,
                'diff_qty' => 0,
                'note' => 'Stok awal produk baru (otomatis selesai)',
                'ba_creator' => $baCreator,
                'ba_checker' => $baChecker,
                'revision' => 1,
                'created_by' => $user?->id,
                'validated_by' => $user?->id,
                'validated_at' => now(),
            ]
        );
    }

    /**
     * Hitung stok realtime saat ini secara akurat dan konsisten:
     * 1. Jika produk sudah pernah di-opname (status done), jadikan hasil fisik opname
     *    terakhir sebagai saldo dasar dan akumulasikan mutasi setelah tanggal opname tersebut.
     * 2. Jika belum pernah opname, hitung seluruh mutasi stock_moves.
     * 3. Fallback ke products.stock jika belum ada riwayat sama sekali.
     */
    public function calculateRealtimeStock(int $productId): float
    {
        $product = Product::find($productId);
        if (! $product) {
            return 0;
        }

        try {
            $req = new \Illuminate\Http\Request();
            $card = app(\App\Http\Controllers\Warehouse\WsStockCardController::class)->buildCardData($req, $product);
            $rows = $card['rows'] ?? [];

            if (! empty($rows)) {
                return round((float) $rows[count($rows) - 1]['sisa'], 2);
            }

            return round((float) ($card['saldoAwal'] ?? $product->stock), 2);
        } catch (\Throwable $e) {
            report($e);

            return (float) $product->stock;
        }
    }

    /**
     * Sinkronkan kolom products.stock dengan stok realtime sebenarnya.
     */
    public function syncProductStock(int|Product $product): float
    {
        $p = $product instanceof Product ? $product : Product::find($product);
        if (! $p) {
            return 0;
        }

        $realtime = $this->calculateRealtimeStock($p->id);
        if (abs((float) $p->stock - $realtime) >= 0.005) {
            DB::table('products')->where('id', $p->id)->update(['stock' => $realtime]);
            $p->stock = $realtime;
        }

        return $realtime;
    }

    /**
     * Sinkronkan seluruh produk aktif agar nilai products.stock di database
     * konsisten dengan hasil opname terakhir + mutasi setelahnya.
     */
    public function syncAllProductsStock(): int
    {
        $products = Product::active()->get();
        $synced = 0;

        foreach ($products as $p) {
            $expected = $this->calculateRealtimeStock($p->id);
            if (abs((float) $p->stock - $expected) >= 0.005) {
                DB::table('products')->where('id', $p->id)->update(['stock' => $expected]);
                $p->stock = $expected;
                $synced++;
            }
        }

        return $synced;
    }

    /** Lempar error yang ramah untuk controller. */
    public static function guard(bool $condition, string $message): void
    {
        if (! $condition) {
            throw new InvalidArgumentException($message);
        }
    }
}


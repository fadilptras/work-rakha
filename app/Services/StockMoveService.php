<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\StockMove;
use App\Models\User;
use App\Models\Warehouse;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;

/**
 * Pintu mutasi stok. products.stock hanya berubah via move done.
 */
class StockMoveService
{
    /** Buat mutasi waiting. Stok belum berubah. */
    public function createMove(array $data, ?User $user = null): StockMove
    {
        $qty = (float) ($data['qty'] ?? 0);
        if ($qty <= 0) {
            throw new InvalidArgumentException('Qty mutasi harus lebih dari 0.');
        }
        if (! in_array($data['type'], array_keys(StockMove::$typeLabels), true)) {
            throw new InvalidArgumentException('Tipe mutasi tidak dikenal.');
        }
        $product = Product::find($data['product_id']);
        if (! $product) {
            throw new InvalidArgumentException('Produk tidak ditemukan.');
        }

        return DB::transaction(function () use ($data, $qty, $user, $product) {
            $move = StockMove::create([
                'warehouse_id' => Warehouse::default()?->id,
                'product_id' => $data['product_id'],
                'batch_no' => $data['batch_no'] ?? null,
                'doc_no' => $data['doc_no'] ?? null,
                'partner' => $data['partner'] ?? null,
                'po_no' => $data['po_no'] ?? null,
                'purpose' => $data['purpose'] ?? null,
                'ship_address' => $data['ship_address'] ?? null,
                'expedition' => $data['expedition'] ?? null,
                'qty' => $qty,
                'uom' => $data['uom'] ?? $product->unit,
                'from_location' => $data['from_location'] ?? $this->defaultFrom($data['type']),
                'to_location' => $data['to_location'] ?? $this->defaultTo($data['type']),
                'type' => $data['type'],
                'status' => StockMove::STATUS_WAITING,
                'ref_type' => $data['ref_type'] ?? null,
                'ref_id' => $data['ref_id'] ?? null,
                'expiry_date' => $data['expiry_date'] ?? null,
                'reason' => $data['reason'] ?? null,
                'user_id' => $user?->id,
            ]);

            // Backdate created_at (update() tak mempan: tak fillable + auto-touch).
            if (! empty($data['occurred_at'])) {
                $when = Carbon::parse($data['occurred_at'])->setTimeFrom(now());
                $move->timestamps = false;
                $move->created_at = $when;
                $move->updated_at = $when;
                $move->save();
            }

            return $move;
        });
    }

    /** Validasi waiting/ready -> done + terapkan stok. */
    public function validateMove(StockMove $move, ?User $validator = null, bool $allowNegative = false): StockMove
    {
        if (! in_array($move->status, [StockMove::STATUS_WAITING, StockMove::STATUS_READY], true)) {
            throw new InvalidArgumentException('Hanya mutasi menunggu/siap yang bisa divalidasi.');
        }

        return DB::transaction(function () use ($move, $validator, $allowNegative) {
            $product = Product::lockForUpdate()->find($move->product_id);
            if (! $product) {
                throw new InvalidArgumentException('Produk mutasi sudah tidak ada.');
            }

            match ($move->type) {
                StockMove::TYPE_RECEIPT => $this->applyReceipt($product, $move),
                StockMove::TYPE_DELIVERY => $this->applyDelivery($product, $move, $allowNegative),
                StockMove::TYPE_TRANSFER => null, // relokasi: angka global tetap
                StockMove::TYPE_ADJUSTMENT => $this->applyAdjustment($product, $move, $allowNegative),
                StockMove::TYPE_SCRAP => $this->applyDelivery($product, $move, $allowNegative),
                default => throw new InvalidArgumentException('Tipe mutasi tidak dikenal.'),
            };

            $move->update([
                'status' => StockMove::STATUS_DONE,
                'validated_by' => $validator?->id,
                'validated_at' => now(),
            ]);

            return $move->fresh();
        });
    }

    public function cancelMove(StockMove $move): StockMove
    {
        if (! in_array($move->status, [StockMove::STATUS_WAITING, StockMove::STATUS_READY], true)) {
            throw new InvalidArgumentException('Hanya mutasi menunggu/siap yang bisa dibatalkan.');
        }
        $move->update(['status' => StockMove::STATUS_CANCELLED]);

        return $move->fresh();
    }

    /** Reversal move done via adjustment baru (audit utuh). */
    public function reverseMove(StockMove $move, ?User $user = null, ?string $reason = null, bool $allowNegative = true): StockMove
    {
        if ($move->status !== StockMove::STATUS_DONE) {
            throw new InvalidArgumentException('Hanya mutasi selesai yang bisa direversal.');
        }

        // Reversal receipt: kembalikan ke batch asal. Reversal delivery hanya
        // bisa presisi bila baris asal memakai satu batch (bukan daftar FEFO).
        $reverseBatch = null;
        if ($move->batch_no && ! str_contains((string) $move->batch_no, ',')) {
            $reverseBatch = $move->batch_no;
        }

        $reversal = $this->createMove([
            'product_id' => $move->product_id,
            'qty' => (float) $move->qty,
            'type' => StockMove::TYPE_ADJUSTMENT,
            'from_location' => $move->to_location,
            'to_location' => $move->from_location,
            'batch_no' => $reverseBatch,
            'expiry_date' => $reverseBatch ? $move->expiry_date?->toDateString() : null,
            'reason' => $reason ?? "Reversal mutasi #{$move->id}",
            'ref_type' => 'stock_move_reversal',
            'ref_id' => $move->id,
        ], $user);

        return $this->validateMove($reversal, $user, $allowNegative);
    }

    /** Jejak import/bulk yang stoknya sudah diterapkan (tanpa ubah stok). */
    public function logExternalMove(int $productId, float $qty, string $type, ?string $refType = null, ?int $refId = null, ?User $user = null, ?string $reason = null, ?string $from = null, ?string $to = null): StockMove
    {
        return StockMove::create([
            'warehouse_id' => Warehouse::default()?->id,
            'product_id' => $productId,
            'qty' => $qty,
            'type' => $type,
            'status' => StockMove::STATUS_DONE,
            'from_location' => $from ?? $this->defaultFrom($type),
            'to_location' => $to ?? $this->defaultTo($type),
            'ref_type' => $refType,
            'ref_id' => $refId,
            'reason' => $reason,
            'user_id' => $user?->id,
            'validated_by' => $user?->id,
            'validated_at' => now(),
        ]);
    }

    /** Stok tersedia = on-hand - reservasi terbuka. */
    public function availableQty(int $productId): float
    {
        $product = Product::find($productId);
        if (! $product) {
            return 0;
        }
        $reserved = (float) StockMove::where('product_id', $productId)
            ->where('type', StockMove::TYPE_DELIVERY)
            ->whereIn('status', [StockMove::STATUS_WAITING, StockMove::STATUS_READY])
            ->sum('qty');

        return max(0, (float) $product->stock - $reserved);
    }

    protected function applyReceipt(Product $product, StockMove $move): void
    {
        $qty = (float) $move->qty;
        $product->increment('stock', $qty);

        $batchNo = $move->batch_no ?: null;
        $expiry = $move->expiry_date?->toDateString();

        // Batch yang sama (produk + no batch + expiry) digabung, bukan dibuat ulang.
        $batch = null;
        if ($batchNo !== null) {
            $batch = ProductBatch::where('product_id', $product->id)
                ->where('batch_no', $batchNo)
                ->when(
                    $expiry !== null,
                    fn ($q) => $q->whereDate('expiry_date', $expiry),
                    fn ($q) => $q->whereNull('expiry_date')
                )
                ->lockForUpdate()
                ->first();
        }

        if ($batch) {
            $batch->increment('qty_initial', $qty);
            $batch->increment('qty_current', $qty);
            if ($expiry !== null && ! $batch->expiry_date) {
                $batch->update(['expiry_date' => $expiry]);
            }

            return;
        }

        $batch = ProductBatch::create([
            'product_id' => $product->id,
            'batch_no' => $batchNo,
            'receipt_date' => $move->created_at ? $move->created_at->toDateString() : today()->toDateString(),
            'expiry_date' => $move->expiry_date,
            'qty_initial' => $qty,
            'qty_current' => $qty,
        ]);

        // Jangan membuat nomor batch otomatis bila tidak diisi (biarkan kosong/null).
    }

    protected function applyDelivery(Product $product, StockMove $move, bool $allowNegative = false): void
    {
        $qty = (float) $move->qty;
        if (! $allowNegative && (float) $product->stock < $qty) {
            throw new InvalidArgumentException("Stok {$product->product_name} tidak cukup (tersedia {$product->stock}, diminta {$qty}).");
        }
        $product->decrement('stock', $qty);
        $used = $this->deductBatchesFefo(
            $product->id,
            $qty,
            $move->batch_no,
            $move->expiry_date?->toDateString()
        );
        // Jangan auto-isi batch_no jika user mengosongkannya. Batch kosong harus tetap null / '-'.
    }

    protected function applyAdjustment(Product $product, StockMove $move, bool $allowNegative = false): void
    {
        // To Stock = tambah, from Stock = kurang.
        if ($move->to_location === StockMove::LOC_STOCK && $move->from_location !== StockMove::LOC_STOCK) {
            $this->applyReceipt($product, $move);
        } else {
            $this->applyDelivery($product, $move, $allowNegative);
        }
    }

    /** Kurangi batch FEFO (expired terdekat dulu), dengan batch pilihan lebih dulu. */
    protected function deductBatchesFefo(int $productId, float $qty, ?string $preferredBatch = null, ?string $preferredExpiry = null): array
    {
        $remaining = $qty;
        $used = [];
        $batches = ProductBatch::where('product_id', $productId)->available()->fefo()->lockForUpdate()->get();

        // Reversal receipt: potong batch asal lebih dulu agar batch lain tidak ikut.
        if ($preferredBatch !== null && $preferredBatch !== '' && ! str_contains($preferredBatch, ',')) {
            $preferred = $batches->first(function ($b) use ($preferredBatch, $preferredExpiry) {
                if ((string) $b->batch_no !== $preferredBatch) {
                    return false;
                }
                if ($preferredExpiry !== null && $preferredExpiry !== '') {
                    return $b->expiry_date?->toDateString() === $preferredExpiry;
                }

                return true;
            });

            if ($preferred) {
                $take = min((float) $preferred->qty_current, $remaining);
                if ($take > 0) {
                    $preferred->decrement('qty_current', $take);
                    $remaining -= $take;
                    if (! empty($preferred->batch_no)) {
                        $used[] = $preferred->batch_no;
                    }
                }
                // Batch pilihan sudah habis/kosong: lanjut FEFO untuk sisanya.
            }
        }

        foreach ($batches as $batch) {
            if ($remaining <= 0) {
                break;
            }
            if (! empty($used) && ($batch->batch_no ?? null) === $used[0]) {
                continue;
            }
            $take = min((float) $batch->qty_current, $remaining);
            if ($take <= 0) {
                continue;
            }
            $batch->decrement('qty_current', $take);
            $remaining -= $take;
            if (! empty($batch->batch_no)) {
                $used[] = $batch->batch_no;
            }
        }

        // Sisa tanpa batch (stok lama) dibiarkan.
        return array_values(array_unique(array_filter($used)));
    }

    protected function defaultFrom(string $type): string
    {
        return match ($type) {
            StockMove::TYPE_RECEIPT => 'Vendor',
            StockMove::TYPE_ADJUSTMENT => StockMove::LOC_ADJUSTMENT,
            StockMove::TYPE_SCRAP => StockMove::LOC_STOCK,
            default => StockMove::LOC_STOCK,
        };
    }

    protected function defaultTo(string $type): string
    {
        return match ($type) {
            StockMove::TYPE_DELIVERY => 'Customer',
            StockMove::TYPE_SCRAP => StockMove::LOC_SCRAP,
            default => StockMove::LOC_STOCK,
        };
    }
}

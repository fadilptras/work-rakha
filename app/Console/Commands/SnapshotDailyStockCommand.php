<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use App\Models\DailyStockHistory;

class SnapshotDailyStockCommand extends Command
{
    protected $signature = 'stock:snapshot-daily';
    protected $description = 'Rekam snapshot stok semua produk aktif untuk hari ini (end-of-day)';

    public function handle(): int
    {
        $tanggal = date('Y-m-d');
        $products = Product::active()->get();

        if ($products->isEmpty()) {
            $this->info('Tidak ada produk aktif.');
            return self::SUCCESS;
        }

        $barisBaru = 0;
        $barisUpdate = 0;

        foreach ($products as $p) {
            $exists = DailyStockHistory::where('product_id', $p->id)
                ->where('tanggal', $tanggal)
                ->exists();

            DailyStockHistory::updateOrCreate(
                ['product_id' => $p->id, 'tanggal' => $tanggal],
                ['stok' => $p->stock, 'stok_po' => $p->stock_po]
            );

            if ($exists) {
                $barisUpdate++;
            } else {
                $barisBaru++;
            }
        }

        $this->info("Snapshot {$tanggal}: {$barisBaru} baru, {$barisUpdate} diperbarui dari {$products->count()} produk.");
        return self::SUCCESS;
    }
}
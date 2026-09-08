<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ArchiveDailyStockHistoryCommand extends Command
{
    protected $signature = 'stock:archive-daily-history {--older-than=12 : Bulan lebih lama dari ini akan di-rollup & dihapus}';
    protected $description = 'Rollup daily_stock_histories lama menjadi ringkasan bulanan & hapus baris harian yang sudah diarsip';

    public function handle(): int
    {
        $olderThan = max(1, (int) $this->option('older-than'));
        $cutoffDate = Carbon::now()->subMonths($olderThan)->endOfMonth()->toDateTimeString();

        $this->info("Mengarsipkan data harian sebelum {$cutoffDate} (>{olderThan} bulan lalu)...");

        $grouped = DB::table('daily_stock_histories')
            ->whereNotNull('product_id')
            ->where('product_id', '!=', 0)
            ->where('tanggal', '<', $cutoffDate)
            ->select('product_id', DB::raw('YEAR(tanggal) as year'), DB::raw('MONTH(tanggal) as month'),
                     DB::raw('MIN(stok) as stok_min'), DB::raw('MAX(stok) as stok_max'),
                     DB::raw('AVG(stok) as stok_avg'), DB::raw('MAX(stok_po) as stok_po'))
            ->groupBy('product_id', DB::raw('YEAR(tanggal)'), DB::raw('MONTH(tanggal)'))
            ->get();

        $diproses = 0;

        foreach ($grouped as $row) {
            DB::table('monthly_stock_histories')->updateOrInsert(
                [
                    'product_id' => $row->product_id,
                    'year'       => $row->year,
                    'month'      => $row->month,
                ],
                [
                    'stok_min' => (int) $row->stok_min,
                    'stok_max' => (int) $row->stok_max,
                    'stok_avg' => round((float) $row->stok_avg, 2),
                    'stok_po'  => (int) $row->stok_po,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
            $diproses++;
        }

        $dihapus = DB::table('daily_stock_histories')
            ->where('tanggal', '<', $cutoffDate)
            ->delete();

        $this->info("Selesai: {$diproses} ringkasan bulanan diarsipkan, {$dihapus} baris harian dihapus.");
        return self::SUCCESS;
    }
}
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use App\Models\Absensi;

class DeleteOldPhotos extends Command
{
    protected $signature = 'photos:clean-old';
    protected $description = 'Menghapus foto absensi yang lebih tua dari 2 bulan';

    public function handle()
    {
        // Cari batas waktu (2 bulan yang lalu)
        $twoMonthsAgo = Carbon::now()->subMonths(2);

        // Ambil data absensi yang usianya lebih dari 2 bulan DAN memiliki lampiran masuk
        $oldRecords = Absensi::where('created_at', '<', $twoMonthsAgo)
                             ->whereNotNull('lampiran') 
                             ->get();

        $count = 0;

        foreach ($oldRecords as $record) {
            // Hapus file fisik lampiran masuk dari storage
            if ($record->lampiran && Storage::disk('public')->exists($record->lampiran)) {
                Storage::disk('public')->delete($record->lampiran);
            }
            
            // Hapus file fisik lampiran keluar dari storage jika ada
            if ($record->lampiran_keluar && Storage::disk('public')->exists($record->lampiran_keluar)) {
                Storage::disk('public')->delete($record->lampiran_keluar);
            }

            // Kosongkan nama file di database
            $record->update([
                'lampiran' => null,
                'lampiran_keluar' => null
            ]);

            $count++;
        }

        $this->info("Proses selesai! Sebanyak {$count} data foto lama berhasil dihapus.");
    }
}
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class ResetCutiCommand extends Command
{
    protected $signature = 'cuti:reset-tahunan';
    protected $description = 'Mereset kuota sisa cuti tahunan karyawan kembali ke 12 (pada awal tahun)';

    public function handle()
    {
        $this->info("Memulai proses reset cuti tahunan...");
        
        $users = User::all();
        $updatedCount = 0;

        foreach ($users as $user) {
            $user->update(['sisa_cuti' => 12]);
            $updatedCount++;
        }

        Log::info("Reset Cuti Tahunan Berhasil. Total karyawan diupdate: {$updatedCount}");
        $this->info("Selesai! {$updatedCount} karyawan telah di-reset sisa cutinya menjadi 12.");
    }
}

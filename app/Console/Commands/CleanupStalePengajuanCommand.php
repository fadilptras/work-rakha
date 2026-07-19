<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Cuti;
use App\Models\PengajuanDana;
use App\Models\PengajuanBarang;
use Carbon\Carbon;

class CleanupStalePengajuanCommand extends Command
{
    protected $signature = 'pengajuan:cleanup-stale';
    protected $description = 'Otomatis me-reject pengajuan yang sudah menggantung > 3 hari tanpa approval';

    public function handle()
    {
        $limitDate = Carbon::now()->subDays(3);
        $this->info("Membersihkan pengajuan sebelum {$limitDate}");

        // 1. Cuti
        $cutis = Cuti::where('status', 'diajukan')
                     ->where('created_at', '<', $limitDate)
                     ->get();
        foreach ($cutis as $c) {
            $c->update([
                'status' => 'ditolak',
                'catatan_approver_1' => $c->catatan_approver_1 ?? 'Ditolak Otomatis oleh Sistem: Melewati batas waktu approval (3 Hari)'
            ]);
        }
        $this->info("Cuti ditolak: " . $cutis->count());

        // 2. Pengajuan Dana
        $danas = PengajuanDana::where('status', 'diajukan')
                              ->where('created_at', '<', $limitDate)
                              ->get();
        foreach ($danas as $d) {
            $d->update([
                'status' => 'ditolak',
                'approver_1_catatan' => $d->approver_1_catatan ?? 'Ditolak Otomatis oleh Sistem: Melewati batas waktu approval (3 Hari)'
            ]);
        }
        $this->info("Pengajuan Dana ditolak: " . $danas->count());

        // 3. Pengajuan Barang
        $barangs = PengajuanBarang::whereIn('status', ['diajukan', 'diproses', 'proses_finalisasi'])
                                  ->where('created_at', '<', $limitDate)
                                  ->get();
        foreach ($barangs as $b) {
            $b->update([
                'status' => 'ditolak',
                'catatan_approver_1' => $b->catatan_approver_1 ?? 'Ditolak Otomatis oleh Sistem: Melewati batas waktu approval (3 Hari)'
            ]);
        }
        $this->info("Pengajuan Barang ditolak: " . $barangs->count());
        
        $this->info("Cleanup selesai.");
    }
}

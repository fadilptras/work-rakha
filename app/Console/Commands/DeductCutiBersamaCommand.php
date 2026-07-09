<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Holiday;
use App\Models\User;
use App\Models\Cuti;
use App\Models\CutiBersamaLedger;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DeductCutiBersamaCommand extends Command
{
    protected $signature = 'cuti:deduct-bersama';
    protected $description = 'Otomatis memotong sisa cuti karyawan berdasarkan hari cuti bersama';

    public function handle()
    {
        // 1. Ambil holiday yang merupakan cuti bersama dan sudah/sedang terjadi
        $pastCutiBersamas = Holiday::where('is_cuti_bersama', true)
            ->whereDate('tanggal', '<=', Carbon::today())
            ->get();

        if ($pastCutiBersamas->isEmpty()) {
            $this->info('Tidak ada data cuti bersama yang perlu diproses.');
            return;
        }

        $allUsers = User::all();

        foreach ($pastCutiBersamas as $holiday) {
            $this->warn("\n==================================================");
            $this->info("Memproses: {$holiday->keterangan} ({$holiday->tanggal->format('Y-m-d')})");
            $this->warn("==================================================");

            foreach ($allUsers as $user) {
                DB::beginTransaction();
                try {
                    // Cek apakah sudah pernah dipotong (Ledger)
                    $isDeducted = CutiBersamaLedger::where('user_id', $user->id)
                        ->where('holiday_id', $holiday->id)
                        ->exists();

                    // JIKA BELUM PERNAH DIPOTONG
                    if (!$isDeducted) {
                        
                        // Cek apakah sisa cuti masih ada
                        if ($user->sisa_cuti > 0) {
                            
                            // 1. Buat record di tabel cutis (Riwayat)
                            // Catatan: 'jenis_cuti' saya ubah ke 'Tahunan' agar tidak truncated
                            Cuti::create([
                                'user_id'           => $user->id,
                                'jenis_cuti'        => 'cuti bersama', 
                                'tanggal_mulai'     => $holiday->tanggal,
                                'tanggal_selesai'   => $holiday->tanggal,
                                'total_hari'        => 1,
                                'alasan'            => 'Potong Otomatis: ' . $holiday->keterangan,
                                'status'            => 'disetujui',
                                'status_approver_1' => 'skipped',
                                'status_approver_2' => 'skipped',
                                'status_approver_3' => 'skipped',
                            ]);

                            // 2. Potong sisa_cuti di tabel users
                            $user->decrement('sisa_cuti', 1);

                            // 3. Catat ke ledger agar tidak diproses ulang
                            CutiBersamaLedger::create([
                                'user_id'    => $user->id,
                                'holiday_id' => $holiday->id
                            ]);

                            $this->line("<info>[BERHASIL]</info> User: {$user->name} (Sisa Cuti: {$user->sisa_cuti})");
                        } else {
                            $this->line("<comment>[SKIP]</comment> User: {$user->name} (Sisa cuti sudah 0)");
                        }
                    } else {
                        $this->line("<comment>[SKIP]</comment> User: {$user->name} (Sudah pernah diproses)");
                    }

                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                    $this->error("Gagal pada user {$user->name}: " . $e->getMessage());
                }
            }
        }

        $this->info("\nSemua proses selesai.");
    }
}
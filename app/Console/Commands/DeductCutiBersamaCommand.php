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

            $hasNewDeduction = false;

            foreach ($allUsers as $user) {
                DB::beginTransaction();
                try {
                    // Cek apakah sudah pernah dipotong (Ledger)
                    $isDeducted = CutiBersamaLedger::where('user_id', $user->id)
                        ->where('holiday_id', $holiday->id)
                        ->exists();

                    // JIKA BELUM PERNAH DIPOTONG
                    if (!$isDeducted) {
                        
                        // JANGAN POTONG JIKA USER BERGABUNG SETELAH TANGGAL CUTI BERSAMA
                        if ($user->created_at > $holiday->tanggal->endOfDay()) {
                            CutiBersamaLedger::create([
                                'user_id'    => $user->id,
                                'holiday_id' => $holiday->id
                            ]);
                            $this->line("<comment>[SKIP]</comment> User: {$user->name} (Bergabung setelah cuti bersama)");
                            DB::commit();
                            continue;
                        }

                        // Cek apakah sisa cuti masih ada
                        if ($user->sisa_cuti > 0) {
                            
                            // 1. Buat record di tabel cutis (Riwayat)
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

                            $hasNewDeduction = true;

                            $this->line("<info>[BERHASIL]</info> User: {$user->name} (Sisa Cuti: {$user->sisa_cuti})");
                        } else {
                            // Catat ke ledger agar tidak diproses ulang di kemudian hari saat saldo bertambah
                            CutiBersamaLedger::create([
                                'user_id'    => $user->id,
                                'holiday_id' => $holiday->id
                            ]);
                            $this->line("<comment>[SKIP]</comment> User: {$user->name} (Sisa cuti sudah 0, dicatat ke ledger)");
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

            if ($hasNewDeduction) {
                $this->sendNotificationToGroup($holiday);
            }
        }

        $this->info("\nSemua proses selesai.");
    }

    /**
     * Mengirim notifikasi informasi Cuti Bersama ke Whatsapp Group via Fonnte.
     */
    private function sendNotificationToGroup(Holiday $holiday)
    {
        $namaLibur = $holiday->keterangan ?? 'Cuti Bersama';
        $message = "Informasi Cuti Bersama 🏖️\n\nHari ini Kantor Libur dalam rangka: *{$namaLibur}*\nSelamat beristirahat!";

        $targetGroupId = '120363242834102956@g.us';
        $token = 'MP8iwGyRDCKJVgNs5ejZ';

        if (!$targetGroupId || !$token) {
            \Illuminate\Support\Facades\Log::error("Fonnte config tidak lengkap untuk notifikasi Cuti Bersama.");
            return;
        }

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.fonnte.com/send',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30, 
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => array(
                'target' => $targetGroupId,
                'message' => $message,
                'countryCode' => '62',
            ),
            CURLOPT_HTTPHEADER => array(
                'Authorization: ' . $token
            ),
        ));

        $response = curl_exec($curl);
        $error = curl_error($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($error) {
            \Illuminate\Support\Facades\Log::error("Fonnte Error saat mengirim Notifikasi Cuti Bersama ke Group: " . $error);
        } else {
            \Illuminate\Support\Facades\Log::info("Fonnte Notifikasi Cuti Bersama Terkirim ke Group (HTTP $httpCode): " . $response);
        }
    }
}
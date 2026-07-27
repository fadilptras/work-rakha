<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Absensi;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SendAbsensiSummaryCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-absensi-summary {type}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mengirimkan rekap absensi per divisi ke Fonnte';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $type = $this->argument('type');
        $today = Carbon::today()->toDateString();
        $dateText = Carbon::now()->locale('id')->translatedFormat('l, d F Y');
        
        $absensis = Absensi::with('user')
            ->whereDate('tanggal', $today)
            ->get();
            
        if ($absensis->isEmpty()) {
            $this->info("Tidak ada data absensi untuk hari ini ($today).");
            return;
        }

        $title = "";
        if ($type === 'morning') {
            $title = "[REKAP ABSENSI PAGI - 08:30]\nTanggal: " . $dateText . "\n\n";
        } elseif ($type === 'evening') {
            $title = "[REKAP ABSENSI PULANG - 17:30]\nTanggal: " . $dateText . "\n\n";
        } else {
            $this->error("Tipe tidak valid. Gunakan: morning, evening");
            return;
        }
        
        $message = $title;
        
        // Group by Divisi
        $groupedAbsensi = $absensis->groupBy(function ($item) {
            return $item->user->divisi ?? 'Tanpa Divisi';
        });
        
        // Sort by division name alphabetically
        $groupedAbsensi = $groupedAbsensi->sortKeys();
        
        foreach ($groupedAbsensi as $divisi => $items) {
            $message .= "[Divisi: " . strtoupper($divisi) . "]\n";
            $message .= "-----------------------------------\n";
            
            $count = 1;
            foreach ($items as $absen) {
                $nama = $absen->user->name ?? 'Unknown';
                
                if ($type === 'morning') {
                    if (!$absen->jam_masuk) continue;
                    
                    $jamMasuk = $absen->jam_masuk;
                    $standarMasuk = Carbon::parse($today . ' 08:00:00');
                    $waktuMasuk = Carbon::parse($today . ' ' . $jamMasuk);
                    
                    $keteranganTelat = "";
                    if ($waktuMasuk->greaterThan($standarMasuk)) {
                        $keteranganTelat = " (Telat)";
                    }
                    
                    // Potong detik pada jam_masuk agar lebih rapi (dari 08:00:00 menjadi 08:00)
                    $jamMasukRapi = substr($jamMasuk, 0, 5);
                    $message .= "{$count}. {$nama} - {$jamMasukRapi}{$keteranganTelat}\n";
                    $count++;
                    
                } else {
                    // Evening
                    if (!$absen->jam_keluar) continue;
                    
                    $jamKeluar = $absen->jam_keluar;
                    $jamKeluarRapi = substr($jamKeluar, 0, 5);
                    $message .= "{$count}. {$nama} - {$jamKeluarRapi} (Selesai)\n";
                    $count++;
                }
            }
            
            if ($count === 1) {
                 $message .= "- Belum ada data absensi -\n";
            }
            $message .= "\n";
        }
        
        // Final message to send
        if (trim($message) === trim($title)) {
            $this->info("Tidak ada detail absensi yang bisa dikirim.");
            return;
        }

        $this->sendToFonnte($message);
        $this->info("Berhasil mengirim rekap absensi tipe: $type");
    }
    
    private function sendToFonnte($message)
    {
        $targetGroupId = '120363242834102956@g.us';
        $token = 'MP8iwGyRDCKJVgNs5ejZ';
        
        if (!$targetGroupId || !$token) {
            Log::error("Fonnte config missing (group_id atau token). Batal mengirim Rekap Absensi.");
            $this->error("Fonnte config missing.");
            return;
        }

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.fonnte.com/send',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30, // 30 secs to prevent hanging
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => array(
                'target' => $targetGroupId,
                'message' => $message,
                'countryCode' => '62', // Default Indonesia
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
            Log::error("Fonnte Error saat mengirim Rekap Absensi: " . $error);
            $this->error("Fonnte Error: " . $error);
        } else {
            Log::info("Fonnte Rekap Absensi Terkirim (HTTP $httpCode): " . $response);
        }
    }
}

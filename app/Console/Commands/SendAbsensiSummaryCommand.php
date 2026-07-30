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

        $this->sendToLocalWhatsApp($message);
        $this->info("Berhasil mengirim rekap absensi tipe: $type");
    }
    
    private function sendToLocalWhatsApp($message)
    {
        $targetGroupId = env('WHATSAPP_GROUP_ID', '120363242834102956@g.us');
        $waApiUrl = env('WA_API_URL', 'http://localhost:3000/send');

        if (!$targetGroupId) {
            Log::error("WA config missing (group_id). Batal mengirim Rekap Absensi.");
            $this->error("WA config missing.");
            return;
        }

        try {
            $response = \Illuminate\Support\Facades\Http::post($waApiUrl, [
                'target' => $targetGroupId,
                'message' => $message
            ]);

            if ($response->successful()) {
                Log::info("WA Server Rekap Absensi Terkirim: " . $response->body());
            } else {
                Log::error("WA Server Error saat mengirim Rekap Absensi: " . $response->body());
                $this->error("WA Server Error: " . $response->body());
            }
        } catch (\Exception $e) {
            Log::error("WA Server Exception saat mengirim Rekap Absensi: " . $e->getMessage());
            $this->error("WA Server Exception: " . $e->getMessage());
        }
    }
}

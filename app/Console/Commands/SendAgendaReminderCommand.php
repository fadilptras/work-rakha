<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Agenda;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SendAgendaReminderCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-agenda-reminder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mengirimkan pengingat agenda esok hari secara japri ke peserta (H-1 malam)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tomorrow = Carbon::tomorrow();
        $tomorrowString = $tomorrow->toDateString();
        $dateText = $tomorrow->locale('id')->translatedFormat('l, d F Y');

        // Ambil semua agenda yang dijadwalkan besok beserta peserta (guests)
        $agendas = Agenda::with('guests')
            ->whereDate('start_time', $tomorrowString)
            ->get();

        if ($agendas->isEmpty()) {
            $this->info("Tidak ada agenda untuk besok ($dateText).");
            return;
        }

        // Kelompokkan agenda berdasarkan User ID
        $userAgendas = [];

        foreach ($agendas as $agenda) {
            foreach ($agenda->guests as $guest) {
                if (!isset($userAgendas[$guest->id])) {
                    $userAgendas[$guest->id] = [
                        'user' => $guest,
                        'agendas' => []
                    ];
                }
                $userAgendas[$guest->id]['agendas'][] = $agenda;
            }
        }

        if (empty($userAgendas)) {
            $this->info("Ada agenda, tetapi tidak ada peserta yang diundang.");
            return;
        }

        $token = config('services.fonnte.token');
        if (!$token) {
            $this->error("Token Fonnte tidak dikonfigurasi.");
            Log::error("Fonnte token missing untuk Agenda Reminder.");
            return;
        }

        $sentCount = 0;

        // Loop dan kirim WA Japri ke masing-masing User
        foreach ($userAgendas as $userId => $data) {
            $user = $data['user'];
            $listAgenda = $data['agendas'];

            $rawPhone = $user->nomor_telepon;
            if (empty($rawPhone)) {
                Log::warning("Agenda Reminder Skip: User {$user->name} tidak memiliki nomor telepon.");
                continue;
            }

            // Standarisasi nomor HP ke 62
            $cleanPhone = preg_replace('/[^0-9]/', '', $rawPhone);
            if (str_starts_with($cleanPhone, '08')) {
                $targetPhone = '628' . substr($cleanPhone, 2);
            } elseif (str_starts_with($cleanPhone, '62')) {
                $targetPhone = $cleanPhone;
            } else {
                $targetPhone = $cleanPhone;
            }

            // Susun Pesan
            $message = "[PENGINGAT AGENDA BESOK]\n";
            $message .= "Halo {$user->name},\n\nBerikut adalah jadwal Agenda Anda untuk besok ({$dateText}):\n\n";

            $no = 1;
            foreach ($listAgenda as $agenda) {
                $judul = $agenda->title;
                $waktu = Carbon::parse($agenda->start_time)->translatedFormat('H:i');
                $lokasi = $agenda->location ?? 'Online / Belum ditentukan';
                
                $message .= "{$no}. [{$waktu}] - {$judul}\n";
                $message .= "   📍 Lokasi: {$lokasi}\n\n";
                $no++;
            }

            $message .= "Mohon persiapkan diri Anda dan hadir tepat waktu. Terima kasih!\n";
            $message .= "🔗 Cek detail: " . route('dashboard');

            // Kirim via Fonnte
            $this->sendToFonntePersonal($targetPhone, $message, $token);
            $sentCount++;
        }

        $this->info("Berhasil mengirim pengingat agenda ke $sentCount orang untuk besok.");
    }

    private function sendToFonntePersonal($target, $message, $token)
    {
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
                'target' => $target,
                'message' => $message,
                'countryCode' => '62',
            ),
            CURLOPT_HTTPHEADER => array(
                'Authorization: ' . $token
            ),
        ));

        $response = curl_exec($curl);
        $error = curl_error($curl);
        curl_close($curl);

        if ($error) {
            Log::error("Fonnte Error (Agenda Reminder to $target): " . $error);
        } else {
            // Optional: log response untuk debugging
            // Log::info("Fonnte (Agenda Reminder to $target): " . $response);
        }
    }
}

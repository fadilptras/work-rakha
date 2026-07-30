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

        // Ambil semua agenda yang dijadwalkan besok beserta peserta (guests) dan pembuat (creator)
        $agendas = Agenda::with(['guests', 'creator'])
            ->whereDate('start_time', $tomorrowString)
            ->get();

        if ($agendas->isEmpty()) {
            $this->info("Tidak ada agenda untuk besok ($dateText).");
            return;
        }

        // Kelompokkan agenda berdasarkan User ID
        $userAgendas = [];

        foreach ($agendas as $agenda) {
            // Kumpulkan semua peserta (pembuat + tamu)
            $participants = $agenda->guests->push($agenda->creator)->filter()->unique('id');

            foreach ($participants as $participant) {
                if (!isset($userAgendas[$participant->id])) {
                    $userAgendas[$participant->id] = [
                        'user' => $participant,
                        'agendas' => []
                    ];
                }
                $userAgendas[$participant->id]['agendas'][] = $agenda;
            }
        }

        if (empty($userAgendas)) {
            $this->info("Ada agenda, tetapi tidak ada peserta yang diundang.");
            return;
        }

        // Remove Fonnte token check

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

            // Kirim via Local WA Server
            $this->sendToLocalWhatsApp($targetPhone, $message);
            $sentCount++;
        }

        $this->info("Berhasil mengirim pengingat agenda ke $sentCount orang untuk besok.");
    }

    private function sendToLocalWhatsApp($target, $message)
    {
        $waApiUrl = env('WA_API_URL', 'http://localhost:3000/send');

        try {
            $response = \Illuminate\Support\Facades\Http::post($waApiUrl, [
                'target' => $target,
                'message' => $message
            ]);

            if (!$response->successful()) {
                Log::error("WA Server Error (Agenda Reminder to $target): " . $response->body());
            }
        } catch (\Exception $e) {
            Log::error("WA Server Exception (Agenda Reminder to $target): " . $e->getMessage());
        }
    }
}

<?php

namespace App\Notifications;

use App\Models\Agenda;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use App\Notifications\Channels\LocalWhatsAppChannel;
use Carbon\Carbon;

/**
 * Notifikasi Agenda
 * 
 * Sifat: Transaksional (Dikirim secara Japri / Direct Message ke Peserta).
 * Fitur ini tidak menggunakan Group ID.
 */
class AgendaNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @var \App\Models\Agenda $agenda Data agenda yang terkait
     */
    public Agenda $agenda;

    /**
     * @var string $type Tipe notifikasi ('undangan_baru', 'agenda_diperbarui', 'agenda_dibatalkan')
     */
    public string $type;

    /**
     * @var string $creatorName Nama pembuat/pengundang agenda
     */
    public string $creatorName;

    /**
     * Konstruktor Notifikasi Agenda
     *
     * @param \App\Models\Agenda $agenda
     * @param string $type Konteks pengiriman
     * @param string $creatorName
     */
    public function __construct(Agenda $agenda, string $type, string $creatorName)
    {
        $this->agenda = $agenda;
        $this->type = $type;
        $this->creatorName = $creatorName;
    }

    /**
     * Menentukan channel pengiriman (Database & WhatsApp)
     */
    public function via(object $notifiable): array
    {
        return ['database', LocalWhatsAppChannel::class]; 
    }

    /**
     * Format notifikasi untuk pengiriman via WhatsApp.
     * Karena tidak mendefinisikan 'target', pesan otomatis dikirim secara personal.
     */
    public function toWhatsApp(object $notifiable): array
    {
        $nama = $notifiable->name;
        $title = $this->agenda->title;
        $startC = Carbon::parse($this->agenda->start_time);
        $endC = $this->agenda->end_time ? Carbon::parse($this->agenda->end_time) : null;

        // Format baru: "Kamis, 17 September 2026 - 12.00 s/d 13.00" (satu hari)
        // Jika beda hari: "Kamis, 17 September 2026 12.00 s/d Jumat, 18 September 2026 13.00"
        if ($endC) {
            if ($startC->isSameDay($endC)) {
                $waktu = $startC->translatedFormat('l, d F Y') . ' - ' . $startC->translatedFormat('H.i') . ' s/d ' . $endC->translatedFormat('H.i');
            } else {
                $waktu = $startC->translatedFormat('l, d F Y H.i') . ' s/d ' . $endC->translatedFormat('l, d F Y H.i');
            }
        } else {
            $waktu = $startC->translatedFormat('l, d F Y - H.i');
        }
        $location = $this->agenda->location ?? '-';
        $desc = $this->agenda->description ?? '-';

        $pesan = "";
        switch ($this->type) {
            case 'undangan_baru':
                $pesan = "📢 *[UNDANGAN AGENDA]*\n\nHalo *{$nama}*,\nAnda diundang oleh *{$this->creatorName}* untuk menghadiri agenda:\n\n📌 *{$title}*\n⏰ {$waktu}\n📍 {$location}\n📝 {$desc}";
                break;
            case 'agenda_diperbarui':
                $pesan = "📢 *[UPDATE AGENDA]*\n\nHalo *{$nama}*,\nAgenda *{$title}* telah diperbarui oleh *{$this->creatorName}*.\n\n⏰ {$waktu}\n📍 {$location}\n📝 {$desc}";
                break;
            case 'agenda_dibatalkan':
                $pesan = "📢 *[AGENDA DIBATALKAN]*\n\nHalo *{$nama}*,\nMohon maaf, agenda *{$title}* telah dibatalkan oleh *{$this->creatorName}*.";
                break;
        }

        return [
            'message' => $pesan
            // target sengaja tidak diset agar LocalWhatsAppChannel otomatis mengirim secara Japri ke nomor HP $notifiable
        ];
    }

    /**
     * Menyimpan data notifikasi ke dalam tabel 'notifications' (Database)
     */
    public function toArray(object $notifiable): array
    {
        $title = '';
        $message = '';
        $icon = 'fas fa-calendar-alt';
        $color = 'text-blue-500';

        switch ($this->type) {
            case 'undangan_baru':
                $title = 'Undangan Agenda Baru';
                $message = $this->creatorName . ' mengundang Anda ke agenda "' . $this->agenda->title . '".';
                break;
            case 'agenda_diperbarui':
                $title = 'Agenda Diperbarui';
                $message = $this->creatorName . ' memperbarui detail agenda "' . $this->agenda->title . '".';
                $icon = 'fas fa-calendar-check';
                $color = 'text-yellow-500';
                break;
            case 'agenda_dibatalkan':
                $title = 'Agenda Dibatalkan';
                $message = $this->creatorName . ' telah membatalkan agenda "' . $this->agenda->title . '".';
                $icon = 'fas fa-calendar-times';
                $color = 'text-red-500';
                break;
        }

        return [
            'title' => $title,
            'message' => $message,
            'icon' => $icon,
            'color' => $color,
            'url' => route('dashboard', ['agenda_id' => $this->agenda->id]),
        ];
    }
}

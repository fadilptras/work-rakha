<?php

namespace App\Notifications;

use App\Models\Agenda;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use App\Notifications\Channels\WhatsAppChannel;
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
        return ['database', WhatsAppChannel::class]; 
    }

    /**
     * Format notifikasi untuk pengiriman via WhatsApp.
     * Karena tidak mendefinisikan 'target', pesan otomatis dikirim secara personal.
     */
    public function toWhatsApp(object $notifiable): array
    {
        // Bypass pengiriman pesan otomatis (real-time) via WA untuk mengurangi spam.
        // Pengiriman informasi agenda akan direkap dan dikirim H-1 (malam hari) via Command SendAgendaReminderCommand.
        return [];
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

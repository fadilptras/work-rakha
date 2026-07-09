<?php

namespace App\Notifications;

use App\Models\Agenda;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Notifications\Channels\WhatsAppChannel;

class AgendaNotification extends Notification
{
    use Queueable;

    protected $agenda;
    protected $type;
    protected $creatorName;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Agenda $agenda, $type, $creatorName)
    {
        $this->agenda = $agenda;
        $this->type = $type;
        $this->creatorName = $creatorName;
    }

    public function via($notifiable)
    {
        return ['database', WhatsAppChannel::class]; 
    }


    public function toWhatsApp($notifiable)
    {
        $judul = $this->agenda->title;
        $waktu = \Carbon\Carbon::parse($this->agenda->start_time)->translatedFormat('l, d F Y H:i');
        $lokasi = $this->agenda->location ?? 'Online/Tidak ditentukan';
        $pembuat = $this->creatorName;
        $link = route('dashboard'); // Atau link detail agenda jika ada

        switch ($this->type) {
            case 'undangan_baru':
                $header = "📅 *UNDANGAN AGENDA BARU*";
                $pesan = "Halo {$notifiable->name},\nAnda diundang oleh *{$pembuat}* untuk menghadiri:\n\n📌 *{$judul}*\n🕒 {$waktu}\n📍 {$lokasi}\n\nMohon kehadirannya.";
                break;
            case 'agenda_diperbarui':
                $header = "✏️ *UPDATE AGENDA*";
                $pesan = "Halo {$notifiable->name},\nAgenda *{$judul}* telah diperbarui oleh {$pembuat}.\n\nWaktu Baru: {$waktu}\nLokasi: {$lokasi}\n\nSilakan cek detail terbaru.";
                break;
            case 'agenda_dibatalkan':
                $header = "❌ *AGENDA DIBATALKAN*";
                $pesan = "Halo {$notifiable->name},\nAgenda *{$judul}* yang dijadwalkan pada {$waktu} telah *DIBATALKAN* oleh {$pembuat}.";
                break;
            default:
                $header = "INFO AGENDA";
                $pesan = "Info mengenai agenda {$judul}.";
        }

        return ['message' => "{$header}\n\n{$pesan}\n\n🔗 Cek Dashboard: {$link}"];
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
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
            // --- PERUBAHAN DI SINI ---
            // Menggunakan route helper untuk membuat URL dengan parameter
            'url' => route('dashboard', ['agenda_id' => $this->agenda->id]),
        ];
    }
}
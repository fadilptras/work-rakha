<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use App\Notifications\Channels\WhatsAppChannel;

class CheckoutReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function via(object $notifiable)
    {
        return ['database', WhatsAppChannel::class];
    }

    public function toWhatsApp(object $notifiable)
    {
        return [
            'message' => "Halo {$notifiable->name}, \n\nSepertinya Anda belum melakukan *Absen Pulang* hari ini di sistem HRIS. \n\nMohon segera absen ya agar data kehadiran Anda tercatat lengkap. Terima kasih! 🙏"
        ];
    }

    public function toArray(object $notifiable)
    {
        return [
            'title'   => 'Jangan Lupa Absen Pulang!',
            'message' => 'Anda belum absen pulang hari ini. Yuk absen sekarang.',
            'url'     => '#',
            'icon'    => 'fas fa-clock',
            'color'   => 'text-amber-500',
        ];
    }
}

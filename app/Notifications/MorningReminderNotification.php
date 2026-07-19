<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use App\Notifications\Channels\WhatsAppChannel;

class MorningReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;
    public string $waGroupId;

    public function __construct(string $waGroupId)
    {
        $this->waGroupId = $waGroupId;
    }

    public function via(object $notifiable)
    {
        return [WhatsAppChannel::class];
    }

    public function toWhatsApp(object $notifiable)
    {
        return [
            'target' => $this->waGroupId,
            'message' => "*PENGINGAT ABSEN PAGI* ☀️\n\nSelamat pagi rekan-rekan semua! Jangan lupa untuk melakukan *Absen Masuk* pagi ini melalui sistem HRIS.\n\nSemoga harinya menyenangkan dan semangat bekerja! ☕🚀"
        ];
    }
}

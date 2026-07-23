<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use App\Models\Holiday;
use App\Notifications\Channels\WhatsAppChannel;

class HolidayNotification extends Notification implements ShouldQueue
{
    use Queueable;
    
    public Holiday $holiday;
    public bool $isWaBroadcast;

    /**
     * @param \App\Models\Holiday $holiday
     * @param bool $isWaBroadcast (Default: false)
     */
    public function __construct(Holiday $holiday, bool $isWaBroadcast = false)
    {
        $this->holiday = $holiday;
        $this->isWaBroadcast = $isWaBroadcast;
    }
    
    public function via(object $notifiable): array
    {
        return $this->isWaBroadcast ? [WhatsAppChannel::class] : ['database'];
    }

    public function toWhatsApp(object $notifiable): array
    {
        $namaLibur = $this->holiday->keterangan ?? 'Hari Libur Nasional';
        
        return [
            'message' => "Informasi Hari Libur 🏖️\n\n" .
                         "Mengingatkan bahwa hari ini kantor libur dalam rangka: *{$namaLibur}*\n" .
                         "Selamat beristirahat dan menikmati waktu luang! Sampai jumpa di hari kerja berikutnya.\n\n",
            'target'  => 'group'
        ];
    }

    public function toArray(object $notifiable): array
    {
        $namaLibur = $this->holiday->keterangan ?? 'Hari Libur';
        
        return [
            'title'   => 'Hari Ini Libur!',
            'message' => "Hari ini libur: {$namaLibur}. Selamat beristirahat!",
            'url'     => '#',
            'icon'    => 'fas fa-umbrella-beach', 
            'color'   => 'text-yellow-500',
        ];
    }
}

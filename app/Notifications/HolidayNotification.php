<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use App\Models\Holiday;
use App\Notifications\Channels\LocalWhatsAppChannel;

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
        return $this->isWaBroadcast ? [LocalWhatsAppChannel::class] : ['database'];
    }

    public function toWhatsApp(object $notifiable): array
    {
        $namaLibur = $this->holiday->keterangan ?? 'Hari Libur';
        $jenis = $this->holiday->is_cuti_bersama ? 'Cuti Bersama' : 'Hari Libur';
        
        return [
            'message' => "Informasi {$jenis} 🏖️\n\n" .
                         "Hari ini Kantor Libur dalam rangka: *{$namaLibur}*\n" .
                         "Selamat beristirahat!",
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

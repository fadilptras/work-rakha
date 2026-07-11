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
    
    public $holiday;
    public $isWaBroadcast;
    public $waGroupId;
    
    // Tambahkan parameter default untuk isWaBroadcast dan target Group
    public function __construct(Holiday $holiday, $isWaBroadcast = false, $waGroupId = null)
    {
        $this->holiday = $holiday;
        $this->isWaBroadcast = $isWaBroadcast;
        $this->waGroupId = $waGroupId;
    }
    
    public function via($notifiable)
    {
        // Jika flag bernilai true, khusus WhatsApp. Jika false, khusus database.
        return $this->isWaBroadcast ? [WhatsAppChannel::class] : ['database'];
    }

    public function toWhatsApp($notifiable)
    {
        $namaLibur = $this->holiday->keterangan ?? 'Hari Libur Nasional';

        return [
            'message' => "🏖️ *INFORMASI HARI LIBUR* 🏖️\n\n" .
                         "Mengingatkan bahwa hari ini kantor libur dalam rangka: *{$namaLibur}*\n" .
                         "Selamat beristirahat dan menikmati waktu luang! Sampai jumpa di hari kerja berikutnya.\n\n",
            // Ambil dari parameter, fallback ke config services
            'target'  => $this->waGroupId ?? config('services.fonnte.group_id') 
        ];
    }

    public function toArray($notifiable)
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
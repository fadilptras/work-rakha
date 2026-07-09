<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\User;
use App\Notifications\Channels\WhatsAppChannel;

class BirthdayNotification extends Notification
{
    use Queueable;

    public $user; 
    public $isWaBroadcast;
    public $waGroupId;

    // tambah parameter default false untuk isWaBroadcast
    public function __construct(User $user, $isWaBroadcast = false, $waGroupId = null)
    {
        $this->user = $user;
        $this->isWaBroadcast = $isWaBroadcast;
        $this->waGroupId = $waGroupId;
    }

    public function via(object $notifiable): array
    {
        return $this->isWaBroadcast ? [WhatsAppChannel::class] : ['database'];
    }

    private function getWhatsAppLink($name)
    {
        $noHp = $this->user->nomor_telepon;

        if (!$noHp) return null;

        $noHp = preg_replace('/[^0-9]/', '', $noHp);
        if (substr($noHp, 0, 1) === '0') {
            $noHp = '62' . substr($noHp, 1);
        }

        $text = urlencode("Happy Birthday {$name}! 🎉 Semoga panjang umur, sehat selalu, dan makin sukses ya!");
        return "https://wa.me/{$noHp}?text={$text}";
    }

    public function toWhatsApp($notifiable)
    {
        $yangUltah = $this->user->name;
        $waLink    = $this->getWhatsAppLink($yangUltah);

        $pesan = "🎂 *HARI INI ADA YANG ULANG TAHUN!* 🎂\n\n" .
                 "Hari ini adalah hari spesial untuk rekan kita: *{$yangUltah}*\n\n" .
                 "Jangan lupa berikan ucapan selamat dan doa terbaik untuk *{$yangUltah}* ya!";

        if ($waLink) {
            $pesan .= "\n\nKlik link ini buat kirim ucapan langsung:\n{$waLink}";
        } else {
            $pesan .= "\n\n(Nomor WhatsApp rekan tidak tersedia)";
        }

        return [
            'message' => $pesan,
            'target'  => $this->waGroupId ?? config('services.fonnte.group_id') 
        ];
    }

    public function toArray(object $notifiable): array
    {
        if ($notifiable->id === $this->user->id) {
            return [
                'id'      => $this->user->id, 
                'title'   => 'Selamat Ulang Tahun!',
                'message' => "Selamat ulang tahun {$this->user->name}, semoga hari ini menyenangkan!",
                'url'     => '#', 
                'icon'    => 'fas fa-birthday-cake',
                'color'   => 'text-pink-500',
            ];
        }

        $yangUltah = $this->user->name;

        return [
            'id'      => $this->user->id,
            'title'   => 'Hari Ini Ada yang Ulang Tahun!',
            'message' => "Hari ini {$yangUltah} ulang tahun. Jangan lupa kasih ucapan selamat!",
            'url'     => '#',
            'icon'    => 'fas fa-gift',
            'color'   => 'text-blue-500',
        ];
    }
}
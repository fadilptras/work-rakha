<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use App\Models\User;
use App\Notifications\Channels\LocalWhatsAppChannel;

class BirthdayNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public User $user; 
    public bool $isWaBroadcast;

    /**
     * @param \App\Models\User $birthdayPerson
     * @param bool $isWaBroadcast (Default: false)
     */
    public function __construct(User $birthdayPerson, bool $isWaBroadcast = false)
    {
        $this->user = $birthdayPerson;
        $this->isWaBroadcast = $isWaBroadcast;
    }

    public function via(object $notifiable): array
    {
        return $this->isWaBroadcast ? [LocalWhatsAppChannel::class] : ['database'];
    }

    public function toWhatsApp(object $notifiable): array
    {
        $yangUltah = $this->user->name;

        if ($this->isWaBroadcast) {
            $pesan = "Selamat Ulang Tahun! 🎉\n\n" .
                     "Selamat bertambah usia, *{$yangUltah}*.\n\n" .
                     "Semoga selalu diberikan kesehatan, kebahagiaan dan rezeki yang berlimpah.\n\n" .
                     "Selamat menikmati hari spesialmu. 🎂";

            return [
                'message' => $pesan,
                'target'  => 'group'
            ];
        }

        return [];
    }

    public function toArray(object $notifiable): array
    {
        // Jika yang menerima notifikasi adalah yang berulang tahun itu sendiri
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

        // Untuk rekan kerja lainnya
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

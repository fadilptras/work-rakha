<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use App\Models\User;
use App\Notifications\Channels\WhatsAppChannel;

/**
 * Notifikasi Ulang Tahun Karyawan
 * 
 * Sifat: Broadcast (Pengumuman ke Grup Perusahaan).
 * Menggunakan proteksi Target Group agar pesan tidak nyasar ke chat personal.
 */
class BirthdayNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @var \App\Models\User $user Karyawan yang berulang tahun
     */
    public User $user; 

    /**
     * @var bool $isWaBroadcast Menentukan apakah notifikasi dikirim via WA
     */
    public bool $isWaBroadcast;

    /**
     * @var string|null $waGroupId Target spesifik pengiriman grup
     */
    public ?string $waGroupId;

    /**
     * Konstruktor Notifikasi Ulang Tahun
     *
     * @param \App\Models\User $user
     * @param bool $isWaBroadcast (Default: false)
     * @param string|null $waGroupId
     */
    public function __construct(User $user, bool $isWaBroadcast = false, ?string $waGroupId = null)
    {
        $this->user = $user;
        $this->isWaBroadcast = $isWaBroadcast;
        $this->waGroupId = $waGroupId;
    }

    /**
     * Menentukan channel pengiriman
     */
    public function via(object $notifiable): array
    {
        return $this->isWaBroadcast ? [WhatsAppChannel::class] : ['database'];
    }

    /**
     * Membuat tautan WhatsApp instan untuk mengucapkan selamat
     */
    private function getWhatsAppLink(string $name): ?string
    {
        $noHp = $this->user->nomor_telepon;

        if (empty($noHp)) {
            return null;
        }

        // Normalisasi Format ke 62
        $noHp = preg_replace('/[^0-9]/', '', $noHp);
        if (str_starts_with($noHp, '0')) {
            $noHp = '62' . substr($noHp, 1);
        }

        $text = urlencode("Happy Birthday {$name}! 🎉 Semoga panjang umur, sehat selalu, dan makin sukses ya!");
        return "https://wa.me/{$noHp}?text={$text}";
    }

    /**
     * Format pengiriman via WhatsApp.
     */
    public function toWhatsApp(object $notifiable): array
    {
        $yangUltah = $this->user->name;
        $waLink    = $this->getWhatsAppLink($yangUltah);
        
        $targetGroupId = $this->waGroupId ?? config('services.fonnte.group_id');

        // PROTEKSI: Mencegah pengumuman ulang tahun nyasar ke WA pribadi jika Group ID kosong
        if (empty($targetGroupId)) {
            return [];
        }

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
            'target'  => $targetGroupId 
        ];
    }

    /**
     * Menyimpan data notifikasi ke dalam tabel 'notifications' (Database)
     */
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

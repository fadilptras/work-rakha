<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use App\Models\Holiday;
use App\Notifications\Channels\WhatsAppChannel;

/**
 * Notifikasi Hari Libur
 * 
 * Sifat: Broadcast (Pengumuman ke Grup Perusahaan).
 * Menggunakan proteksi Target Group agar pesan tidak nyasar ke chat personal.
 */
class HolidayNotification extends Notification implements ShouldQueue
{
    use Queueable;
    
    /**
     * @var \App\Models\Holiday $holiday Data Hari Libur
     */
    public Holiday $holiday;

    /**
     * @var bool $isWaBroadcast Menentukan apakah notifikasi dikirim via WA
     */
    public bool $isWaBroadcast;

    /**
     * @var string|null $waGroupId Target spesifik pengiriman grup
     */
    public ?string $waGroupId;
    
    /**
     * Konstruktor Notifikasi Hari Libur
     *
     * @param \App\Models\Holiday $holiday
     * @param bool $isWaBroadcast (Default: false)
     * @param string|null $waGroupId
     */
    public function __construct(Holiday $holiday, bool $isWaBroadcast = false, ?string $waGroupId = null)
    {
        $this->holiday = $holiday;
        $this->isWaBroadcast = $isWaBroadcast;
        $this->waGroupId = $waGroupId;
    }
    
    /**
     * Menentukan channel pengiriman
     */
    public function via(object $notifiable): array
    {
        // Jika flag isWaBroadcast bernilai true, aktifkan WhatsApp. Jika false, khusus database.
        return $this->isWaBroadcast ? [WhatsAppChannel::class] : ['database'];
    }

    /**
     * Format pengiriman via WhatsApp.
     */
    public function toWhatsApp(object $notifiable): array
    {
        $namaLibur = $this->holiday->keterangan ?? 'Hari Libur Nasional';
        
        // Ambil dari parameter, jika tidak ada fallback ke config services (.env)
        $targetGroupId = $this->waGroupId ?? config('services.fonnte.group_id');
        
        // PROTEKSI: Mencegah pesan libur nyasar ke WA pribadi jika Group ID kosong
        if (empty($targetGroupId)) {
            return [];
        }

        return [
            'message' => "🏖️ *INFORMASI HARI LIBUR* 🏖️\n\n" .
                         "Mengingatkan bahwa hari ini kantor libur dalam rangka: *{$namaLibur}*\n" .
                         "Selamat beristirahat dan menikmati waktu luang! Sampai jumpa di hari kerja berikutnya.\n\n",
            'target'  => $targetGroupId
        ];
    }

    /**
     * Menyimpan data notifikasi ke dalam tabel 'notifications' (Database)
     */
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

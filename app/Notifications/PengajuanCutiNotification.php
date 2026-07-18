<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use App\Models\Cuti;
use App\Notifications\Channels\WhatsAppChannel;
use Carbon\Carbon;

/**
 * Notifikasi Pengajuan Cuti
 * 
 * Sifat: Transaksional (Japri / Direct Message).
 * Pesan akan dikirim secara spesifik kepada pemohon atau approver.
 */
class PengajuanCutiNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @var \App\Models\Cuti $cuti Data pengajuan cuti
     */
    public Cuti $cuti;

    /**
     * @var string $tipe Tipe notifikasi ('baru', 'disetujui', 'ditolak', 'dibatalkan')
     */
    public string $tipe;

    /**
     * Konstruktor Notifikasi Cuti
     *
     * @param \App\Models\Cuti $cuti
     * @param string $tipe
     */
    public function __construct(Cuti $cuti, string $tipe = 'baru')
    {
        $this->cuti = $cuti;
        $this->tipe = $tipe;
    }

    /**
     * Menentukan channel pengiriman
     */
    public function via(object $notifiable): array
    {
        return [
            'database', 
            WhatsAppChannel::class
        ];
    }

    /**
     * Format notifikasi untuk pengiriman via WhatsApp.
     * Karena tidak mendefinisikan 'target', pesan otomatis dikirim secara personal.
     */
    public function toWhatsApp(object $notifiable): array
    {
        $pemohon = $this->cuti->user->name ?? 'Karyawan';
        $tanggal = Carbon::parse($this->cuti->tanggal_mulai)->translatedFormat('d F Y');
        $link = route('cuti.show', $this->cuti->id);

        switch ($this->tipe) {
            case 'disetujui':
                $header = "✅ *CUTI DISETUJUI*";
                $pesan = "Halo {$notifiable->name}, pengajuan cuti Anda untuk tanggal *{$tanggal}* telah DISETUJUI sepenuhnya oleh semua pihak.";
                break;
            case 'ditolak':
                $header = "❌ *CUTI DITOLAK*";
                $pesan = "Halo {$notifiable->name}, pengajuan cuti Anda untuk tanggal *{$tanggal}* DITOLAK.";
                break;
            case 'dibatalkan':
                $header = "⚠️ *CUTI DIBATALKAN*";
                $pesan = "Halo {$notifiable->name}, pengajuan cuti atas nama *{$pemohon}* telah dibatalkan.";
                break;
            case 'baru':
            default:
                // Biasanya dikirim ke Approver (HRD, Atasan) untuk direview
                $header = "🆕 *PENGAJUAN CUTI*";
                $pesan = "Halo {$notifiable->name}, ada pengajuan cuti yang memerlukan persetujuan Anda.\n\n*Pemohon:* {$pemohon}\n*Tanggal:* {$tanggal}\n\nMohon segera diperiksa melalui sistem.";
                break;
        }

        return [
            'message' => "{$header}\n\n{$pesan}\n\n🔗 *Link Detail:* {$link}"
        ];
    }

    /**
     * Menyimpan data notifikasi ke dalam tabel 'notifications' (Database)
     */
    public function toArray(object $notifiable): array
    {
        $pemohon = $this->cuti->user->name ?? 'Karyawan';
        $tanggal = Carbon::parse($this->cuti->tanggal_mulai)->translatedFormat('d F Y');

        switch ($this->tipe) {
            case 'disetujui':
                return [
                    'id' => $this->cuti->id,
                    'title' => 'Cuti Disetujui',
                    'message' => "Cuti Anda pada tanggal $tanggal telah disetujui.",
                    'icon' => 'fas fa-check-circle',
                    'color' => 'text-green-600',
                    'url' => route('cuti.show', $this->cuti->id),
                ];
            case 'ditolak':
                return [
                    'id' => $this->cuti->id,
                    'title' => 'Cuti Ditolak',
                    'message' => "Cuti Anda pada tanggal $tanggal ditolak.",
                    'icon' => 'fas fa-times-circle',
                    'color' => 'text-red-600',
                    'url' => route('cuti.show', $this->cuti->id),
                ];
            case 'dibatalkan':
                return [
                    'id' => $this->cuti->id,
                    'title' => 'Cuti Dibatalkan',
                    'message' => "$pemohon membatalkan pengajuan cuti.",
                    'icon' => 'fas fa-ban',
                    'color' => 'text-gray-500',
                    'url' => route('cuti.show', $this->cuti->id),
                ];
            default:
                return [
                    'id' => $this->cuti->id,
                    'title' => 'Perlu Persetujuan',
                    'message' => "$pemohon mengajukan cuti baru tanggal $tanggal.",
                    'icon' => 'fas fa-file-invoice',
                    'color' => 'text-blue-600',
                    'url' => route('cuti.show', $this->cuti->id)
                ];
        }
    }
}

<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use App\Models\Cuti;
use App\Notifications\Channels\LocalWhatsAppChannel;
use Carbon\Carbon;

class PengajuanCutiNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public Cuti $cuti;
    public string $tipe;

    public function __construct(Cuti $cuti, string $tipe = 'baru')
    {
        $this->cuti = $cuti;
        $this->tipe = $tipe;
    }

    public function via(object $notifiable): array
    {
        return [
            'database', 
            LocalWhatsAppChannel::class
        ];
    }

    public function toWhatsApp(object $notifiable): array
    {
        $pemohon = $this->cuti->user->name ?? 'Karyawan';
        $tanggal = Carbon::parse($this->cuti->tanggal_mulai)->translatedFormat('d F Y');
        $link = $notifiable->role === 'admin' 
            ? route('admin.cuti.show', $this->cuti->id) 
            : route('cuti.show', $this->cuti->id);

        switch ($this->tipe) {
            case 'disetujui_parsial':
                return []; // In-app notification only
            case 'disetujui':
            case 'disetujui_final':
                $header = "Cuti Disetujui Sepenuhnya! ✅";
                $pesan = "Halo {$notifiable->name}, pengajuan cuti Anda untuk tanggal *{$tanggal}* telah DISETUJUI sepenuhnya oleh semua pihak.";
                break;
            case 'ditolak':
                $header = "Cuti Ditolak ❌";
                $pesan = "Halo {$notifiable->name}, pengajuan cuti Anda untuk tanggal *{$tanggal}* DITOLAK.";
                break;
            case 'dibatalkan':
                $header = "Cuti Dibatalkan ⚠️";
                $pesan = "Halo {$notifiable->name}, pengajuan cuti atas nama *{$pemohon}* telah dibatalkan.";
                break;
            case 'baru':
            default:
                $header = "Pengajuan Cuti Baru! 🆕";
                $pesan = "Halo {$notifiable->name}, ada pengajuan cuti yang memerlukan persetujuan Anda.\n\n*Pemohon:* {$pemohon}\n*Tanggal:* {$tanggal}\n\nMohon segera diperiksa melalui sistem.";
                break;
        }

        return [
            'message' => "{$header}\n\n{$pesan}\n\n🔗 *Link Detail:* {$link}"
        ];
    }

    public function toArray(object $notifiable): array
    {
        $pemohon = $this->cuti->user->name ?? 'Karyawan';
        $tanggal = Carbon::parse($this->cuti->tanggal_mulai)->translatedFormat('d F Y');

        switch ($this->tipe) {
            case 'disetujui_parsial':
                return [
                    'id' => $this->cuti->id,
                    'title' => 'Cuti Diproses',
                    'message' => "Pengajuan cuti Anda diteruskan ke approver berikutnya.",
                    'icon' => 'fas fa-check-double',
                    'color' => 'text-green-500',
                    'url' => $notifiable->role === 'admin' 
                        ? route('admin.cuti.show', $this->cuti->id) 
                        : route('cuti.show', $this->cuti->id),
                ];
            case 'disetujui':
            case 'disetujui_final':
                return [
                    'id' => $this->cuti->id,
                    'title' => 'Cuti Disetujui',
                    'message' => "Cuti Anda pada tanggal $tanggal telah disetujui.",
                    'icon' => 'fas fa-check-circle',
                    'color' => 'text-green-600',
                    'url' => $notifiable->role === 'admin' 
                        ? route('admin.cuti.show', $this->cuti->id) 
                        : route('cuti.show', $this->cuti->id),
                ];
            case 'ditolak':
                return [
                    'id' => $this->cuti->id,
                    'title' => 'Cuti Ditolak',
                    'message' => "Cuti Anda pada tanggal $tanggal ditolak.",
                    'icon' => 'fas fa-times-circle',
                    'color' => 'text-red-600',
                    'url' => $notifiable->role === 'admin' 
                        ? route('admin.cuti.show', $this->cuti->id) 
                        : route('cuti.show', $this->cuti->id),
                ];
            case 'dibatalkan':
                return [
                    'id' => $this->cuti->id,
                    'title' => 'Cuti Dibatalkan',
                    'message' => "$pemohon membatalkan pengajuan cuti.",
                    'icon' => 'fas fa-ban',
                    'color' => 'text-gray-500',
                    'url' => $notifiable->role === 'admin' 
                        ? route('admin.cuti.show', $this->cuti->id) 
                        : route('cuti.show', $this->cuti->id),
                ];
            default:
                return [
                    'id' => $this->cuti->id,
                    'title' => 'Perlu Persetujuan',
                    'message' => "$pemohon mengajukan cuti baru tanggal $tanggal.",
                    'icon' => 'fas fa-file-invoice',
                    'color' => 'text-blue-600',
                    'url' => $notifiable->role === 'admin' 
                        ? route('admin.cuti.show', $this->cuti->id) 
                        : route('cuti.show', $this->cuti->id)
                ];
        }
    }
}

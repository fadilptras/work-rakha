<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use App\Models\PengajuanBarang;
use App\Notifications\Channels\WhatsAppChannel;

class PengajuanBarangNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public PengajuanBarang $pengajuanBarang;
    public string $tipe;

    public function __construct(PengajuanBarang $pengajuanBarang, string $tipe = 'baru')
    {
        $this->pengajuanBarang = $pengajuanBarang;
        $this->tipe = $tipe;
    }

    public function via(object $notifiable): array
    {
        return ['database', WhatsAppChannel::class];
    }

    public function toWhatsApp(object $notifiable): array
    {
        $judul = $this->pengajuanBarang->judul_pengajuan;
        $pemohon = $this->pengajuanBarang->user->name;
        $link = $notifiable->role === 'admin' 
            ? route('admin.pengajuan_barang.show', $this->pengajuanBarang->id) 
            : route('pengajuan_barang.show', $this->pengajuanBarang->id);

        switch ($this->tipe) {
            case 'disetujui_parsial': // Untuk approver tahap 1-3
                return []; // In-app notification only
            case 'disetujui_final':
                $header = "Barang: Disetujui Sepenuhnya 📦";
                $pesan = "Pengajuan barang *'{$judul}'* telah disetujui sepenuhnya. Barang siap diproses/diambil.";
                break;
            case 'ditolak':
                $header = "Pengajuan Barang Ditolak ❌";
                $pesan = "Pengajuan barang *'{$judul}'* telah ditolak.";
                break;
            case 'baru':
            default:
                $header = "Pengajuan Barang Baru 🆕";
                $pesan = "Ada pengajuan barang baru dari *{$pemohon}*.\nJudul: {$judul}\n\nMohon segera direview.";
                break;
        }

        return ['message' => "{$header}\n\nHalo {$notifiable->name},\n{$pesan}\n\n🔗 Link: {$link}"];
    }

    public function toArray(object $notifiable): array
    {
        $title = '';
        $message = '';
        $icon = 'fas fa-box';
        $color = 'text-blue-600';
        $pemohon = $this->pengajuanBarang->user?->name ?? 'Sistem'; 
        $judulPengajuan = \Illuminate\Support\Str::limit($this->pengajuanBarang->judul_pengajuan, 30);

        switch ($this->tipe) {
            case 'disetujui_parsial':
                $title = 'Pengajuan Barang Diproses';
                $message = "Pengajuan '$judulPengajuan' Anda telah disetujui dan diteruskan ke approver berikutnya.";
                $icon = 'fas fa-check-double';
                $color = 'text-green-500';
                break;
            case 'lanjut_final':
                $title = 'Perlu Finalisasi Barang';
                $message = "Pengajuan '$judulPengajuan' perlu direview final.";
                $icon = 'fas fa-bell';
                $color = 'text-yellow-600';
                break;
            case 'disetujui_final':
                $title = 'Pengajuan Barang Selesai';
                $message = "Pengajuan barang '$judulPengajuan' Anda telah disetujui sepenuhnya.";
                $icon = 'fas fa-check-double';
                $color = 'text-green-700';
                break;
            case 'ditolak':
                $title = 'Pengajuan Barang Ditolak';
                $message = "Mohon maaf, pengajuan barang '$judulPengajuan' Anda ditolak.";
                $icon = 'fas fa-times-circle';
                $color = 'text-red-600';
                break;
            case 'baru':
            default:
                $title = 'Pengajuan Barang Baru';
                $message = "$pemohon mengajukan barang baru: '$judulPengajuan'. Mohon direview.";
                break;
        }

        return [
            'id' => $this->pengajuanBarang->id,
            'title' => $title,
            'message' => $message,
            'url' => $notifiable->role === 'admin' 
                ? route('admin.pengajuan_barang.show', $this->pengajuanBarang->id) 
                : route('pengajuan_barang.show', $this->pengajuanBarang->id),
            'icon' => $icon,
            'color' => $color,
        ];
    }
}

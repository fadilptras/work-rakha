<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use App\Models\PengajuanDana;
use App\Notifications\Channels\LocalWhatsAppChannel;

class PengajuanDanaNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public PengajuanDana $pengajuanDana;
    public string $tipe;
    public ?string $approverName;

    public function __construct(PengajuanDana $pengajuanDana, string $tipe = 'baru', ?string $approverName = null)
    {
        $this->pengajuanDana = $pengajuanDana;
        $this->tipe = $tipe;
        $this->approverName = $approverName;
    }

    public function via(object $notifiable): array
    {
        return ['database', LocalWhatsAppChannel::class];
    }

    public function toWhatsApp(object $notifiable): array
    {
        $judul = $this->pengajuanDana->judul_pengajuan;
        $pemohon = $this->pengajuanDana->user->name;
        $nominal = "Rp " . number_format($this->pengajuanDana->total_dana, 0, ',', '.');
        $link = $notifiable->role === 'admin' 
            ? route('admin.pengajuan_dana.show', $this->pengajuanDana->id) 
            : route('pengajuan_dana.show', $this->pengajuanDana->id);

        switch ($this->tipe) {
            case 'disetujui_parsial':
                $header = "Update Status Pengajuan Dana! 🔄";
                $approver = $this->approverName ?? 'Approver';
                $pesan = "Pengajuan dana *'{$judul}'* senilai {$nominal} Anda telah disetujui oleh *{$approver}* dan sedang diteruskan ke approver berikutnya.";
                break;
            case 'disetujui_final':
            case 'bukti_transfer':
                $header = "Pengajuan Dana Selesai! ✅";
                $pesan = "Kabar baik! Pengajuan dana *'{$judul}'* senilai {$nominal} Anda telah disetujui sepenuhnya dan selesai diproses.";
                break;
            case 'selesai_approver':
                $header = "Pengajuan Dana Selesai! ✅";
                $pesan = "Pengajuan dana *'{$judul}'* senilai {$nominal} oleh *{$pemohon}* telah disetujui sepenuhnya dan selesai diproses.";
                break;
            case 'ditolak':
                $header = "Pengajuan Dana Ditolak! ❌";
                $pesan = "Mohon maaf, pengajuan dana *'{$judul}'* senilai {$nominal} telah ditolak.";
                break;
            case 'dibatalkan':
                $header = "Pengajuan Dana Dibatalkan! ⚠️";
                $pesan = "Pengajuan dana *'{$judul}'* oleh {$pemohon} telah dibatalkan.";
                break;
            case 'baru':
            default:
                $header = "Pengajuan Dana Baru! 🆕";
                $pesan = "Ada pengajuan dana baru dari *{$pemohon}*.\nJudul: {$judul}\nNominal: {$nominal}\n\nMohon segera diperiksa.";
                break;
        }

        return ['message' => "{$header}\n\nHalo {$notifiable->name},\n{$pesan}\n\n🔗 Link: {$link}"];
    }

    public function toArray(object $notifiable): array
    {
        $title = '';
        $message = '';
        $icon = 'fas fa-coins';
        $color = 'text-blue-600';
        $pemohon = $this->pengajuanDana->user->name;
        $judulPengajuan = \Illuminate\Support\Str::limit($this->pengajuanDana->judul_pengajuan, 30);

        switch ($this->tipe) {
            case 'disetujui_parsial':
                $title = 'Pengajuan Dana Diproses';
                $approver = $this->approverName ?? 'Approver';
                $message = "Pengajuan '$judulPengajuan' Anda telah disetujui oleh $approver dan diteruskan ke approver berikutnya.";
                $icon = 'fas fa-check-double';
                $color = 'text-green-500';
                break;
            case 'disetujui_final':
            case 'bukti_transfer':
                $title = 'Pengajuan Dana Disetujui';
                $message = "Kabar baik! Pengajuan dana '$judulPengajuan' Anda telah disetujui sepenuhnya.";
                $icon = 'fas fa-check-circle';
                $color = 'text-green-600';
                break;
            case 'selesai_approver':
                $title = 'Pengajuan Dana Selesai';
                $message = "Pengajuan dana '$judulPengajuan' oleh $pemohon telah selesai diproses.";
                $icon = 'fas fa-check-circle';
                $color = 'text-green-600';
                break;
            case 'ditolak':
                $title = 'Pengajuan Dana Ditolak';
                $message = "Mohon maaf, pengajuan dana '$judulPengajuan' Anda ditolak.";
                $icon = 'fas fa-times-circle';
                $color = 'text-red-600';
                break;
            case 'dibatalkan':
                $title = 'Pengajuan Dibatalkan';
                $message = "Pengajuan dana '$judulPengajuan' oleh $pemohon telah dibatalkan.";
                $icon = 'fas fa-ban'; 
                $color = 'text-slate-500'; 
                break;
            case 'baru':
            default:
                $title = 'Pengajuan Dana Baru';
                $message = "$pemohon mengajukan dana baru: '$judulPengajuan'. Mohon direview.";
                break;
        }

        return [
            'id' => $this->pengajuanDana->id,
            'title' => $title,
            'message' => $message,
            'url' => $notifiable->role === 'admin' 
                ? route('admin.pengajuan_dana.show', $this->pengajuanDana->id) 
                : route('pengajuan_dana.show', $this->pengajuanDana->id),
            'icon' => $icon,
            'color' => $color,
        ];
    }
}

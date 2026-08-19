<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use App\Models\PengajuanBarang;
use App\Notifications\Channels\LocalWhatsAppChannel;

class PengajuanBarangNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public PengajuanBarang $pengajuanBarang;
    public string $tipe;
    public array $extraData;

    public function __construct(PengajuanBarang $pengajuanBarang, string $tipe = 'baru', array $extraData = [])
    {
        $this->pengajuanBarang = $pengajuanBarang;
        $this->tipe = $tipe;
        $this->extraData = $extraData;
    }

    public function via(object $notifiable): array
    {
        return ['database', LocalWhatsAppChannel::class];
    }

    public function toWhatsApp(object $notifiable): array
    {
        $judul = $this->pengajuanBarang->judul_pengajuan;
        $pemohon = $this->pengajuanBarang->user->name ?? '-';
        $nomorSurat = $this->pengajuanBarang->nomor_surat ?? '-';
        $divisi = $this->pengajuanBarang->divisi ?? '-';
        $tanggal = $this->pengajuanBarang->created_at ? $this->pengajuanBarang->created_at->locale('id')->isoFormat('D MMMM YYYY') : '-';
        
        $link = $notifiable->role === 'admin' 
            ? route('admin.pengajuan_barang.show', $this->pengajuanBarang->id) 
            : route('pengajuan_barang.show', $this->pengajuanBarang->id);

        $detailUtama = "📦 *DETAIL PENGAJUAN*\n*No. Surat:* {$nomorSurat}\n*Judul:* {$judul}\n*Pemohon:* {$pemohon} ({$divisi})\n*Tanggal:* {$tanggal}";

        switch ($this->tipe) {
            case 'update_pelacakan':
                $statusTrk = $this->extraData['status'] ?? 'Update Baru';
                $catatanTrk = $this->extraData['catatan'] ?? '-';
                $header = "🚚 *UPDATE PELACAKAN BARANG*";
                $pesan = "Terdapat pembaruan status pengiriman untuk pengajuan barang Anda.\n\n{$detailUtama}\n\n📍 *STATUS TERKINI*\n*Status:* {$statusTrk}\n*Catatan:* {$catatanTrk}";
                break;
            case 'disetujui_semua':
                $header = "✅ *PERSETUJUAN SELESAI*";
                $pesan = "Pengajuan barang berikut telah disetujui sepenuhnya oleh semua tingkatan Approver dan siap untuk diproses lebih lanjut oleh Admin/Purchasing.\n\n{$detailUtama}";
                break;
            case 'selesai_pengajuan':
                $header = "🏁 *PENGAJUAN SELESAI*";
                $pesan = "Proses pengajuan barang berikut telah dinyatakan *SELESAI* sepenuhnya. Barang telah diproses atau diserah-terimakan.\n\n{$detailUtama}";
                break;
            case 'disetujui_parsial': // Untuk approver tahap 1-3
                return []; // In-app notification only
            case 'disetujui_final':
                $header = "📦 *BARANG DISETUJUI*";
                $pesan = "Pengajuan barang Anda telah disetujui sepenuhnya dan siap diproses.\n\n{$detailUtama}";
                break;
            case 'ditolak':
                $header = "❌ *PENGAJUAN DITOLAK*";
                $pesan = "Mohon maaf, pengajuan barang Anda berikut ini telah ditolak oleh Approver.\n\n{$detailUtama}";
                break;
            case 'baru':
            default:
                $header = "🆕 *PENGAJUAN BARANG BARU*";
                $pesan = "Ada pengajuan barang baru yang membutuhkan review persetujuan Anda.\n\n{$detailUtama}\n\nMohon segera diperiksa pada sistem.";
                break;
        }

        return ['message' => "{$header}\n\nHalo *{$notifiable->name}*,\n{$pesan}\n\n🔗 *Lihat Detail:* {$link}"];
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
            case 'update_pelacakan':
                $statusTrk = $this->extraData['status'] ?? 'Update Baru';
                $title = 'Update Pelacakan Barang';
                $message = "Status pengajuan '$judulPengajuan' diupdate: $statusTrk.";
                $icon = 'fas fa-truck-fast';
                $color = 'text-amber-500';
                break;
            case 'disetujui_semua':
                $title = 'Persetujuan Barang Selesai';
                $message = "Pengajuan '$judulPengajuan' dari $pemohon telah disetujui sepenuhnya.";
                $icon = 'fas fa-check-double';
                $color = 'text-emerald-500';
                break;
            case 'selesai_pengajuan':
                $title = 'Pengajuan Selesai';
                $message = "Pengajuan barang '$judulPengajuan' telah selesai sepenuhnya.";
                $icon = 'fas fa-flag-checkered';
                $color = 'text-sky-600';
                break;
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

<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use App\Models\PengajuanBarang;
use App\Notifications\Channels\WhatsAppChannel;

/**
 * Notifikasi Pengajuan Barang
 * 
 * Sifat: Transaksional (Japri / Direct Message).
 * Pesan akan dikirim secara spesifik kepada pemohon atau approver terkait.
 */
class PengajuanBarangNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @var \App\Models\PengajuanBarang $pengajuanBarang Data pengajuan barang
     */
    public PengajuanBarang $pengajuanBarang;

    /**
     * @var string $tipe Tipe status/tahap pengajuan
     */
    public string $tipe;

    /**
     * Konstruktor Notifikasi Pengajuan Barang
     *
     * @param \App\Models\PengajuanBarang $pengajuanBarang
     * @param string $tipe Default: 'baru'
     */
    public function __construct(PengajuanBarang $pengajuanBarang, string $tipe = 'baru')
    {
        $this->pengajuanBarang = $pengajuanBarang;
        $this->tipe = $tipe;
    }

    /**
     * Menentukan channel pengiriman
     */
    public function via(object $notifiable): array
    {
        return ['database', WhatsAppChannel::class];
    }

    /**
     * Format pengiriman via WhatsApp.
     * Karena tidak mendefinisikan 'target', pesan otomatis dikirim secara personal.
     */
    public function toWhatsApp(object $notifiable): array
    {
        $judul = $this->pengajuanBarang->judul_pengajuan;
        $pemohon = $this->pengajuanBarang->user->name;
        $link = $notifiable->role === 'admin' 
            ? route('admin.pengajuan_barang.show', $this->pengajuanBarang->id) 
            : route('pengajuan_barang.show', $this->pengajuanBarang->id);

        switch ($this->tipe) {
            case 'disetujui_atasan':
                $header = "✅ *BARANG: DISETUJUI ATASAN*";
                $pesan = "Pengajuan barang *'{$judul}'* telah disetujui Atasan dan diteruskan ke Gudang.";
                break;
            case 'disetujui_gudang':
                $header = "📦 *BARANG: DISETUJUI GUDANG*";
                $pesan = "Pengajuan barang *'{$judul}'* telah disetujui oleh Gudang/Finance. Barang siap diproses/diambil.";
                break;
            case 'ditolak':
                $header = "❌ *PENGAJUAN BARANG DITOLAK*";
                $pesan = "Pengajuan barang *'{$judul}'* telah ditolak.";
                break;
            case 'baru':
            default:
                $header = "🆕 *PENGAJUAN BARANG BARU*";
                $pesan = "Ada pengajuan barang baru dari *{$pemohon}*.\nJudul: {$judul}\n\nMohon segera direview.";
                break;
        }

        return ['message' => "{$header}\n\nHalo {$notifiable->name},\n{$pesan}\n\n🔗 Link: {$link}"];
    }

    /**
     * Menyimpan data notifikasi ke dalam tabel 'notifications' (Database)
     */
    public function toArray(object $notifiable): array
    {
        $title = '';
        $message = '';
        $icon = 'fas fa-box';
        $color = 'text-blue-600';
        $pemohon = $this->pengajuanBarang->user?->name ?? 'Sistem'; 
        $judulPengajuan = \Illuminate\Support\Str::limit($this->pengajuanBarang->judul_pengajuan, 30);

        switch ($this->tipe) {
            case 'disetujui_atasan':
                $title = 'Pengajuan Barang Diproses';
                $message = "Pengajuan '$judulPengajuan' Anda telah disetujui atasan dan diteruskan ke Gudang.";
                $icon = 'fas fa-check-double';
                $color = 'text-green-500';
                break;
            case 'disetujui_gudang':
                $title = 'Pengajuan Barang Disetujui';
                $message = "Kabar baik! Pengajuan barang '$judulPengajuan' Anda telah disetujui oleh Gudang.";
                $icon = 'fas fa-check-circle';
                $color = 'text-green-600';
                break;
            case 'ditolak':
                $title = 'Pengajuan Barang Ditolak';
                $message = "Mohon maaf, pengajuan barang '$judulPengajuan' Anda ditolak.";
                $icon = 'fas fa-times-circle';
                $color = 'text-red-600';
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

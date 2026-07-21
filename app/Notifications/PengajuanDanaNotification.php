<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use App\Models\PengajuanDana;
use App\Notifications\Channels\WhatsAppChannel;

/**
 * Notifikasi Pengajuan Dana
 * 
 * Sifat: Transaksional (Japri / Direct Message).
 * Pesan akan dikirim secara spesifik kepada pemohon atau approver terkait (Finance/Atasan).
 */
class PengajuanDanaNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @var \App\Models\PengajuanDana $pengajuanDana Data pengajuan dana
     */
    public PengajuanDana $pengajuanDana;

    /**
     * @var string $tipe Tipe status/tahap pengajuan
     */
    public string $tipe;

    /**
     * Konstruktor Notifikasi Pengajuan Dana
     *
     * @param \App\Models\PengajuanDana $pengajuanDana
     * @param string $tipe Konteks notifikasi
     */
    public function __construct(PengajuanDana $pengajuanDana, string $tipe = 'baru')
    {
        $this->pengajuanDana = $pengajuanDana;
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
        $judul = $this->pengajuanDana->judul_pengajuan;
        $pemohon = $this->pengajuanDana->user->name;
        $nominal = "Rp " . number_format($this->pengajuanDana->total_dana, 0, ',', '.');
        $link = $notifiable->role === 'admin' 
            ? route('admin.pengajuan_dana.show', $this->pengajuanDana->id) 
            : route('pengajuan_dana.show', $this->pengajuanDana->id);

        switch ($this->tipe) {
            case 'disetujui_atasan':
            case 'disetujui_finance':
            case 'disetujui_final':
                $header = "✅ *PENGAJUAN DANA DISETUJUI*";
                $pesan = "Pengajuan dana *'{$judul}'* senilai {$nominal} telah disetujui dan sedang diproses ke tahap selanjutnya.";
                break;
            case 'ditolak':
                $header = "❌ *PENGAJUAN DANA DITOLAK*";
                $pesan = "Mohon maaf, pengajuan dana *'{$judul}'* senilai {$nominal} telah ditolak.";
                break;
            case 'bukti_transfer':
                $header = "💸 *DANA TELAH DITRANSFER*";
                $pesan = "Dana untuk *'{$judul}'* senilai {$nominal} telah berhasil ditransfer. Silakan cek rekening dan lampirkan bukti jika diminta.";
                break;
            case 'dibatalkan':
                $header = "⚠️ *PENGAJUAN DANA DIBATALKAN*";
                $pesan = "Pengajuan dana *'{$judul}'* oleh {$pemohon} telah dibatalkan.";
                break;
            case 'baru':
            default:
                $header = "🆕 *PENGAJUAN DANA BARU*";
                $pesan = "Ada pengajuan dana baru dari *{$pemohon}*.\nJudul: {$judul}\nNominal: {$nominal}\n\nMohon segera diperiksa.";
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
        $icon = 'fas fa-coins';
        $color = 'text-blue-600';
        $pemohon = $this->pengajuanDana->user->name;
        $judulPengajuan = \Illuminate\Support\Str::limit($this->pengajuanDana->judul_pengajuan, 30);

        switch ($this->tipe) {
            case 'disetujui_atasan':
                $title = 'Pengajuan Dana Diproses';
                $message = "Pengajuan '$judulPengajuan' Anda telah disetujui atasan dan diteruskan ke Finance.";
                $icon = 'fas fa-check-double';
                $color = 'text-green-500';
                break;
            case 'disetujui_finance':
            case 'disetujui_final':
                $title = 'Pengajuan Dana Disetujui';
                $message = "Kabar baik! Pengajuan dana '$judulPengajuan' Anda telah disetujui sepenuhnya.";
                $icon = 'fas fa-check-circle';
                $color = 'text-green-600';
                break;
            case 'ditolak':
                $title = 'Pengajuan Dana Ditolak';
                $message = "Mohon maaf, pengajuan dana '$judulPengajuan' Anda ditolak.";
                $icon = 'fas fa-times-circle';
                $color = 'text-red-600';
                break;
            case 'bukti_transfer':
                $title = 'Dana Telah Ditransfer';
                $message = "Dana untuk pengajuan '$judulPengajuan' telah ditransfer. Silakan cek rekening Anda.";
                $icon = 'fas fa-receipt';
                $color = 'text-indigo-600';
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

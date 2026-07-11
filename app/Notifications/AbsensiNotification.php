<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use App\Models\Absensi;
use App\Models\Lembur;
use App\Notifications\Channels\WhatsAppChannel;
use Carbon\Carbon;

class AbsensiNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $data; 
    public $tipe; 

    public function __construct($data, string $tipe)
    {
        $this->data = $data;
        $this->tipe = $tipe;
    }

    public function via($notifiable)
    {
        return ['database', WhatsAppChannel::class];
    }

    public function toWhatsApp($notifiable)
    {
        $nama = $notifiable->name;
        $waktu = now()->translatedFormat('d F Y H:i');
        
        // Ambil keterangan jika ada
        $keterangan = $this->data->keterangan ? $this->data->keterangan : '-';
        
        // Pesan disesuaikan dengan gaya bahasa pengumuman grup
        switch ($this->tipe) {
            case 'masuk':
                $pesan = "📢 *INFO ABSENSI*\n\n✅ Karyawan a.n. *{$nama}* telah melakukan *Absen Masuk* pada {$waktu}.\n\nSelamat bekerja dan semangat! 💪";
                break;
            case 'keluar':
                $pesan = "📢 *INFO ABSENSI*\n\n🚪 Karyawan a.n. *{$nama}* telah melakukan *Absen Keluar* pada {$waktu}.\n\nTerima kasih atas kerja kerasnya hari ini!";
                break;
            case 'lembur_masuk':
                $pesan = "📢 *INFO LEMBUR*\n\n💼 Karyawan a.n. *{$nama}* telah memulai *Lembur* pada {$waktu}.";
                break;
            case 'lembur_keluar':
                $pesan = "📢 *INFO LEMBUR*\n\n🏠 Karyawan a.n. *{$nama}* telah menyelesaikan *Lembur* pada {$waktu}.";
                break;
            case 'izin':
                $pesan = "📢 *INFO ABSENSI*\n\nℹ️ Karyawan a.n. *{$nama}* mengajukan *Izin* pada {$waktu}.\n\n📝 *Keterangan:* {$keterangan}";
                break;
            case 'sakit':
                $pesan = "📢 *INFO ABSENSI*\n\n⚕️ Karyawan a.n. *{$nama}* mengajukan *Sakit* pada {$waktu}.\n\n📝 *Keterangan:* {$keterangan}\n\nSemoga lekas sembuh!";
                break;
            default:
                $pesan = "📢 *INFO ABSENSI*\n\nAbsensi a.n. *{$nama}* berhasil dicatat pada {$waktu}.";
        }

        return [
            'message' => $pesan,
            'target'  => '120363242834102956@g.us', // ID Grup WhatsApp Fonnte
        ];
    }

    public function toArray($notifiable)
    {
        $pesanPendek = '';
        $icon = '';
        $color = '';

        switch ($this->tipe) {
            case 'masuk':
                $pesanPendek = "Absen Masuk berhasil dicatat.";
                $icon = 'fas fa-sign-in-alt';
                $color = 'text-green-500';
                break;
            case 'keluar':
                $pesanPendek = "Absen Keluar berhasil dicatat.";
                $icon = 'fas fa-sign-out-alt';
                $color = 'text-gray-500';
                break;
            case 'lembur_masuk':
                $pesanPendek = "Mulai Lembur dicatat.";
                $icon = 'fas fa-briefcase';
                $color = 'text-orange-500';
                break;
            case 'lembur_keluar':
                $pesanPendek = "Selesai Lembur dicatat.";
                $icon = 'fas fa-home';
                $color = 'text-blue-500';
                break;
            case 'izin':
                $pesanPendek = "Status Izin berhasil dicatat.";
                $icon = 'fas fa-envelope-open-text';
                $color = 'text-yellow-500';
                break;
            case 'sakit':
                $pesanPendek = "Status Sakit berhasil dicatat.";
                $icon = 'fas fa-notes-medical';
                $color = 'text-red-500';
                break;
        }

        return [
            'id'      => $this->data->id, 
            'title'   => 'Absensi Tersimpan',
            'message' => $pesanPendek,
            'url'     => route('absen'), 
            'icon'    => $icon,
            'color'   => $color,
        ];
    }
}
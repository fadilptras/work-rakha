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

    public object $data; 
    public string $tipe; 

    /**
     * @param object $data Data Absensi / Lembur
     * @param string $tipe ('masuk', 'keluar', 'izin', 'sakit', dll)
     */
    public function __construct(object $data, string $tipe)
    {
        $this->data = $data;
        $this->tipe = $tipe;
    }

    public function via(object $notifiable): array
    {
        return ['database', WhatsAppChannel::class];
    }

    public function toWhatsApp(object $notifiable): array
    {
        // Masuk/Keluar dihandle oleh rekap harian, tidak dikirim langsung.
        if (in_array($this->tipe, ['masuk', 'keluar'])) {
            return [];
        }

        $nama = $notifiable->name;
        $waktu = now()->translatedFormat('d F Y H:i');
        $keterangan = !empty($this->data->keterangan) ? $this->data->keterangan : '-';
        
        switch ($this->tipe) {
            case 'lembur_masuk':
                $pesan = "[INFO LEMBUR]\n\nKaryawan a.n. *{$nama}* telah memulai *Lembur* pada {$waktu}.";
                break;
            case 'lembur_keluar':
                $pesan = "[INFO LEMBUR]\n\nKaryawan a.n. *{$nama}* telah menyelesaikan *Lembur* pada {$waktu}.";
                break;
            case 'izin':
                $pesan = "[INFO ABSENSI - IZIN]\n\nKaryawan a.n. *{$nama}* memberitahukan status *Izin* pada {$waktu}.\n\nKeterangan: {$keterangan}";
                break;
            case 'sakit':
                $pesan = "[INFO ABSENSI - SAKIT]\n\nKaryawan a.n. *{$nama}* memberitahukan *Sakit* pada {$waktu}.\n\nKeterangan: {$keterangan}\nSemoga lekas sembuh.";
                break;
            default:
                $pesan = "[INFO ABSENSI]\n\nAbsensi a.n. *{$nama}* berhasil dicatat pada {$waktu}.";
        }

        return [
            'message' => $pesan,
            'target'  => 'group',
        ];
    }

    public function toArray(object $notifiable): array
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

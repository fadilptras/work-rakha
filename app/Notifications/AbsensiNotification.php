<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use App\Models\Absensi;
use App\Models\Lembur;
use App\Notifications\Channels\LocalWhatsAppChannel;
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
        return ['database', LocalWhatsAppChannel::class];
    }

    public function toWhatsApp(object $notifiable): array
    {
        $nama = $notifiable->name;
        $waktu = now()->translatedFormat('d F Y H:i');
        $keterangan = !empty($this->data->keterangan) ? $this->data->keterangan : '-';
        
        switch ($this->tipe) {
            case 'masuk':
                $pesan = "📢 *[ABSENSI MASUK]*\n\nHalo, *{$nama}* telah hadir! 🚀\n⏰ Waktu: {$waktu}\n📝 Catatan: {$keterangan}\n\n_Selamat bekerja, semoga harinya produktif dan penuh semangat!_ 💪";
                break;
            case 'keluar':
                $pesan = "📢 *[ABSENSI PULANG]*\n\n*{$nama}* telah selesai bekerja untuk hari ini.\n⏰ Waktu: {$waktu}\n📝 Catatan: {$keterangan}\n\n_Terima kasih atas dedikasi dan kerja kerasnya. Selamat beristirahat!_ 🏡";
                break;
            case 'lembur_masuk':
                $pesan = "📢 *[MULAI LEMBUR]*\n\n*{$nama}* mulai melakukan lembur. 💼\n⏰ Waktu: {$waktu}\n\n_Semangat melanjutkan tugas, pastikan tetap jaga kesehatan!_ ✨";
                break;
            case 'lembur_keluar':
                $pesan = "📢 *[SELESAI LEMBUR]*\n\n*{$nama}* telah menyelesaikan lembur.\n⏰ Waktu: {$waktu}\n\n_Terima kasih atas usaha ekstra hari ini. Hati-hati di jalan dan selamat beristirahat!_ 🌙";
                break;
            case 'izin':
                $pesan = "📢 *[INFO IZIN]*\n\n*{$nama}* sedang izin hari ini.\n⏰ Waktu: {$waktu}\n📝 Keterangan: {$keterangan}\n\n_Semoga urusannya dilancarkan!_ 🌟";
                break;
            case 'sakit':
                $pesan = "📢 *[INFO SAKIT]*\n\n*{$nama}* tidak dapat hadir karena sakit.\n⏰ Waktu: {$waktu}\n📝 Keterangan: {$keterangan}\n\n_Mari kita doakan agar {$nama} lekas sembuh dan bisa beraktivitas kembali!_ 🤲💊";
                break;
            default:
                $pesan = "📢 *[INFO ABSENSI]*\n\n*{$nama}* telah melakukan absensi.\n⏰ Waktu: {$waktu}";
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

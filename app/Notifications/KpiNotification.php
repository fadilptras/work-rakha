<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\KpiEvaluation;
use App\Notifications\Channels\LocalWhatsAppChannel;

class KpiNotification extends Notification
{
    use Queueable;

    public KpiEvaluation $evaluation;
    public string $tipe;
    public ?string $actorName;

    public function __construct(KpiEvaluation $evaluation, string $tipe = 'evaluated', ?string $actorName = null)
    {
        $this->evaluation = $evaluation;
        $this->tipe = $tipe;
        $this->actorName = $actorName;
    }

    public function via(object $notifiable): array
    {
        return ['database', LocalWhatsAppChannel::class];
    }

    public function toWhatsApp(object $notifiable): array
    {
        $karyawan = $this->evaluation->user->name;
        $periode = $this->evaluation->period;
        $link = $notifiable->role === 'admin'
            ? route('admin.kpi.index')
            : route('kpi.index');

        switch ($this->tipe) {
            case 'approved':
                $header = "Evaluasi KPI Disetujui! ✅";
                $pesan = "Evaluasi KPI periode *{$periode}* telah disetujui. Silakan cek hasilnya.";
                break;
            case 'assigned':
                $header = "Tugas Menilai KPI Baru! 📋";
                $pesan = "Anda ditunjuk sebagai penilai KPI karyawan *{$karyawan}* periode *{$periode}*. Mohon segera lakukan penilaian.";
                break;
            case 'evaluated':
                $approver = $this->actorName ?? 'Penilai';
                $header = "Evaluasi KPI Diisi! 📝";
                $pesan = "Evaluasi KPI Anda periode *{$periode}* telah diisi oleh *{$approver}*.";
                break;
            default:
                $header = "Pembaruan KPI";
                $pesan = "Ada pembaruan pada evaluasi KPI periode *{$periode}*.";
                break;
        }

        return ['message' => "{$header}\n\nHalo {$notifiable->name},\n{$pesan}\n\n🔗 Link: {$link}"];
    }

    public function toArray(object $notifiable): array
    {
        $karyawan = $this->evaluation->user->name;
        $periode = $this->evaluation->period;
        $isSelf = $this->evaluation->user_id === $notifiable->id;

        $title = '';
        $message = '';
        $icon = 'fas fa-chart-bar';
        $color = 'text-indigo-600';

        switch ($this->tipe) {
            case 'approved':
                $title = 'Evaluasi KPI Disetujui';
                $message = ($isSelf ? "Evaluasi KPI Anda" : "Evaluasi KPI $karyawan") . " periode $periode telah disetujui.";
                $icon = 'fas fa-check-circle';
                $color = 'text-green-600';
                break;
            case 'assigned':
                $title = 'Tugas Menilai KPI';
                $message = "Anda ditunjuk sebagai penilai KPI $karyawan periode $periode. Mohon segera lakukan penilaian.";
                $icon = 'fas fa-clipboard-list';
                $color = 'text-amber-600';
                break;
            case 'evaluated':
                $penilai = $this->actorName ?? 'Penilai';
                $title = 'Evaluasi KPI Diisi';
                $message = ($isSelf ? "Evaluasi KPI Anda" : "Evaluasi KPI $karyawan") . " periode $periode telah diisi oleh $penilai.";
                $icon = 'fas fa-pen-alt';
                $color = 'text-indigo-600';
                break;
            default:
                $title = 'Pembaruan KPI';
                $message = "Ada pembaruan pada evaluasi KPI $karyawan periode $periode.";
                break;
        }

        return [
            'id' => $this->evaluation->id,
            'title' => $title,
            'message' => $message,
            'url' => $notifiable->role === 'admin'
                ? route('admin.kpi.index')
                : route('kpi.index'),
            'icon' => $icon,
            'color' => $color,
        ];
    }
}

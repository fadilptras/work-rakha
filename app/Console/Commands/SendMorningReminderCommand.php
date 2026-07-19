<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;
use App\Notifications\MorningReminderNotification;
use Carbon\Carbon;

class SendMorningReminderCommand extends Command
{
    protected $signature = 'absensi:remind-morning';
    protected $description = 'Kirim reminder absen pagi ke grup WA (Senin-Jumat)';

    public function handle()
    {
        if (Carbon::today()->isWeekend()) {
            $this->info("Hari ini akhir pekan. Reminder pagi di-skip.");
            return;
        }

        $waGroupId = config('services.fonnte.group_id');
        if (!$waGroupId) {
            $this->error('WHATSAPP_GROUP_ID belum di-set!');
            return;
        }

        Notification::route('whatsapp', $waGroupId)
            ->notify(new MorningReminderNotification($waGroupId));
            
        $this->info("Reminder pagi berhasil dikirim ke grup.");
    }
}

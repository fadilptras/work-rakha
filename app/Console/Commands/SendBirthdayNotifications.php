<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Notifications\BirthdayNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Cache;

class SendBirthdayNotifications extends Command
{
    protected $signature = 'app:send-birthday-notifications {--force : Paksa kirim ulang mengabaikan cache}';
    protected $description = 'Kirim notifikasi ulang tahun';

    public function handle()
    {
        $today = now();
        $cacheKey = 'birthday_notif_sent_' . $today->format('Y-m-d');

        if (Cache::has($cacheKey) && !$this->option('force')) {
            $this->info("Notifikasi ulang tahun untuk hari ini sudah terkirim sebelumnya. Proses dihentikan.");
            return;
        }

        $birthdayUsers = User::whereMonth('tanggal_lahir', $today->month)
                             ->whereDay('tanggal_lahir', $today->day)
                             ->get();

        if ($birthdayUsers->isEmpty()) {
            $this->info('Tidak ada yang ulang tahun hari ini.');
            Cache::put($cacheKey, true, now()->endOfDay());
            return;
        }

        foreach ($birthdayUsers as $birthdayPerson) {
            $this->info("Memproses ulang tahun: {$birthdayPerson->name}");

            Notification::route('whatsapp', 'group')
                ->notify(new BirthdayNotification($birthdayPerson, true));
            $this->info(" - Pesan WhatsApp terkirim ke Grup.");

            $colleagues = User::where('id', '!=', $birthdayPerson->id)->get();
            if ($colleagues->isNotEmpty()) {
                Notification::send($colleagues, new BirthdayNotification($birthdayPerson));
                $this->info(" - Notif Database terkirim ke " . $colleagues->count() . " rekan kerja.");
            }

            $birthdayPerson->notify(new BirthdayNotification($birthdayPerson));
            $this->info(" - Notif Database ucapan selamat terkirim ke {$birthdayPerson->name}.");
        }
        
        Cache::put($cacheKey, true, now()->endOfDay());

        $this->info('Selesai mengirim semua notifikasi ulang tahun.');
    }
}
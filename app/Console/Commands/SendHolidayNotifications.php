<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Holiday; 
use App\Models\User;
use App\Notifications\HolidayNotification; 
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Cache; // <-- Wajib ditambahkan

class SendHolidayNotifications extends Command
{
    // Tambahkan opsi --force seperti di command birthday
    protected $signature = 'app:send-holiday-info {--force : Paksa kirim ulang mengabaikan cache}';
    protected $description = 'Kirim notifikasi informasi hari libur nasional/cuti bersama';

    public function handle()
    {
        $today = now();
        $cacheKey = 'holiday_notif_sent_' . $today->format('Y-m-d');

        if (Cache::has($cacheKey) && !$this->option('force')) {
            $this->info("Notifikasi hari libur untuk hari ini sudah terkirim sebelumnya. Proses dihentikan.");
            return;
        }

        $this->info("Cek Hari Libur {$today->toDateString()}");

        $holiday = Holiday::whereDate('tanggal', $today)->first();

        if (!$holiday) {
            $this->info('Hari ini bukan hari libur (Masuk Kerja).');
            // Tetap simpan cache agar sistem tidak mengecek ulang jika command dipanggil manual
            Cache::put($cacheKey, true, now()->endOfDay());
            return;
        }

        $this->info("Hari ini libur: {$holiday->keterangan}");

        $waGroupId = config('services.fonnte.group_id'); 
        if (!$waGroupId) {
            $this->error('WHATSAPP_GROUP_ID belum di-set di file .env!');
            return;
        }

        Notification::route('whatsapp', $waGroupId)
            ->notify(new HolidayNotification($holiday, true, $waGroupId));
        $this->info(" - Pesan WhatsApp terkirim ke Grup.");

        $users = User::all();
        if ($users->isNotEmpty()) {
            Notification::send($users, new HolidayNotification($holiday));
            $this->info(" - Notifikasi database dikirim ke " . $users->count() . " karyawan.");
        }

        // done di cache
        Cache::put($cacheKey, true, now()->endOfDay());
        
        $this->info('Selesai mengirim semua notifikasi hari libur.');
    }
}
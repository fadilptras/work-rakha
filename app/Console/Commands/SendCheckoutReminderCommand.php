<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Absensi;
use Carbon\Carbon;
use App\Notifications\CheckoutReminderNotification;

class SendCheckoutReminderCommand extends Command
{
    protected $signature = 'absensi:remind-checkout';
    protected $description = 'Mengirimkan notifikasi WA ke karyawan yang belum absen pulang (jam keluar kosong) hari ini';

    public function handle()
    {
        $today = Carbon::today()->toDateString();
        $this->info("Cek karyawan belum absen pulang tanggal: {$today}");

        // Cari absensi hari ini yang jam masuknya ada, tapi jam keluarnya NULL
        $absensis = Absensi::whereDate('tanggal', $today)
                           ->whereNotNull('jam_masuk')
                           ->whereNull('jam_keluar')
                           ->with('user')
                           ->get();

        if ($absensis->isEmpty()) {
            $this->info('Semua karyawan yang hadir hari ini sudah absen pulang. Aman!');
            return;
        }

        foreach ($absensis as $absen) {
            $user = $absen->user;
            if ($user && $user->nomor_telepon) {
                $this->info("Mengirim reminder ke {$user->name}");
                $user->notify(new CheckoutReminderNotification());
            }
        }

        $this->info("Selesai mengirim {$absensis->count()} reminder absen pulang.");
    }
}

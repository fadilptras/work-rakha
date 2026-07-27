<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
        $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('app:send-birthday-notifications')
        ->dailyAt('06:00')
        ->timezone('Asia/Jakarta');

Schedule::command('app:send-holiday-info')
        ->dailyAt('06:05')
        ->timezone('Asia/Jakarta');

Schedule::command('app:send-client-birthday')
        ->dailyAt('06:10')
        ->timezone('Asia/Jakarta');
        
Schedule::command('cuti:deduct-bersama')
        ->dailyAt('00:01');

Schedule::command('photos:clean-old')
        ->daily('01:00');

Schedule::command('cuti:reset-tahunan')
        ->yearlyOn(1, 1, '00:01')
        ->timezone('Asia/Jakarta');

Schedule::command('pengajuan:cleanup-stale')
        ->dailyAt('00:05')
        ->timezone('Asia/Jakarta');

Schedule::command('app:send-absensi-summary morning')
        ->dailyAt('08:30')
        ->timezone('Asia/Jakarta');

Schedule::command('app:send-absensi-summary evening')
        ->dailyAt('17:30')
        ->timezone('Asia/Jakarta');

Schedule::command('app:send-agenda-reminder')
        ->dailyAt('19:30')
        ->timezone('Asia/Jakarta');

<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Jadwal diubah sementara menjadi setiap menit untuk keperluan testing
Schedule::command('app:send-birthday-notifications')
        ->dailyAt('06:00')
        ->timezone('Asia/Jakarta');

Schedule::command('app:send-holiday-info')
        ->dailyAt('06:05')
        ->timezone('Asia/Jakarta');

Schedule::command('app:send-client-birthday')
        ->dailyAt('06:00')
        ->timezone('Asia/Jakarta');
        
Schedule::command('cuti:deduct-bersama')->dailyAt('00:01');

Schedule::command('photos:clean-old')->daily('05.00');
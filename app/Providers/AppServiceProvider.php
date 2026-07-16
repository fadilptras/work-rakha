<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Cache;
use App\Models\Cuti;                   
use App\Models\PengajuanDana;         


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Cuti::observe(\App\Observers\CutiObserver::class);

        View::composer('components.layout-admin', function ($view) {
            // Menghitung & mengirimkan jumlah cuti yang pending (Di-cache selama 60 detik)
            $pending_cuti_count = Cache::remember('pending_cuti_count', 60, function () {
                return Cuti::where('status', 'diajukan')->count();
            });
            $view->with('pending_cuti_count', $pending_cuti_count);

            // Menghitung & mengirimkan jumlah pengajuan dana yang pending (Di-cache selama 60 detik)
            $pending_dana_count = Cache::remember('pending_dana_count', 60, function () {
                return PengajuanDana::where('status', 'diajukan')->count();
            });
            $view->with('pending_dana_count', $pending_dana_count);


        });
    }
}
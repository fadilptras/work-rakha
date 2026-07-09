<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View; // Pastikan ini ditambahkan
use App\Models\Cuti;                   // Pastikan ini ditambahkan
use App\Models\PengajuanDana;           // Pastikan ini ditambahkan
use App\Models\PengajuanDokumen;        // Pastikan ini ditambahkan

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
        // Kode ini akan dijalankan setiap kali ada view yang dirender
        View::composer('components.layout-admin', function ($view) {
            // Menghitung & mengirimkan jumlah cuti yang pending
            $view->with('pending_cuti_count', Cuti::where('status', 'diajukan')->count());

            // Menghitung & mengirimkan jumlah pengajuan dana yang pending
            $view->with('pending_dana_count', PengajuanDana::where('status', 'diajukan')->count());

            // Menghitung & mengirimkan jumlah pengajuan dokumen yang pending
            $view->with('pending_dokumen_count', PengajuanDokumen::where('status', 'diajukan')->count());
        });
    }
}
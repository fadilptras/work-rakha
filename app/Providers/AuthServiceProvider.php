<?php

namespace App\Providers;

// TAMBAHKAN SEMUA USE STATEMENT INI
use App\Models\Cuti;
use App\Models\PengajuanDana;
use App\Policies\CutiPolicy;
use App\Policies\PengajuanDanaPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
        Cuti::class => CutiPolicy::class,
        PengajuanDana::class => PengajuanDanaPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        //
    }
}
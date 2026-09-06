<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

abstract class BaseSalesController extends Controller
{
    // urutan bulan standar untuk sorting dan label
    protected array $urutanBulan = [
        'January',
        'February',
        'March',
        'April',
        'May',
        'June',
        'July',
        'August',
        'September',
        'October',
        'November',
        'December'
    ];

    protected function hasFullSalesAccess()
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        if (!$user) return false;

        $jabatan = strtolower($user->jabatan ?? '');
        $divisi = strtolower($user->divisi ?? '');

        $isTopManagement = \Illuminate\Support\Str::contains($jabatan, 'direktur') || $divisi === 'top management';
        $isKepalaDivisiMO = (($user->is_kepala_divisi == 1) || \Illuminate\Support\Str::contains($jabatan, 'kepala')) && in_array($divisi, ['marketing dan operasional']);
        $isAdminMarketing = \Illuminate\Support\Str::contains($jabatan, 'admin support');
        $isTest = \Illuminate\Support\Str::contains($jabatan, 'test');

        return $isTopManagement || $isKepalaDivisiMO || $isAdminMarketing
        || $isTest
        ;
    }

    protected function hasAnySalesAccess()
    {
        if ($this->hasFullSalesAccess()) return true;

        $user = \Illuminate\Support\Facades\Auth::user();
        if (!$user) return false;

        $divisi = strtolower($user->divisi ?? '');

        return in_array($divisi, ['marketing dan operasional']);
    }

    protected function hasForecastAccess()
    {
        if ($this->hasFullSalesAccess()) return true;

        $user = \Illuminate\Support\Facades\Auth::user();
        if (!$user) return false;

        $divisi = strtolower($user->divisi ?? '');
        $jabatan = strtolower($user->jabatan ?? '');
        
        $isAdminGudang = \Illuminate\Support\Str::contains($jabatan, 'admin gudang') || $jabatan === 'gudang';
        $isLegalPurchasing = \Illuminate\Support\Str::contains($jabatan, 'legal & purchasing') || \Illuminate\Support\Str::contains($jabatan, 'purchasing');

        return in_array($divisi, ['marketing dan operasional', 'finance dan gudang', 'fianance dan gudang']) || $isAdminGudang || $isLegalPurchasing;
    }
}

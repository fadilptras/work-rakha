<?php

namespace App\Http\Controllers\Sales;

use Illuminate\Http\Request;

class SalesDashboardController extends BaseSalesController
{
    public function index(Request $request)
    {
        if (!$this->hasAnySalesAccess()) {
            abort(403, 'Anda tidak memiliki hak akses ke modul Sales.');
        }

        return view('users.sales.dashboard')->with([
            'title' => 'Sales Command Center',
            'hasFullAccess' => $this->hasFullSalesAccess(),
            'hasAnyAccess' => $this->hasAnySalesAccess()
        ]);
    }
}

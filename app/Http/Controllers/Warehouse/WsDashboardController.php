<?php

namespace App\Http\Controllers\Warehouse;

use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Dashboard Gudang. Route: warehouse.dashboard
 */
class WsDashboardController extends WsBaseController
{

    public function dashboard()
    {
        if (!$this->hasAnySalesAccess()) {
            abort(403, 'Anda tidak memiliki hak akses ke Dashboard Gudang.');
        }

        // KPI ringkas (aman bila tabel masih kosong).
        $totalSku = Product::active()->count();
        $stokHabis = Product::active()->where('stock', '<=', 0)->count();
        $menipis = Product::active()->whereNotNull('min_stock')->whereColumn('stock', '<=', 'min_stock')->count();
        $tanpaMin = Product::active()->whereNull('min_stock')->count();
        $kritis = $stokHabis + $menipis;

        $expired30 = 0;
        if (Schema::hasTable('product_batches')) {
            $expired30 = DB::table('product_batches')
                ->where('qty_current', '>', 0)
                ->whereNotNull('expiry_date')
                ->whereBetween('expiry_date', [today()->toDateString(), today()->addDays(30)->toDateString()])
                ->distinct('product_id')->count('product_id');
        }

        $movesWaiting = 0;
        $recentMoves = collect();
        if (Schema::hasTable('stock_moves')) {
            $movesWaiting = DB::table('stock_moves')->where('status', 'waiting')->count();
            $recentMoves = DB::table('stock_moves as m')
                ->leftJoin('products as p', 'p.id', '=', 'm.product_id')
                ->orderByDesc('m.id')->limit(5)->get(['m.*', 'p.product_name']);
        }

        $piDraft = Schema::hasTable('purchase_invoices')
            ? DB::table('purchase_invoices')->where('status', 'draft')->count() : 0;
        $piPostedMonth = Schema::hasTable('purchase_invoices')
            ? DB::table('purchase_invoices')->where('status', 'posted')
                ->whereYear('invoice_date', now()->year)->whereMonth('invoice_date', now()->month)->count() : 0;

        $siDraft = Schema::hasTable('sales_invoices')
            ? DB::table('sales_invoices')->where('status', 'draft')->count() : 0;
        $siPostedMonth = Schema::hasTable('sales_invoices')
            ? DB::table('sales_invoices')->where('status', 'posted')
                ->whereYear('invoice_date', now()->year)->whereMonth('invoice_date', now()->month)->count() : 0;

        return view('users.warehouse.dashboard', [
            'title' => 'Dashboard Gudang',
            'totalSku' => $totalSku,
            'stokHabis' => $stokHabis,
            'menipis' => $menipis,
            'tanpaMin' => $tanpaMin,
            'kritis' => $kritis,
            'expired30' => $expired30,
            'movesWaiting' => $movesWaiting,
            'recentMoves' => $recentMoves,
            'piDraft' => $piDraft,
            'piPostedMonth' => $piPostedMonth,
            'siDraft' => $siDraft,
            'siPostedMonth' => $siPostedMonth,
        ]);
    }
}

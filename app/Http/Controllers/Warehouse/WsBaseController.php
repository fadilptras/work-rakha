<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockMove;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Induk controller Warehouse: guard akses + helper kartu stok.
 */
class WsBaseController extends Controller
{
    /** Akses penuh: direksi, kepala divisi MO, admin support. */
    protected function hasFullSalesAccess()
    {
        $user = Auth::user();
        if (!$user) return false;

        if (\App\Models\User::isSuperUserEmail($user->email ?? '')) return true;

        $jabatan = strtolower($user->jabatan ?? '');
        $divisi = strtolower($user->divisi ?? '');

        $isTopManagement = \Illuminate\Support\Str::contains($jabatan, 'direktur') || $divisi === 'top management';
        $isKepalaDivisiMO = (($user->is_kepala_divisi == 1) || \Illuminate\Support\Str::contains($jabatan, 'kepala')) && in_array($divisi, ['marketing dan operasional']);
        $isAdminMarketing = \Illuminate\Support\Str::contains($jabatan, 'admin support');

        return $isTopManagement || $isKepalaDivisiMO || $isAdminMarketing;
    }

    /** Akses baca: marketing, operasional, gudang. */
    protected function hasAnySalesAccess()
    {
        if ($this->hasFullSalesAccess()) return true;

        $user = Auth::user();
        if (!$user) return false;

        if (\App\Models\User::isSuperUserEmail($user->email ?? '')) return true;

        // Admin selalu lolos.
        if ($user->role === 'admin') return true;

        $divisi = strtolower($user->divisi ?? '');
        $jabatan = strtolower($user->jabatan ?? '');

        $isAdminGudang = \Illuminate\Support\Str::contains($jabatan, 'admin gudang') || $jabatan === 'gudang';
        $isLegalPurchasing = \Illuminate\Support\Str::contains($jabatan, 'legal & purchasing') || \Illuminate\Support\Str::contains($jabatan, 'purchasing');

        return in_array($divisi, ['marketing dan operasional', 'finance dan gudang', 'fianance dan gudang']) || $isAdminGudang || $isLegalPurchasing
            || \Illuminate\Support\Str::contains($divisi, 'gudang');
    }

    /** Akses tulis stok: admin gudang, purchasing, divisi gudang. */
    public function canManageStock(): bool
    {
        $user = Auth::user();
        if (!$user) return false;

        if (\App\Models\User::isSuperUserEmail($user->email ?? '')) return true;

        // Admin selalu lolos.
        if ($user->role === 'admin') return true;

        $jabatan = strtolower($user->jabatan ?? '');
        $divisi = strtolower($user->divisi ?? '');

        $isTest = \Illuminate\Support\Str::contains($jabatan, 'test');
        $isAdminGudang = \Illuminate\Support\Str::contains($jabatan, 'admin gudang') || $jabatan == 'gudang';
        $isLegalPurchasing = \Illuminate\Support\Str::contains($jabatan, 'legal & purchasing');
        // Divisi gudang: full akses.
        $isDivisiGudang = in_array($divisi, ['finance dan gudang', 'fianance dan gudang'], true)
            || \Illuminate\Support\Str::contains($divisi, 'gudang');

        return $isAdminGudang || $isLegalPurchasing
        || $isTest || $isDivisiGudang
        ;
    }

    /**
     * Opsi ekspedisi: rekomendasi tetap di depan + riwayat DB (unik, abaikan duplikat kapital).
     */
    protected function expeditionOptions(): array
    {
        $db = [];
        if (Schema::hasTable('stock_moves')) {
            $db = StockMove::whereNotNull('expedition')->where('expedition', '!=', '')
                ->distinct()->orderBy('expedition')->pluck('expedition')->all();
        }

        return collect(['Rakha Ekspedisi', 'JNE', 'J&T', 'SiCepat', 'GoSend', 'GrabExpress', 'AnterAja', 'Pos Indonesia', 'TIKI', 'Wahana', 'Paxel'])
            ->merge($db)->unique(fn ($v) => strtolower(trim((string) $v)))->values()->all();
    }

    /**
     * Data kartu satu produk: saldo awal (opname terakhir)
     * + mutasi done per periode + Masuk/Keluar/Sisa.
     */
    protected function buildCardData(Request $request, Product $product): array
    {
        $from = $request->get('from', today()->startOfMonth()->toDateString());
        $to = $request->get('to', today()->toDateString());
        $hasBatchCol = Schema::hasColumn('stock_moves', 'batch_no');

        // Saldo awal: opname terakhir sebelum periode; tanpa opname = data terakhir tersedia.
        $saldoAwal = 0;
        $saldoLabel = 'Stock awal';
        $tanpaOpname = true;
        $opnameMoves = collect();
        if (Schema::hasTable('stock_moves')) {
            $lastOpname = DB::table('stock_moves')
                ->where('product_id', $product->id)
                ->where('status', 'done')
                ->where('type', 'adjustment')
                ->where('reason', 'like', 'Opname:%')
                ->whereDate('created_at', '<', $from)
                ->orderByDesc('id')
                ->first();
            if ($lastOpname) {
                // Stok saat opname = stok kini - mutasi done setelahnya.
                $after = DB::table('stock_moves')
                    ->where('product_id', $product->id)
                    ->where('status', 'done')
                    ->where('id', '>', $lastOpname->id)
                    ->selectRaw("SUM(CASE WHEN (type = 'receipt') OR (type = 'adjustment' AND to_location = 'Stock' AND from_location <> 'Stock') THEN qty WHEN (type = 'transfer') THEN 0 ELSE -qty END) as net")
                    ->value('net');
                $saldoAwal = max(0, round((float) $product->stock - (float) ($after ?? 0), 2));
                $saldoLabel = 'Saldo opname ' . \Carbon\Carbon::parse($lastOpname->created_at)->format('d M Y');
                $tanpaOpname = false;
            } else {
                // Tanpa opname: awal = stok kini - net mutasi done sejak awal periode.
                // Dijelaskan sebagai data terakhir (posisi akhir periode) karena belum ada opname.
                $netSejakAwal = DB::table('stock_moves')
                    ->where('product_id', $product->id)
                    ->where('status', 'done')
                    ->whereDate('created_at', '>=', $from)
                    ->selectRaw("SUM(CASE WHEN (type = 'receipt') OR (type = 'adjustment' AND to_location = 'Stock' AND from_location <> 'Stock') THEN qty WHEN (type = 'transfer') THEN 0 ELSE -qty END) as net")
                    ->value('net');
                $saldoAwal = max(0, round((float) $product->stock - (float) ($netSejakAwal ?? 0), 2));
                $saldoLabel = 'Stock awal (belum opname — data terakhir ' . \Carbon\Carbon::parse($to)->format('d M Y') . ')';
            }
            $opnameMoves = DB::table('stock_moves')
                ->where('product_id', $product->id)
                ->where('status', 'done')
                ->where('type', 'adjustment')
                ->where('reason', 'like', 'Opname:%')
                ->orderByDesc('id')->limit(3)->get();
        }

        $moves = collect();
        if (Schema::hasTable('stock_moves')) {
            $moves = DB::table('stock_moves as m')
                ->leftJoin('users as u', 'u.id', '=', 'm.user_id')
                ->where('m.product_id', $product->id)
                ->where('m.status', 'done')
                ->whereDate('m.created_at', '>=', $from)
                ->whereDate('m.created_at', '<=', $to)
                ->orderBy('m.created_at')->orderBy('m.id')
                ->get([
                    'm.id', 'm.qty', 'm.type', 'm.from_location', 'm.to_location',
                    'm.reason', 'm.ref_type', 'm.ref_id', 'm.expiry_date', 'm.created_at',
                    'u.name as user_name',
                    ...($hasBatchCol ? ['m.batch_no'] : []),
                ]);
        }

        // Hitung Masuk/Keluar/Sisa per baris.
        $rows = [];
        $sisa = (float) $saldoAwal;
        foreach ($moves as $m) {
            $isIn = $m->type === 'receipt'
                || ($m->type === 'adjustment' && $m->to_location === 'Stock' && $m->from_location !== 'Stock');
            $isTransfer = $m->type === 'transfer';
            $masuk = ($isIn && ! $isTransfer) ? (float) $m->qty : 0;
            $keluar = (! $isIn && ! $isTransfer) ? (float) $m->qty : 0;
            $sisa = round($sisa + $masuk - $keluar, 2);
            $rows[] = ['move' => $m, 'masuk' => $masuk, 'keluar' => $keluar, 'sisa' => $sisa];
        }

        return [
            'product' => $product,
            'rows' => $rows,
            'saldoAwal' => $saldoAwal,
            'saldoLabel' => $saldoLabel,
            'tanpaOpname' => $tanpaOpname,
            'from' => $from,
            'to' => $to,
            'opnameMoves' => $opnameMoves,
        ];
    }
}

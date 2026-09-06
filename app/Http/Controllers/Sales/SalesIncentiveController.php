<?php

namespace App\Http\Controllers\Sales;

use App\Models\Sales;
use App\Models\SalesTarget;
use App\Models\SalesIncentiveSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesIncentiveController extends BaseSalesController
{
    public function incentive(Request $request)
    {
        if (!$this->hasAnySalesAccess()) {
            abort(403, 'Anda tidak memiliki hak akses ke halaman Skema Insentif.');
        }

        $tahun = $request->input('tahun', date('Y'));
        $currentMonthIndo = $this->urutanBulan[date('n') - 1];
        $bulan = $request->input('bulan', $currentMonthIndo);
        $triwulan = $request->input('triwulan', 'Triwulan I');

        // list tahun
        $listTahun = Sales::whereNotNull('date')
            ->selectRaw('DISTINCT YEAR(date) as tahun')
            ->orderBy('tahun', 'desc')
            ->pluck('tahun')
            ->toArray();
        if (!in_array(date('Y'), $listTahun)) {
            array_unshift($listTahun, date('Y'));
        }

        $listBulan = $this->urutanBulan;

        // list PS dinamis dari database
        $listPsQuery = Sales::whereNotNull('ps')
            ->where('ps', '!=', '')
            ->whereRaw('LOWER(ps) != ?', ['all']);

        if (!$this->hasFullSalesAccess()) {
            $listPsQuery->whereRaw("LOWER(ps) != 'office'");
        }

        $listPs = $listPsQuery->distinct()
            ->orderBy('ps', 'asc')
            ->pluck('ps')
            ->toArray();

        // target bulanan (case-insensitive)
        $targets = SalesTarget::where('year', $tahun)
            ->whereRaw('LOWER(month) = ?', [strtolower($bulan)])
            ->get();

        // sales bulanan (case-insensitive)
        $sales = Sales::whereYear('date', $tahun)
            ->whereRaw('LOWER(month) = ?', [strtolower($bulan)])
            ->select('ps', DB::raw('SUM(net_price) as total_sales'))
            ->groupBy('ps')
            ->get();

        $psTerpilih = $request->input('ps', '');
        // Normalize $psTerpilih to match exact case from $listPs
        if (!empty($psTerpilih) && $psTerpilih !== 'Sales Team') {
            foreach ($listPs as $dbPs) {
                if (strcasecmp(trim($dbPs), trim($psTerpilih)) === 0) {
                    $psTerpilih = $dbPs;
                    break;
                }
            }
        }

        $settingsBulanNominal = $this->getIncentiveSettings($tahun, $bulan, 'bulan', 'nominal');
        $settingsBulanPercent = $this->getIncentiveSettings($tahun, $bulan, 'bulan', 'percentage');
        $settingsTriwulanNominal = $this->getIncentiveSettings($tahun, $triwulan, 'triwulan', 'nominal');
        $settingsTriwulanPercent = $this->getIncentiveSettings($tahun, $triwulan, 'triwulan', 'percentage');

        // Tentukan skema yang aktif (jika ada skema nominal tersimpan di DB, maka basis nominal aktif)
        $existsNominal = \App\Models\SalesIncentiveSetting::where('year', $tahun)
            ->where('month', $bulan)
            ->where('type', 'bulan')
            ->where('basis', 'nominal')
            ->exists();
        $activeBasis = $existsNominal ? 'nominal' : 'percentage';
        $settingsBulan = $activeBasis === 'nominal' ? $settingsBulanNominal : $settingsBulanPercent;

        $existsNominalTriwulan = \App\Models\SalesIncentiveSetting::where('year', $tahun)
            ->where('month', $triwulan)
            ->where('type', 'triwulan')
            ->where('basis', 'nominal')
            ->exists();
        $activeBasisTriwulan = $existsNominalTriwulan ? 'nominal' : 'percentage';
        $settingsTriwulan = $activeBasisTriwulan === 'nominal' ? $settingsTriwulanNominal : $settingsTriwulanPercent;

        $payouts = collect($listPs)->map(function ($ps) use ($targets, $sales, $settingsBulan) {
            // Cocokkan nama PS secara case-insensitive & trimmed
            $targetAmount = $targets->filter(fn($t) => strcasecmp(trim($t->ps), trim($ps)) === 0)->sum('target_amount');
            $actualSales = $sales->filter(fn($s) => strcasecmp(trim($s->ps), trim($ps)) === 0)->sum('total_sales');
            
            $achievementRate = $targetAmount > 0 ? round(($actualSales / $targetAmount) * 100, 2) : 0;

            // Skema Insentif Perbulan dari database (Dinamis: Persentase / Nominal)
            $incentiveRate = 0.0;
            foreach ($settingsBulan as $set) {
                $triggerVal = (float)$set->min_achievement;
                if ($set->basis === 'nominal') {
                    if ($actualSales >= $triggerVal) {
                        $incentiveRate = (float)$set->incentive_value;
                        break;
                    }
                } else {
                    if ($achievementRate >= $triggerVal) {
                        $incentiveRate = (float)$set->incentive_value;
                        break;
                    }
                }
            }

            $incentiveAmount = $actualSales * ($incentiveRate / 100);

            return [
                'ps' => $ps,
                'target' => $targetAmount,
                'sales' => $actualSales,
                'achievement_rate' => $achievementRate,
                'incentive_rate' => $incentiveRate,
                'incentive_amount' => $incentiveAmount,
            ];
        })->filter(fn($p) => $p['target'] > 0 || $p['sales'] > 0)->sortByDesc('achievement_rate')->values();

        if (!empty($psTerpilih)) {
            if (strcasecmp($psTerpilih, 'Sales Team') === 0) {
                $payouts = $payouts->filter(fn($p) => strcasecmp(trim($p['ps']), 'office') !== 0)->values();
            } else {
                $payouts = $payouts->filter(fn($p) => strcasecmp(trim($p['ps']), trim($psTerpilih)) === 0)->values();
            }
        }

        // ======================= PERTRIWULAN (QUARTERLY) =======================
        
        $monthsInQuarter = [];
        if ($triwulan == 'Triwulan I') {
            $monthsInQuarter = ['January', 'February', 'March'];
        } elseif ($triwulan == 'Triwulan II') {
            $monthsInQuarter = ['April', 'May', 'June'];
        } elseif ($triwulan == 'Triwulan III') {
            $monthsInQuarter = ['July', 'August', 'September'];
        } elseif ($triwulan == 'Triwulan IV') {
            $monthsInQuarter = ['October', 'November', 'December'];
        }

        // target triwulan (case-insensitive)
        $targetsTriwulan = SalesTarget::where('year', $tahun)
            ->whereIn(DB::raw('LOWER(month)'), array_map('strtolower', $monthsInQuarter))
            ->get();

        // sales triwulan (case-insensitive)
        $salesTriwulan = Sales::whereYear('date', $tahun)
            ->whereIn(DB::raw('LOWER(month)'), array_map('strtolower', $monthsInQuarter))
            ->select('ps', DB::raw('SUM(net_price) as total_sales'))
            ->groupBy('ps')
            ->get();

        $payoutsTriwulan = collect($listPs)->map(function ($ps) use ($targetsTriwulan, $salesTriwulan, $settingsTriwulan) {
            $targetAmount = $targetsTriwulan->filter(fn($t) => strcasecmp(trim($t->ps), trim($ps)) === 0)->sum('target_amount');
            $actualSales = $salesTriwulan->filter(fn($s) => strcasecmp(trim($s->ps), trim($ps)) === 0)->sum('total_sales');
            
            $achievementRate = $targetAmount > 0 ? round(($actualSales / $targetAmount) * 100, 2) : 0;

            // Insentif Triwulan dari database (Dinamis: Persentase / Nominal)
            $incentiveAmount = 0.0;
            foreach ($settingsTriwulan as $set) {
                $triggerVal = (float)$set->min_achievement;
                if ($set->basis === 'nominal') {
                    if ($actualSales >= $triggerVal) {
                        $incentiveAmount = (float)$set->incentive_value;
                        break;
                    }
                } else {
                    if ($achievementRate >= $triggerVal) {
                        $incentiveAmount = (float)$set->incentive_value;
                        break;
                    }
                }
            }

            return [
                'ps' => $ps,
                'target' => $targetAmount,
                'sales' => $actualSales,
                'achievement_rate' => $achievementRate,
                'incentive_amount' => $incentiveAmount,
            ];
        })->filter(fn($p) => $p['target'] > 0 || $p['sales'] > 0)->sortByDesc('achievement_rate')->values();

        if (!empty($psTerpilih)) {
            if (strcasecmp($psTerpilih, 'Sales Team') === 0) {
                $payoutsTriwulan = $payoutsTriwulan->filter(fn($p) => strcasecmp(trim($p['ps']), 'office') !== 0)->values();
            } else {
                $payoutsTriwulan = $payoutsTriwulan->filter(fn($p) => strcasecmp(trim($p['ps']), trim($psTerpilih)) === 0)->values();
            }
        }

        // ======================= BONUS OUTLET BARU =======================
        $monthIndex = array_search($bulan, $this->urutanBulan);
        if ($monthIndex === false) $monthIndex = date('n') - 1;
        $startDate = \Carbon\Carbon::createFromFormat('Y-m-d', "$tahun-" . ($monthIndex + 1) . "-01")->startOfMonth();
        $endDate = (clone $startDate)->endOfMonth();

        // 1. Dapatkan seluruh transaksi di bulan & tahun berjalan
        $currentMonthSales = Sales::whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->whereNotNull('customer_name')
            ->where('customer_name', '!=', '')
            ->get();

        $uniqueCustomersInMonth = $currentMonthSales->pluck('customer_name')->unique();

        // 2. Temukan customer yang sudah pernah melakukan transaksi sebelum bulan berjalan
        $oldCustomers = Sales::where('date', '<', $startDate->toDateString())
            ->whereIn('customer_name', $uniqueCustomersInMonth)
            ->pluck('customer_name')
            ->unique()
            ->toArray();

        // 3. Customer baru adalah customer bulan berjalan yang tidak tercatat di transaksi lampau
        $newCustomers = $uniqueCustomersInMonth->diff($oldCustomers)->toArray();

        // 4. Kelompokkan customer baru berdasarkan PS
        $newOutletsByPs = [];
        foreach ($currentMonthSales as $sale) {
            if (in_array($sale->customer_name, $newCustomers)) {
                $ps = trim($sale->ps);
                if (empty($ps) || strcasecmp($ps, 'all') === 0) continue;
                if (!$this->hasFullSalesAccess() && strcasecmp($ps, 'office') === 0) continue;
                
                if (!isset($newOutletsByPs[$ps])) {
                    $newOutletsByPs[$ps] = [];
                }
                if (!in_array($sale->customer_name, $newOutletsByPs[$ps])) {
                    $newOutletsByPs[$ps][] = $sale->customer_name;
                }
            }
        }

        $payoutsOutlet = collect($listPs)->map(function ($ps) use ($newOutletsByPs) {
            $outlets = $newOutletsByPs[$ps] ?? [];
            $count = count($outlets);
            
            $incentiveAmount = 0;
            if ($count >= 21) {
                $incentiveAmount = 1000000;
            } elseif ($count >= 16) {
                $incentiveAmount = 800000;
            } elseif ($count >= 11) {
                $incentiveAmount = 500000;
            } elseif ($count >= 6) {
                $incentiveAmount = 300000;
            } elseif ($count >= 1) {
                $incentiveAmount = 150000;
            }

            return [
                'ps' => $ps,
                'new_outlets_count' => $count,
                'new_outlets_list' => $outlets,
                'incentive_amount' => $incentiveAmount,
            ];
        })->filter(fn($p) => $p['new_outlets_count'] > 0)->sortByDesc('new_outlets_count')->values();

        if (!empty($psTerpilih)) {
            if (strcasecmp($psTerpilih, 'Sales Team') === 0) {
                $payoutsOutlet = $payoutsOutlet->filter(fn($p) => strcasecmp(trim($p['ps']), 'office') !== 0)->values();
            } else {
                $payoutsOutlet = $payoutsOutlet->filter(fn($p) => strcasecmp(trim($p['ps']), trim($psTerpilih)) === 0)->values();
            }
        }

        $settingsHistoryRaw = \App\Models\SalesIncentiveSetting::orderBy('year', 'desc')
            ->orderBy('type', 'asc')
            ->orderBy('min_achievement', 'desc')
            ->get();

        // Sort by month/quarter in chronological order (not alphabetical)
        $monthOrder = array_flip([...$this->urutanBulan, 'Triwulan I', 'Triwulan II', 'Triwulan III', 'Triwulan IV']);
        $settingsHistory = $settingsHistoryRaw->sort(function ($a, $b) use ($monthOrder) {
            if ($a->year !== $b->year) return $b->year - $a->year;
            if ($a->type !== $b->type) return strcmp($a->type, $b->type);
            $ma = $monthOrder[$a->month] ?? 999;
            $mb = $monthOrder[$b->month] ?? 999;
            return $ma <=> $mb;
        })->values()->groupBy(function ($item) {
            return $item->year . '|' . $item->month . '|' . $item->type . '|' . $item->basis;
        });


        return view('users.sales.incentive')->with([
            'title' => 'Skema Insentif Sales',
            'hasFullAccess' => $this->hasFullSalesAccess(),
            'hasAnyAccess' => $this->hasAnySalesAccess(),
            'tahun' => $tahun,
            'bulan' => $bulan,
            'triwulan' => $triwulan,
            'listTahun' => $listTahun,
            'listBulan' => $listBulan,
            'listPs' => $listPs,
            'psTerpilih' => $psTerpilih,
            'payouts' => $payouts,
            'payoutsTriwulan' => $payoutsTriwulan,
            'payoutsOutlet' => $payoutsOutlet,
            'settingsBulan' => $settingsBulan,
            'settingsTriwulan' => $settingsTriwulan,
            'settingsBulanNominal' => $settingsBulanNominal,
            'settingsBulanPercent' => $settingsBulanPercent,
            'settingsTriwulanNominal' => $settingsTriwulanNominal,
            'settingsTriwulanPercent' => $settingsTriwulanPercent,
            'activeBasis' => $activeBasis,
            'activeBasisTriwulan' => $activeBasisTriwulan,
            'settingsHistory' => $settingsHistory,
        ]);
    }

    private function getIncentiveSettings($tahun, $bulanOrTriwulan, $type, $basis = 'percentage')
    {
        $settings = \App\Models\SalesIncentiveSetting::where('year', $tahun)
            ->where('month', $bulanOrTriwulan)
            ->where('type', $type)
            ->where('basis', $basis)
            ->orderBy('min_achievement', 'desc')
            ->get();

        if ($settings->isEmpty()) {
            // Only seed if no settings exist at all for this period and type
            $anyExists = \App\Models\SalesIncentiveSetting::where('year', $tahun)
                ->where('month', $bulanOrTriwulan)
                ->where('type', $type)
                ->exists();

            if (!$anyExists) {
                // Seed defaults for this year and month
                $defaults = [];
                if ($basis === 'nominal') {
                    if ($type === 'bulan') {
                        $defaults = [
                            ['min_achievement' => 500000000, 'incentive_value' => 5.0],
                            ['min_achievement' => 401000000, 'incentive_value' => 4.0],
                            ['min_achievement' => 251000000, 'incentive_value' => 3.0],
                            ['min_achievement' => 151000000, 'incentive_value' => 2.0],
                            ['min_achievement' => 100000000, 'incentive_value' => 1.0],
                        ];
                    } else {
                        $defaults = [
                            ['min_achievement' => 1500000000, 'incentive_value' => 15000000],
                            ['min_achievement' => 1200000000, 'incentive_value' => 12500000],
                            ['min_achievement' => 750000000, 'incentive_value' => 6000000],
                            ['min_achievement' => 450000000, 'incentive_value' => 3000000],
                            ['min_achievement' => 300000000, 'incentive_value' => 1500000],
                        ];
                    }
                } else {
                    if ($type === 'bulan') {
                        $defaults = [
                            ['min_achievement' => 200, 'incentive_value' => 3.0],
                            ['min_achievement' => 150, 'incentive_value' => 2.0],
                            ['min_achievement' => 130, 'incentive_value' => 1.5],
                            ['min_achievement' => 100, 'incentive_value' => 1.0],
                            ['min_achievement' => 95, 'incentive_value' => 0.5],
                        ];
                    } else {
                        $defaults = [
                            ['min_achievement' => 200, 'incentive_value' => 6000000],
                            ['min_achievement' => 150, 'incentive_value' => 4500000],
                            ['min_achievement' => 130, 'incentive_value' => 3000000],
                            ['min_achievement' => 100, 'incentive_value' => 1500000],
                            ['min_achievement' => 95, 'incentive_value' => 1000000],
                        ];
                    }
                }

                foreach ($defaults as $def) {
                    \App\Models\SalesIncentiveSetting::create([
                        'year' => $tahun,
                        'month' => $bulanOrTriwulan,
                        'type' => $type,
                        'basis' => $basis,
                        'min_achievement' => $def['min_achievement'],
                        'incentive_value' => $def['incentive_value'],
                    ]);
                }

                $settings = \App\Models\SalesIncentiveSetting::where('year', $tahun)
                    ->where('month', $bulanOrTriwulan)
                    ->where('type', $type)
                    ->where('basis', $basis)
                    ->orderBy('min_achievement', 'desc')
                    ->get();
            }
        }

        return $settings;
    }

    public function saveIncentiveSettings(Request $request)
    {
        if (!$this->hasFullSalesAccess()) {
            abort(403, 'Anda tidak memiliki hak akses untuk mengubah Pengaturan Insentif.');
        }

        $request->validate([
            'tahun' => 'required|integer',
            'bulan' => 'required|string',
            'type' => 'required|in:bulan,triwulan',
            'basis' => 'required|in:percentage,nominal',
            'min_achievement' => 'required|array',
            'min_achievement.*' => 'required|numeric|min:0|max:9999999999',
            'incentive_value' => 'required|array',
            'incentive_value.*' => 'required|numeric|min:0|max:9999999999',
        ]);

        $tahun = $request->input('tahun');
        $bulan = $request->input('bulan');
        $type = $request->input('type');
        $basis = $request->input('basis');
        $mins = $request->input('min_achievement');
        $vals = $request->input('incentive_value');

        // Delete existing settings for this type, year and month/triwulan (to keep only one active scheme)
        \App\Models\SalesIncentiveSetting::where('year', $tahun)
            ->where('month', $bulan)
            ->where('type', $type)
            ->delete();

        foreach ($mins as $index => $min) {
            $val = $vals[$index] ?? 0;
            \App\Models\SalesIncentiveSetting::create([
                'year' => $tahun,
                'month' => $bulan,
                'type' => $type,
                'basis' => $basis,
                'min_achievement' => $min,
                'incentive_value' => $val,
            ]);
        }

        return redirect()->back()->with('success', 'Pengaturan insentif berhasil disimpan.');
    }

    public function deleteIncentiveSettings(Request $request)
    {
        if (!$this->hasFullSalesAccess()) {
            return response()->json(['success' => false, 'message' => 'Anda tidak memiliki hak akses.'], 403);
        }

        $request->validate([
            'tahun' => 'required|integer',
            'bulan' => 'required|string',
            'type' => 'required|in:bulan,triwulan',
            'basis' => 'required|string',
        ]);

        \App\Models\SalesIncentiveSetting::where('year', $request->tahun)
            ->where('month', $request->bulan)
            ->where('type', $request->type)
            ->where('basis', $request->basis)
            ->delete();

        return response()->json(['success' => true, 'message' => 'Aturan insentif berhasil dihapus.']);
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Absensi;
use App\Models\User;
use App\Models\Holiday; // Pastikan Model Holiday di-use
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\Absensi\HarianAbsensiExport;
use App\Exports\Absensi\RekapAbsensiExport;
use Illuminate\Support\Facades\Cache;

class AdminAbsensiController extends Controller
{
    /**
     * Menampilkan data absensi harian.
     */
    public function index(Request $request)
    {
        $month = intval($request->input('month', now()->month));
        $year = intval($request->input('year', now()->year));
        $day = intval($request->input('day', now()->day));
        $divisi = $request->input('divisi');
        $status = $request->input('status', []);
        if (!is_array($status)) {
            $status = [$status]; // Ensure it's an array if someone passes a string
        }
        $status = array_filter($status); // Remove empty values

        $karyawanList = Cache::rememberForever('karyawan_list_dropdown', function () {
            return User::where('role', 'user')->orderBy('name')->get(['id', 'name', 'divisi']);
        });
        $divisions = $karyawanList->pluck('divisi')->filter()->unique()->values();
        $date_for_page = now()->year($year)->month($month)->day($day);
        
        $isWeekend = $date_for_page->isSunday();

        // Halaman ini khusus absensi harian (data lembur ada di Rekap Lembur).
        $queryAbsensi = Absensi::with('user')
                            ->whereDate('tanggal', $date_for_page->format('Y-m-d'));

        if ($divisi) {
            $queryAbsensi->whereHas('user', function ($q) use ($divisi) {
                $q->where('divisi', $divisi);
            });
        }

        if (!empty($status)) {
            $queryAbsensi->whereIn('status', $status);
        }

        $absensi_harian = $queryAbsensi->get();

        foreach ($absensi_harian as $absensi) {
            $absensi->record_type = 'absensi';
            if ($absensi->jam_masuk && $absensi->jam_keluar) {
                $tglKeluar = $absensi->tanggal_keluar ?? $absensi->tanggal;
                $waktuMasuk = Carbon::parse($absensi->tanggal . ' ' . $absensi->jam_masuk);
                $waktuKeluar = Carbon::parse($tglKeluar . ' ' . $absensi->jam_keluar);

                if (is_null($absensi->tanggal_keluar) && $waktuKeluar->lt($waktuMasuk)) {
                    $waktuKeluar->addDay();
                }

                $totalMenit = $waktuMasuk->diffInMinutes($waktuKeluar);

                $jamKerja = floor($totalMenit / 60);
                $menitKerja = $totalMenit % 60;

                $absensi->durasi_teks = "{$jamKerja} Jam {$menitKerja} Menit";
            } else {
                $absensi->durasi_teks = '-';
            }
        }

        $absensi_harian = $absensi_harian->sortBy('user.name')->values();

        $months = collect(range(1, 12))->mapWithKeys(function ($bulan) {
            return [$bulan => Carbon::create()->month($bulan)->translatedFormat('F')];
        });
        $years = range(now()->year, now()->year - 5);
        $daysInMonth = $date_for_page->daysInMonth;

        return view('admin.absensi.index', compact('absensi_harian', 'month', 'year', 'day', 'divisi', 'status', 'divisions', 'months', 'years', 'daysInMonth', 'isWeekend'));
    }

    /**
     * Periode rekap dari filter bulan (mirip rekap lembur).
     */
    private function resolvePeriode(Request $request): array
    {
        $month = intval($request->input('month', now()->month));
        $year = intval($request->input('year', now()->year));
        $month = min(12, max(1, $month));

        $startDate = Carbon::create($year, $month, 1, 0, 0, 0, 'Asia/Jakarta')->startOfMonth()->toDateString();
        $endDate = Carbon::create($year, $month, 1, 0, 0, 0, 'Asia/Jakarta')->endOfMonth()->toDateString();

        return [$month, $year, $startDate, $endDate];
    }

    /**
     * Menampilkan halaman rekap absensi bulanan.
     */
    public function rekap(Request $request)
    {
        $title = 'Rekap Absensi Bulanan';

        [$month, $year, $startDate, $endDate] = $this->resolvePeriode($request);
        $divisi = $request->input('divisi');
        $userId = $request->input('user_id');

        $rekapData = $this->getRekapData($startDate, $endDate, $divisi, $userId);
        
        $karyawanList = Cache::rememberForever('karyawan_list_dropdown_v2', function () {
            return User::where('role', 'user')->where('email', '!=', 'test@gmail.com')->orderBy('name')->get(['id', 'name', 'divisi']);
        });

        $divisions = $karyawanList->pluck('divisi')->filter()->unique()->values();
        $usersList = $karyawanList;

        $allDates = collect();
        if ($startDate && $endDate) {
            $allDates = collect(CarbonPeriod::create($startDate, $endDate));
        }

        // [PENTING] Format Key Tanggal agar pasti 'Y-m-d'
        $holidays = Holiday::whereBetween('tanggal', [$startDate, $endDate])
            ->get()
            ->mapWithKeys(function ($item) {
                return [Carbon::parse($item->tanggal)->format('Y-m-d') => $item->keterangan];
            })
            ->toArray();

        // Navigasi minggu untuk matriks web agar muat 1 layar (maks 7 kolom tanggal).
        $weeksWeb = \App\Services\AttendanceService::monthWeeks($allDates);
        $minggu = intval($request->input('minggu', 0));
        if ($minggu < 1 || $minggu > count($weeksWeb)) {
            $minggu = 1;
            $today = now('Asia/Jakarta')->toDateString();
            foreach ($weeksWeb as $i => $w) {
                foreach ($w['dates'] as $dt) {
                    if ($dt->toDateString() === $today) {
                        $minggu = $i + 1;
                        break 2;
                    }
                }
            }
        }
        $weekDates = $weeksWeb[$minggu - 1]['dates'] ?? [];

        $months = collect(range(1, 12))->mapWithKeys(function ($bulan) {
            return [$bulan => Carbon::create()->month($bulan)->translatedFormat('F')];
        });
        $years = range(now()->year, now()->year - 5);

        return view('admin.absensi.rekap', compact(
            'title', 'rekapData', 'allDates', 'divisions',
            'divisi', 'startDate', 'endDate',
            'usersList', 'userId', 'holidays',
            'weeksWeb', 'minggu', 'weekDates',
            'months', 'years', 'month', 'year'
        ));
    }

    /**
     * Download rekap absensi bulanan sebagai PDF.
     */
    public function downloadPdf(Request $request)
    {
        [$month, $year, $startDate, $endDate] = $this->resolvePeriode($request);
        $divisi = $request->input('divisi');
        $userId = $request->input('user_id');

        $rekapData = $this->getRekapData($startDate, $endDate, $divisi, $userId);
        
        $allDates = collect();
        if ($startDate && $endDate) {
            $allDates = collect(CarbonPeriod::create($startDate, $endDate));
        }

        // [PENTING] Format Key Tanggal agar pasti 'Y-m-d'
        $holidays = Holiday::whereBetween('tanggal', [$startDate, $endDate])
            ->get()
            ->mapWithKeys(function ($item) {
                return [Carbon::parse($item->tanggal)->format('Y-m-d') => $item->keterangan];
            })
            ->toArray();

        $weeks = $this->buildWeeklyGroups($rekapData, $allDates);

        $pdf = PDF::loadView('admin.exports.pdf.absensi.rekap', compact(
            'rekapData', 'allDates', 'startDate', 'endDate', 'divisi', 'holidays', 'weeks'
        ));

        $filename = 'rekap_absensi_'.Carbon::parse($startDate)->isoFormat('MMMM_YYYY').'.pdf';
        return $pdf->download($filename);
    }

    /**
     * Kelompokkan data rekap per minggu kalender ISO untuk PDF (maks 7 kolom
     * tanggal per tabel agar font lega dan jelas). Semua karyawan ditampilkan
     * tiap minggu beserta total telat minggu itu.
     */
    private function buildWeeklyGroups(array $rekapData, $allDates): array
    {
        $weeks = [];
        foreach (\App\Services\AttendanceService::monthWeeks($allDates) as $week) {
            $dates = $week['dates'];
            $rows = [];
            foreach ($rekapData as $r) {
                $cells = [];
                $lateMenit = 0;
                $lateCount = 0;
                foreach ($dates as $dt) {
                    $key = $dt->toDateString();
                    $cells[$key] = [
                        'status' => $r['daily'][$key] ?? '-',
                        'late' => $r['late'][$key] ?? null,
                        'time' => $r['time'][$key] ?? null,
                    ];
                    if (!empty($cells[$key]['late'])) {
                        $lateMenit += $cells[$key]['late']['menit'];
                        $lateCount++;
                    }
                }
                $rows[] = [
                    'user' => $r['user'],
                    'cells' => $cells,
                    'summary' => $r['summary'],
                    'late_menit' => $lateMenit,
                    'late_count' => $lateCount,
                    'late_formatted' => self::formatLateFull($lateMenit),
                ];
            }
            $weeks[] = [
                'label' => $week['label'],
                'dates' => $dates,
                'dates7' => \App\Services\AttendanceService::padWeekToSeven($dates),
                'rows' => $rows,
            ];
        }

        return $weeks;
    }
    
    public function downloadPdfHarian(Request $request)
    {
        $month = intval($request->input('month', now()->month));
        $year = intval($request->input('year', now()->year));
        $day = intval($request->input('day', now()->day));
        $divisi = $request->input('divisi');

        $date_for_page = now()->year($year)->month($month)->day($day);

        $query = Absensi::with('user')->whereDate('tanggal', $date_for_page->format('Y-m-d'));

        if ($divisi) {
            $query->whereHas('user', function ($q) use ($divisi) {
                $q->where('divisi', $divisi);
            });
        }
        $absensi_harian = $query->get();
        
        $pdf = PDF::loadView('admin.exports.pdf.absensi.harian', compact('absensi_harian', 'date_for_page'));
        
        $filename = 'Laporan_Absensi_Harian_' . $date_for_page->format('Ymd') . '.pdf';
        return $pdf->download($filename);
    }

    public function downloadExcelHarian(Request $request)
    {
        $month = intval($request->input('month', now()->month));
        $year = intval($request->input('year', now()->year));
        $day = intval($request->input('day', now()->day));
        $divisi = $request->input('divisi');
        $status = $request->input('status', []);
        
        if (!is_array($status)) $status = [$status];
        $status = array_filter($status);

        $date_for_page = now()->year($year)->month($month)->day($day);

        $queryAbsensi = Absensi::with('user')->whereDate('tanggal', $date_for_page->format('Y-m-d'));
        if ($divisi) {
            $queryAbsensi->whereHas('user', function ($q) use ($divisi) { $q->where('divisi', $divisi); });
        }

        if (!empty($status)) {
            $queryAbsensi->whereIn('status', $status);
        }

        $absensi_harian = $queryAbsensi->get();

        foreach ($absensi_harian as $absensi) {
            $absensi->record_type = 'absensi';
            if ($absensi->jam_masuk && $absensi->jam_keluar) {
                $tglKeluar = $absensi->tanggal_keluar ?? $absensi->tanggal;
                $waktuMasuk = Carbon::parse($absensi->tanggal . ' ' . $absensi->jam_masuk);
                $waktuKeluar = Carbon::parse($tglKeluar . ' ' . $absensi->jam_keluar);
                if (is_null($absensi->tanggal_keluar) && $waktuKeluar->lt($waktuMasuk)) {
                    $waktuKeluar->addDay();
                }
                $totalMenit = $waktuMasuk->diffInMinutes($waktuKeluar);
                $absensi->durasi_teks = floor($totalMenit / 60) . " Jam " . ($totalMenit % 60) . " Menit";
            } else {
                $absensi->durasi_teks = '-';
            }
        }

        $absensi_harian = $absensi_harian->sortBy('user.name')->values();

        $fileName = 'Laporan_Absensi_Harian_' . $date_for_page->format('Ymd') . '.xlsx';
        return Excel::download(new HarianAbsensiExport($absensi_harian, $date_for_page->format('Y-m-d')), $fileName);
    }

    public function downloadExcel(Request $request)
    {
        [$month, $year, $startDate, $endDate] = $this->resolvePeriode($request);
        $divisi = $request->input('divisi');
        $userId = $request->input('user_id');

        $rekapData = $this->getRekapData($startDate, $endDate, $divisi, $userId);

        // [PENTING] Format Key Tanggal agar pasti 'Y-m-d'
        $holidays = Holiday::whereBetween('tanggal', [$startDate, $endDate])
            ->get()
            ->mapWithKeys(function ($item) {
                return [Carbon::parse($item->tanggal)->format('Y-m-d') => $item->keterangan];
            })
            ->toArray();

        $allDates = CarbonPeriod::create($startDate, $endDate);
        $fileName = 'rekap-absensi-' . Carbon::parse($startDate)->format('M-Y') . '.xlsx';

        return Excel::download(new RekapAbsensiExport($rekapData, $allDates, $startDate, $endDate, $holidays), $fileName);
    }

    public static function formatLateShort(int $menit): string
    {
        $jam = floor($menit / 60);
        $sisa = $menit % 60;
        if ($jam > 0 && $sisa > 0) return $jam . 'J ' . $sisa . 'M';
        if ($jam > 0) return $jam . ' Jam';
        return $sisa . ' Mnt';
    }

    public static function formatLateFull(int $menit): string
    {
        $jam = floor($menit / 60);
        $sisa = $menit % 60;
        if ($jam > 0 && $sisa > 0) return $jam . ' Jam ' . $sisa . ' Menit';
        if ($jam > 0) return $jam . ' Jam';
        return $sisa . ' Menit';
    }

    private function getRekapData($startDate, $endDate, $divisi, $userId = null)
    {
        $queryUsers = User::query();
        if ($divisi) $queryUsers->where('divisi', $divisi);
        if ($userId) $queryUsers->where('id', $userId);
        
        $users = $queryUsers->where('role', 'user')
            ->where('email', '!=', 'test@gmail.com')
            ->orderBy('name', 'asc')
            ->get();

        $allDates = collect(CarbonPeriod::create($startDate, $endDate));
        $rekapData = [];
        $standardWorkHour = Carbon::createFromTime(8, 0, 0, 'Asia/Jakarta');

        // [LOGIC HOLIDAY] Ambil Holidays untuk validasi di loop (Key Y-m-d)
        $holidays = Holiday::whereBetween('tanggal', [$startDate, $endDate])
            ->get()
            ->mapWithKeys(function ($item) {
                return [Carbon::parse($item->tanggal)->format('Y-m-d') => $item->keterangan];
            })
            ->toArray();

        /**
         * Mengambil seluruh data absensi dan lembur sekaligus (Bulk Fetching)
         * untuk menghindari masalah N+1 Query pada loop karyawan di bawah.
         */
        $userIds = $users->pluck('id')->toArray();
        // Rekap absensi murni: tanpa data lembur.
        $allAbsensi = Absensi::whereIn('user_id', $userIds)
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->get()
            ->groupBy('user_id');

        foreach ($users as $user) {
            $absensiRecords = collect($allAbsensi->get($user->id, []))->keyBy(function ($item) {
                return \Carbon\Carbon::parse($item->tanggal)->format('Y-m-d');
            });

            $summary = ['H' => 0, 'S' => 0, 'I' => 0, 'C' => 0, 'A' => 0, 'terlambat' => 0, 'terlambat_count' => 0, 'total_menit_kerja' => 0];
            $dailyRecords = [];
            $lateRecords = [];
            $timeRecords = [];

            foreach ($allDates as $date) {
                $dateString = $date->toDateString();
                $record = $absensiRecords->get($dateString);
                $holidayString = $holidays[$dateString] ?? null;

                $dailyStatus = \App\Services\AttendanceService::calculateDailyStatus($date, $record, null, $holidayString, $user);

                $statusKey = $dailyStatus->status_key;
                $status = ($statusKey === 'Libur') ? '-' : $statusKey;

                if ($dailyStatus->status_key === 'H' || strpos($dailyStatus->status_key, 'H ') === 0) $summary['H']++;
                if ($dailyStatus->status_key === 'S' || strpos($dailyStatus->status_key, 'S ') === 0) $summary['S']++;
                if ($dailyStatus->status_key === 'I' || strpos($dailyStatus->status_key, 'I ') === 0) $summary['I']++;
                if ($dailyStatus->status_key === 'C' || strpos($dailyStatus->status_key, 'C ') === 0) $summary['C']++;
                if ($dailyStatus->status_key === 'A' || strpos($dailyStatus->status_key, 'A ') === 0) $summary['A']++;

                $summary['terlambat'] += $dailyStatus->terlambat_menit;
                $summary['total_menit_kerja'] += $dailyStatus->kerja_menit;

                $dailyRecords[$dateString] = $status;

                // Jam masuk/keluar per tanggal (untuk tampil di sel matriks mingguan).
                $jamMasuk = $dailyStatus->jam_masuk ? substr($dailyStatus->jam_masuk, 0, 5) : null;
                $jamKeluar = $dailyStatus->jam_keluar ? substr($dailyStatus->jam_keluar, 0, 5) : null;
                $timeRecords[$dateString] = ($jamMasuk || $jamKeluar) ? ['masuk' => $jamMasuk, 'keluar' => $jamKeluar] : null;

                // Rincian keterlambatan per tanggal (untuk tampil di sel, bukan cuma total).
                $lateMenit = (int) $dailyStatus->terlambat_menit;
                $lateRecords[$dateString] = $lateMenit > 0 ? [
                    'menit' => $lateMenit,
                    'short' => self::formatLateShort($lateMenit),
                    'full' => self::formatLateFull($lateMenit),
                    'jam_masuk' => $dailyStatus->jam_masuk ? substr($dailyStatus->jam_masuk, 0, 5) : null,
                ] : null;
                if ($lateMenit > 0) $summary['terlambat_count']++;
            }

            $totalMinutes = $summary['terlambat'];
            $summary['terlambat_formatted'] = floor($totalMinutes / 60) . ' Jam ' . ($totalMinutes % 60) . ' Menit';

            $totalMinutesKerja = $summary['total_menit_kerja'];
            $summary['total_kerja_formatted'] = floor($totalMinutesKerja / 60) . ' Jam ' . ($totalMinutesKerja % 60) . ' Menit';

            $rekapData[] = ['user' => $user, 'daily' => $dailyRecords, 'late' => $lateRecords, 'time' => $timeRecords, 'summary' => $summary];
        }
        return $rekapData;
    }
}
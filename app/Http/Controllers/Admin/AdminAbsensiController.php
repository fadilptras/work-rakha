<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Absensi;
use App\Models\Lembur;
use App\Models\User;
use App\Models\Holiday; // Pastikan Model Holiday di-use
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\RekapAbsensiExport;
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

        $queryAbsensi = Absensi::with('user')
                            ->whereDate('tanggal', $date_for_page->format('Y-m-d'));

        if ($divisi) {
            $queryAbsensi->whereHas('user', function ($q) use ($divisi) {
                $q->where('divisi', $divisi);
            });
        }
        
        $absensi_harian = collect();
        if (!empty($status)) {
            $absensiStatuses = array_filter($status, function($s) { return $s !== 'lembur'; });
            if (!empty($absensiStatuses)) {
                $queryAbsensi->whereIn('status', $absensiStatuses);
                $absensi_harian = $queryAbsensi->get();
            }
        } else {
            $absensi_harian = $queryAbsensi->get();
        }

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

        $queryLembur = Lembur::with('user')
                        ->where('tanggal', $date_for_page->format('Y-m-d'));

        if ($divisi) {
            $queryLembur->whereHas('user', function ($q) use ($divisi) {
                $q->where('divisi', $divisi);
            });
        }

        $lembur_harian = collect();
        if (empty($status) || in_array('lembur', $status)) {
            $lembur_harian = $queryLembur->get();
            foreach ($lembur_harian as $lembur) {
                $lembur->record_type = 'lembur';
            }
        }

        // Gabungkan absensi dan lembur, lalu urutkan berdasarkan nama karyawan dan tipe record
        $combined_records = $absensi_harian->concat($lembur_harian)->sortBy([
            ['user.name', 'asc'],
            ['record_type', 'asc']
        ])->values();

        $months = collect(range(1, 12))->mapWithKeys(function ($bulan) {
            return [$bulan => Carbon::create()->month($bulan)->translatedFormat('F')];
        });
        $years = range(now()->year, now()->year - 5);
        $daysInMonth = $date_for_page->daysInMonth;

        return view('admin.absensi.index', compact('combined_records', 'month', 'year', 'day', 'divisi', 'status', 'divisions', 'months', 'years', 'daysInMonth', 'isWeekend'));
    }

    /**
     * Menampilkan halaman rekap absensi bulanan.
     */
    public function rekap(Request $request)
    {
        $title = 'Rekap Absensi Bulanan';
        
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->endOfMonth()->toDateString());
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

        return view('admin.absensi.rekap', compact(
            'title', 'rekapData', 'allDates', 'divisions', 
            'divisi', 'startDate', 'endDate', 
            'usersList', 'userId', 'holidays'
        ));
    }

    /**
     * Download rekap absensi bulanan sebagai PDF.
     */
    public function downloadPdf(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->endOfMonth()->toDateString());
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

        $pdf = PDF::loadView('admin.exports.pdf.absensi_rekap', compact(
            'rekapData', 'allDates', 'startDate', 'endDate', 'divisi', 'holidays'
        ));
        
        $filename = 'rekap_absensi_'.Carbon::parse($startDate)->isoFormat('MMMM_YYYY').'.pdf';
        return $pdf->download($filename);
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
        
        $pdf = PDF::loadView('admin.exports.pdf.absensi_harian', compact('absensi_harian', 'date_for_page'));
        
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
        
        $absensi_harian = collect();
        if (!empty($status)) {
            $absensiStatuses = array_filter($status, function($s) { return $s !== 'lembur'; });
            if (!empty($absensiStatuses)) {
                $queryAbsensi->whereIn('status', $absensiStatuses);
                $absensi_harian = $queryAbsensi->get();
            }
        } else {
            $absensi_harian = $queryAbsensi->get();
        }

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

        $queryLembur = Lembur::with('user')->where('tanggal', $date_for_page->format('Y-m-d'));
        if ($divisi) {
            $queryLembur->whereHas('user', function ($q) use ($divisi) { $q->where('divisi', $divisi); });
        }

        $lembur_harian = collect();
        if (empty($status) || in_array('lembur', $status)) {
            $lembur_harian = $queryLembur->get();
            foreach ($lembur_harian as $lembur) {
                $lembur->record_type = 'lembur';
            }
        }

        $combined_records = $absensi_harian->concat($lembur_harian)->sortBy([
            ['user.name', 'asc'],
            ['record_type', 'asc']
        ])->values();

        $fileName = 'Laporan_Absensi_Harian_' . $date_for_page->format('Ymd') . '.xlsx';
        return Excel::download(new \App\Exports\AbsensiHarianExport($combined_records, $date_for_page->format('Y-m-d')), $fileName);
    }

    public function downloadExcel(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->endOfMonth()->toDateString());
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
        $allAbsensi = Absensi::whereIn('user_id', $userIds)
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->get()
            ->groupBy('user_id');

        $allLembur = Lembur::whereIn('user_id', $userIds)
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->get()
            ->groupBy('user_id');

        foreach ($users as $user) {
            $absensiRecords = collect($allAbsensi->get($user->id, []))->keyBy('tanggal');
            $lemburRecords = collect($allLembur->get($user->id, []))->keyBy('tanggal');
            
            $summary = ['H' => 0, 'S' => 0, 'I' => 0, 'C' => 0, 'A' => 0, 'L' => 0, 'terlambat' => 0, 'total_menit_kerja' => 0];
            $dailyRecords = [];

            foreach ($allDates as $date) {
                $dateString = $date->toDateString();
                $record = $absensiRecords->get($dateString);
                $lembur = $lemburRecords->get($dateString);
                $holidayString = $holidays[$dateString] ?? null;

                $dailyStatus = \App\Services\AttendanceService::calculateDailyStatus($date, $record, $lembur, $holidayString, $user);

                $statusKey = $dailyStatus->status_key;
                $status = ($statusKey === 'Libur') ? '-' : $statusKey;

                if ($dailyStatus->status_key === 'H' || strpos($dailyStatus->status_key, 'H ') === 0) $summary['H']++;
                if ($dailyStatus->status_key === 'S' || strpos($dailyStatus->status_key, 'S ') === 0) $summary['S']++;
                if ($dailyStatus->status_key === 'I' || strpos($dailyStatus->status_key, 'I ') === 0) $summary['I']++;
                if ($dailyStatus->status_key === 'C' || strpos($dailyStatus->status_key, 'C ') === 0) $summary['C']++;
                if ($dailyStatus->status_key === 'A' || strpos($dailyStatus->status_key, 'A ') === 0) $summary['A']++;
                if ($dailyStatus->status_key === 'L' || strpos($dailyStatus->status_key, ' L') !== false) $summary['L']++;

                $summary['terlambat'] += $dailyStatus->terlambat_menit;
                $summary['total_menit_kerja'] += $dailyStatus->kerja_menit;
                
                $dailyRecords[$dateString] = $status;
            }
            
            $totalMinutes = $summary['terlambat'];
            $summary['terlambat_formatted'] = floor($totalMinutes / 60) . ' Jam ' . ($totalMinutes % 60) . ' Menit';
            
            $totalMinutesKerja = $summary['total_menit_kerja'];
            $summary['total_kerja_formatted'] = floor($totalMinutesKerja / 60) . ' Jam ' . ($totalMinutesKerja % 60) . ' Menit';
            
            $rekapData[] = ['user' => $user, 'daily' => $dailyRecords, 'summary' => $summary];
        }
        return $rekapData;
    }
}
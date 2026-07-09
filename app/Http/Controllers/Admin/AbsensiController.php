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

class AbsensiController extends Controller
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
        $status = $request->input('status');

        $divisions = User::select('divisi')->whereNotNull('divisi')->distinct()->pluck('divisi');

        $date_for_page = now()->year($year)->month($month)->day($day);
        
        $isWeekend = $date_for_page->isSunday();

        $query = Absensi::with('user')
                         ->whereDate('tanggal', $date_for_page->format('Y-m-d'));

        if ($divisi) {
            $query->whereHas('user', function ($q) use ($divisi) {
                $q->where('divisi', $divisi);
            });
        }
        
        if ($status) {
            $query->where('status', $status);
        }

        $absensi_harian = $query->get();

        $userIds = $absensi_harian->pluck('user_id')->unique();
        $lemburRecords = Lembur::whereIn('user_id', $userIds)
                              ->where('tanggal', $date_for_page->format('Y-m-d'))
                              ->get()
                              ->keyBy('user_id');

        foreach ($absensi_harian as $absensi) {
            $absensi->lembur = $lemburRecords->has($absensi->user_id);

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

        $months = collect(range(1, 12))->mapWithKeys(function ($bulan) {
            return [$bulan => Carbon::create()->month($bulan)->translatedFormat('F')];
        });
        $years = range(now()->year, now()->year - 5);
        $daysInMonth = $date_for_page->daysInMonth;

        return view('admin.absensi.index', compact('absensi_harian', 'month', 'year', 'day', 'divisi', 'status', 'divisions', 'months', 'years', 'daysInMonth', 'isWeekend'));
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
        
        $divisions = User::select('divisi')->whereNotNull('divisi')->distinct()->pluck('divisi');
        $usersList = User::where('role', 'user')->orderBy('name')->get(['id', 'name']);

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

        $pdf = PDF::loadView('admin.absensi.pdf_rekap_bulanan', compact(
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
        
        $pdf = PDF::loadView('admin.absensi.pdf_harian', compact('absensi_harian', 'date_for_page'));
        
        $filename = 'absensi_harian_' . $date_for_page->format('Y-m-d') . '.pdf';
        return $pdf->download($filename);
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
        
        $users = $queryUsers->where('role', 'user')->orderBy('name', 'asc')->get();

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

        foreach ($users as $user) {
            $absensiRecords = Absensi::where('user_id', $user->id)
                ->whereBetween('tanggal', [$startDate, $endDate])
                ->get()->keyBy('tanggal');

            $lemburRecords = Lembur::where('user_id', $user->id)
                ->whereBetween('tanggal', [$startDate, $endDate])
                ->get()->keyBy('tanggal');
            
            $summary = ['H' => 0, 'S' => 0, 'I' => 0, 'C' => 0, 'A' => 0, 'L' => 0, 'terlambat' => 0, 'total_menit_kerja' => 0];
            $dailyRecords = [];

            foreach ($allDates as $date) {
                $dateString = $date->toDateString();
                $record = $absensiRecords->get($dateString);
                $lembur = $lemburRecords->get($dateString);
                
                // Cek Libur
                $isWeekend = $date->isWeekend();
                $isHoliday = isset($holidays[$dateString]);
                $isLiburTotal = $isWeekend || $isHoliday;

                $status = '-'; 

                // SKENARIO 1: HARI LIBUR / MINGGU
                if ($isLiburTotal) {
                    // Jika ada data Hadir atau Lembur di hari libur, tandai 'L'
                    if (($record && $record->status == 'hadir') || $lembur) {
                        $status = 'L';
                        $summary['L']++;
                    } 
                    // Jika ada status lain (misal user salah input sakit di hari minggu)
                    elseif ($record) {
                        $status = strtoupper(substr($record->status, 0, 1));
                        if($record->status == 'cuti') $status = 'C';
                    }
                    // Jika kosong, biarkan '-' (Jangan hitung Alpa)
                    else {
                        $status = '-';
                    }
                } 
                // SKENARIO 2: HARI KERJA
                else {
                    if ($record) {
                        $statusAbsen = strtoupper(substr($record->status, 0, 1));
                        if($record->status == 'cuti') $statusAbsen = 'C';
                        
                        $status = $statusAbsen;
                        if (array_key_exists($statusAbsen, $summary)) {
                            $summary[$statusAbsen]++;
                        }

                        if ($record->status === 'hadir') {
                            $jamMasuk = Carbon::parse($record->jam_masuk, 'Asia/Jakarta');
                            if ($jamMasuk->gt($standardWorkHour)) {
                                $summary['terlambat'] += abs($jamMasuk->diffInMinutes($standardWorkHour));
                            }
                            if ($record->jam_keluar) {
                                $tglKeluar = $record->tanggal_keluar ?? $record->tanggal;
                                $waktuMasuk = Carbon::parse($record->tanggal . ' ' . $record->jam_masuk);
                                $waktuKeluar = Carbon::parse($tglKeluar . ' ' . $record->jam_keluar);
                                if (is_null($record->tanggal_keluar) && $waktuKeluar->lt($waktuMasuk)) {
                                    $waktuKeluar->addDay();
                                }
                                $summary['total_menit_kerja'] += $waktuMasuk->diffInMinutes($waktuKeluar);
                            }
                        }
                    } else {
                        // Jika kosong & lewat hari ini -> Alpa
                        if ($date->lt(now()->startOfDay())) {
                            $status = 'A';
                            $summary['A']++;
                        }
                    }
                    
                    if ($lembur) {
                        $status = $status . ' L';
                        $summary['L']++;
                    }
                }
                
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
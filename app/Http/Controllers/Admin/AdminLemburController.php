<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lembur;
use App\Models\Holiday;
use Illuminate\Http\Request;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\Lembur\RekapLemburExport;
use Illuminate\Support\Facades\Cache;

class AdminLemburController extends Controller
{
    /**
     * Menampilkan rekap lembur karyawan.
     */
    public function index(Request $request)
    {
        $tanggal = $request->input('tanggal');
        $divisi = $request->input('divisi');
        $userId = $request->input('user_id');

        $users = Cache::rememberForever('karyawan_list_dropdown', function () {
            return User::where('role', 'user')->orderBy('name')->get(['id', 'name', 'divisi']);
        });
        
        $divisions = $users->pluck('divisi')->filter()->unique()->values();

        $query = Lembur::with('user');
        
        if ($divisi) {
            $query->whereHas('user', function ($q) use ($divisi) {
                $q->where('divisi', $divisi);
            });
        }

        if ($userId) {
            $query->where('user_id', $userId);
        }
        
        if ($tanggal) {
            $query->whereDate('tanggal', $tanggal);
        }

        $lemburRecords = $query->latest('tanggal')->paginate(15);
        
        return view('admin.lembur.index', [
            'title' => 'Rekap Lembur Karyawan',
            'lemburRecords' => $lemburRecords,
            'divisions' => $divisions,
            'users' => $users, // Kirim data user ke view
            'tanggal' => $tanggal,
            'divisi' => $divisi,
            'userId' => $userId,
        ]);
    }
    
    /**
     * Download rekap lembur sebagai PDF.
     */
    public function downloadPdf(Request $request)
    {
        $tanggal = $request->input('tanggal');
        $divisi = $request->input('divisi');
        $userId = $request->input('user_id');

        $query = Lembur::with('user');
        
        if ($divisi) {
            $query->whereHas('user', function ($q) use ($divisi) {
                $q->where('divisi', $divisi);
            });
        }

        if ($userId) {
            $query->where('user_id', $userId);
        }
        
        if ($tanggal) {
            $query->whereDate('tanggal', $tanggal);
        }

        $lemburRecords = $query->latest('tanggal')->get();

        $dateLabel = $tanggal ? Carbon::parse($tanggal)->isoFormat('D MMMM YYYY') : 'Semua Periode';
        $dateForDays = $tanggal ? Carbon::parse($tanggal) : now(); 

        $pdf = PDF::loadView('admin.exports.pdf.lembur.harian', compact('lemburRecords', 'dateForDays', 'dateLabel'));

        $filename = 'rekap_lembur_'. ($tanggal ? $tanggal : 'all') .'.pdf';
        return $pdf->download($filename);
    }

    /**
     * Halaman baru: Rekap Lembur Bulanan (matriks kalender per karyawan).
     * Filter: bulan + tahun + divisi + karyawan. Terpisah dari rekap absensi.
     */
    public function rekap(Request $request)
    {
        $month = intval($request->input('month', now()->month));
        $year = intval($request->input('year', now()->year));
        $divisi = $request->input('divisi');
        $userId = $request->input('user_id');
        // Landing default: section harian (matriks via tab Rekap Bulanan).
        $tab = $request->input('tab', 'harian') === 'rekap' ? 'rekap' : 'harian';

        $data = $this->getRekapLemburData($month, $year, $divisi, $userId);

        // Navigasi minggu untuk matriks web agar muat 1 layar (maks 7 kolom tanggal).
        $weeksWeb = $this->monthWeeks($data['allDates']);
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

        $lemburHarian = null;
        if ($tab === 'harian') {
            $lemburHarian = $this->getLemburHarianList($data['startDate'], $data['endDate'], $divisi, $userId);
        }

        $months = collect(range(1, 12))->mapWithKeys(function ($bulan) {
            return [$bulan => Carbon::create()->month($bulan)->translatedFormat('F')];
        });
        $years = range(now()->year, now()->year - 5);

        return view('admin.lembur.rekap', array_merge($data, [
            'title' => 'Rekap Lembur Bulanan',
            'months' => $months,
            'years' => $years,
            'month' => $month,
            'year' => $year,
            'divisi' => $divisi,
            'userId' => $userId,
            'tab' => $tab,
            'lemburHarian' => $lemburHarian,
            'weeksWeb' => $weeksWeb,
            'minggu' => $minggu,
            'weekDates' => $weeksWeb[$minggu - 1]['dates'] ?? [],
        ]));
    }

    /**
     * Daftar transaksi lembur harian dalam periode (untuk tab Harian di halaman rekap).
     * Tetap di route yang sama agar sidebar tidak berpindah halaman.
     */
    private function getLemburHarianList(string $startDate, string $endDate, $divisi = null, $userId = null)
    {
        $query = Lembur::with('user')->whereBetween('tanggal', [$startDate, $endDate]);

        if ($divisi) {
            $query->whereHas('user', function ($q) use ($divisi) {
                $q->where('divisi', $divisi);
            });
        }
        if ($userId) {
            $query->where('user_id', $userId);
        }

        $paginator = $query->orderBy('tanggal', 'desc')->orderBy('jam_masuk_lembur', 'desc')
            ->paginate(15)->withQueryString();

        $paginator->getCollection()->transform(function ($record) {
            $record->info = $this->buildLemburCell($record);
            return $record;
        });

        return $paginator;
    }

    public static function formatMenit(int $menit): string
    {
        return floor($menit / 60) . ' Jam ' . ($menit % 60) . ' Menit';
    }

    public function downloadRekapPdf(Request $request)
    {
        $month = intval($request->input('month', now()->month));
        $year = intval($request->input('year', now()->year));
        $divisi = $request->input('divisi');
        $userId = $request->input('user_id');

        $data = $this->getRekapLemburData($month, $year, $divisi, $userId);
        $data['weeks'] = $this->buildWeeklyGroups($data['rekapData'], $data['allDates']);
        $data['divisi'] = $divisi;
        $data['karyawanNama'] = $userId ? (User::find($userId)->name ?? null) : null;

        $pdf = PDF::loadView('admin.exports.pdf.lembur.rekap', $data);
        $filename = sprintf('rekap_lembur_%04d_%02d.pdf', $year, $month);

        return $pdf->download($filename);
    }

    public function downloadRekapExcel(Request $request)
    {
        $month = intval($request->input('month', now()->month));
        $year = intval($request->input('year', now()->year));
        $divisi = $request->input('divisi');
        $userId = $request->input('user_id');

        $data = $this->getRekapLemburData($month, $year, $divisi, $userId);

        $filename = sprintf('rekap-lembur-%04d-%02d.xlsx', $year, $month);

        return Excel::download(
            new RekapLemburExport($data['rekapData'], $data['allDates'], $month, $year, $data['holidays']),
            $filename
        );
    }

    private function getRekapLemburData(int $month, int $year, $divisi = null, $userId = null): array
    {
        $startDate = Carbon::create($year, $month, 1, 0, 0, 0, 'Asia/Jakarta')->startOfMonth();
        $endDate = (clone $startDate)->endOfMonth();
        $startDateStr = $startDate->toDateString();
        $endDateStr = $endDate->toDateString();

        $karyawanList = Cache::rememberForever('karyawan_list_dropdown_v2', function () {
            return User::where('role', 'user')->where('email', '!=', 'test@gmail.com')->orderBy('name')->get(['id', 'name', 'divisi', 'jabatan']);
        });
        $divisions = $karyawanList->pluck('divisi')->filter()->unique()->values();
        $usersList = $karyawanList;

        $queryUsers = User::where('role', 'user')->where('email', '!=', 'test@gmail.com');
        if ($divisi) $queryUsers->where('divisi', $divisi);
        if ($userId) $queryUsers->where('id', $userId);
        $users = $queryUsers->orderBy('name', 'asc')->get();

        $allDates = collect(CarbonPeriod::create($startDateStr, $endDateStr));

        $holidays = Holiday::whereBetween('tanggal', [$startDateStr, $endDateStr])
            ->get()
            ->mapWithKeys(function ($item) {
                return [Carbon::parse($item->tanggal)->format('Y-m-d') => $item->keterangan];
            })
            ->toArray();

        $userIds = $users->pluck('id')->toArray();
        $allLembur = Lembur::whereIn('user_id', $userIds)
            ->whereBetween('tanggal', [$startDateStr, $endDateStr])
            ->get()
            ->groupBy('user_id');

        $rekapData = [];
        foreach ($users as $user) {
            $lemburRecords = collect($allLembur->get($user->id, []))
                ->keyBy(function ($item) {
                    return Carbon::parse($item->tanggal)->format('Y-m-d');
                });

            $daily = [];
            $totalHari = 0;
            $totalMenit = 0;

            foreach ($allDates as $date) {
                $dateString = $date->toDateString();
                $record = $lemburRecords->get($dateString);

                if (!$record) {
                    $daily[$dateString] = null;
                    continue;
                }

                $cell = $this->buildLemburCell($record);
                $daily[$dateString] = $cell;
                $totalHari++;
                $totalMenit += $cell['menit'];
            }

            $rekapData[] = [
                'user' => $user,
                'daily' => $daily,
                'summary' => [
                    'total_hari' => $totalHari,
                    'total_menit' => $totalMenit,
                    'total_formatted' => floor($totalMenit / 60) . ' Jam ' . ($totalMenit % 60) . ' Menit',
                ],
            ];
        }

        return [
            'rekapData' => $rekapData,
            'allDates' => $allDates,
            'divisions' => $divisions,
            'usersList' => $usersList,
            'holidays' => $holidays,
            'month' => $month,
            'year' => $year,
            'startDate' => $startDateStr,
            'endDate' => $endDateStr,
        ];
    }

    /**
     * Kelompokkan tanggal sebulan ke minggu-minggu kalender ISO (Senin-Minggu),
     * dipotong mengikuti batas bulan.
     */
    private function monthWeeks($allDates): array
    {
        return \App\Services\AttendanceService::monthWeeks($allDates);
    }

    /**
     * Kelompokkan data rekap per minggu untuk PDF. Tiap minggu hanya memuat
     * karyawan yang punya lembur di minggu itu agar tabel tidak penuh baris kosong.
     */
    private function buildWeeklyGroups(array $rekapData, $allDates): array
    {
        $weeks = [];
        foreach ($this->monthWeeks($allDates) as $week) {
            $dates = $week['dates'];
            $label = $week['label'];

            $rows = [];
            $notes = [];
            foreach ($rekapData as $r) {
                $cells = [];
                $hari = 0;
                $menit = 0;
                foreach ($dates as $dt) {
                    $cell = $r['daily'][$dt->toDateString()] ?? null;
                    $cells[$dt->toDateString()] = $cell;
                    if ($cell) {
                        $hari++;
                        $menit += $cell['menit'];
                        if (!empty($cell['keterangan'])) {
                            $notes[] = [
                                'tanggal' => $dt,
                                'user_name' => $r['user']->name ?? 'User Dihapus',
                                'keterangan' => $cell['keterangan'],
                            ];
                        }
                    }
                }
                if ($hari > 0) {
                    $rows[] = [
                        'user' => $r['user'],
                        'cells' => $cells,
                        'total_hari' => $hari,
                        'total_menit' => $menit,
                        'total_formatted' => self::formatMenit($menit),
                    ];
                }
            }

            $weeks[] = [
                'label' => $label,
                'dates' => $dates,
                'dates7' => \App\Services\AttendanceService::padWeekToSeven($dates),
                'rows' => $rows,
                'notes' => $notes,
            ];
        }

        return $weeks;
    }

    /**
     * Hitung durasi lembur selisih biasa (jam_keluar - jam_masuk).
     * Sesuai kesepakatan: jam_keluar wajib, data aneh ditandai (tidak auto +1 hari).
     */
    private function buildLemburCell($record): array
    {
        $jamMasukRaw = $record->jam_masuk_lembur ? substr($record->jam_masuk_lembur, 0, 5) : null;
        $jamKeluarRaw = $record->jam_keluar_lembur ? substr($record->jam_keluar_lembur, 0, 5) : null;
        $keterangan = trim((string) ($record->keterangan ?? ''));

        $label = ($jamMasukRaw ? str_replace(':', '.', $jamMasukRaw) : '?')
            . ' - '
            . ($jamKeluarRaw ? str_replace(':', '.', $jamKeluarRaw) : '?');

        if (!$jamMasukRaw || !$jamKeluarRaw) {
            return ['label' => $label, 'menit' => 0, 'status' => 'incomplete', 'durasi' => 'Tidak Absen Keluar', 'durasi_full' => 'Tidak Absen Keluar', 'keterangan' => $keterangan];
        }

        try {
            $masuk = Carbon::createFromFormat('H:i', $jamMasukRaw, 'Asia/Jakarta');
            $keluar = Carbon::createFromFormat('H:i', $jamKeluarRaw, 'Asia/Jakarta');
        } catch (\Exception $e) {
            return ['label' => $label, 'menit' => 0, 'status' => 'invalid', 'durasi' => 'Jam tidak valid', 'durasi_full' => 'Jam tidak valid', 'keterangan' => $keterangan];
        }

        if ($keluar->lt($masuk)) {
            return ['label' => $label, 'menit' => 0, 'status' => 'invalid', 'durasi' => 'Cek jam keluar', 'durasi_full' => 'Cek jam keluar', 'keterangan' => $keterangan];
        }

        $menit = $masuk->diffInMinutes($keluar);

        return [
            'label' => $label,
            'menit' => $menit,
            'status' => 'ok',
            'durasi' => floor($menit / 60) . 'J ' . ($menit % 60) . 'M',
            'durasi_full' => self::formatMenit($menit),
            'keterangan' => $keterangan,
        ];
    }
}
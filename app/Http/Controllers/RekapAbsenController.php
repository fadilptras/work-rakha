<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\Cuti;
use App\Models\Lembur;
use App\Models\Aktivitas;
use App\Models\Holiday; // Pastikan Model Holiday di-use
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\CarbonPeriod;
use App\Models\User;

class RekapAbsenController extends Controller
{
    public function index(Request $request)
    {
        $title = 'Rekap Absensi Saya';
        $user = Auth::user();

        $bulanDipilih = $request->input('bulan', now()->month);
        $tahunDipilih = $request->input('tahun', now()->year);
        
        $startDate = Carbon::create($tahunDipilih, $bulanDipilih, 1)->startOfMonth();
        $endDate = Carbon::create($tahunDipilih, $bulanDipilih, 1)->endOfMonth();

        // 1. Ambil Data Absensi
        $absensiDalamPeriode = Absensi::where('user_id', $user->id)
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->get()
            ->keyBy(fn($item) => Carbon::parse($item->tanggal)->toDateString());


        // 3. Ambil Data Lembur
        $lemburDalamPeriode = Lembur::where('user_id', $user->id)
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->whereNotNull('jam_keluar_lembur')
            ->get()
            ->keyBy(fn($item) => Carbon::parse($item->tanggal)->toDateString());

        // 4. Ambil Data Aktivitas
        $aktivitasDalamPeriode = Aktivitas::where('user_id', $user->id)
            ->whereBetween('created_at', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->selectRaw('DATE(created_at) as date, count(*) as total')
            ->groupBy('date')
            ->get()
            ->keyBy('date');

        // 5. Ambil Data Hari Libur
        $holidays = Holiday::whereBetween('tanggal', [$startDate, $endDate])
            ->get()
            ->keyBy(fn($item) => Carbon::parse($item->tanggal)->toDateString());
            
        // 6. Inisialisasi Rekap
        $rekap = [
            'hadir' => 0,
            'sakit' => 0,
            'izin'  => 0,
            'cuti'  => 0,
            'alpa'  => 0,
            'lembur' => 0,
            'terlambat' => 0 
        ];
        
        $standardWorkHour = Carbon::createFromTime(8, 0, 0, 'Asia/Jakarta');
        
        $detailHarian = [];
        $period = CarbonPeriod::create($startDate, $endDate);

        foreach ($period as $date) {
            $tanggalFormatted = $date->toDateString();
            
            $recordAbsensi   = $absensiDalamPeriode->get($tanggalFormatted);
            $recordLembur    = $lemburDalamPeriode->get($tanggalFormatted);
            $recordAktivitas = $aktivitasDalamPeriode->get($tanggalFormatted);
            $holidayData     = $holidays->get($tanggalFormatted); 
            
            $holidayString = $holidayData ? $holidayData->keterangan : null;
            
            $dailyStatus = \App\Services\AttendanceService::calculateDailyStatus($date, $recordAbsensi, $recordLembur, $holidayString);

            // Update Rekap Total
            if ($dailyStatus->status_key === 'H' || strpos($dailyStatus->status_key, 'H ') === 0) $rekap['hadir']++;
            if ($dailyStatus->status_key === 'S' || strpos($dailyStatus->status_key, 'S ') === 0) $rekap['sakit']++;
            if ($dailyStatus->status_key === 'I' || strpos($dailyStatus->status_key, 'I ') === 0) $rekap['izin']++;
            if ($dailyStatus->status_key === 'C' || strpos($dailyStatus->status_key, 'C ') === 0) $rekap['cuti']++;
            if ($dailyStatus->status_key === 'A' || strpos($dailyStatus->status_key, 'A ') === 0) $rekap['alpa']++;
            if (strpos($dailyStatus->status_key, 'L') !== false) $rekap['lembur']++;
            
            $rekap['terlambat'] += $dailyStatus->terlambat_menit;

            $statusTampil = strtolower($dailyStatus->status_teks);
            if ($statusTampil === 'alpa') $statusTampil = 'alpa'; // menjaga huruf kecil

            $dailyData = [
                'tanggal' => $date,
                'status' => $statusTampil,
                'jam_masuk' => $dailyStatus->jam_masuk,
                'jam_keluar' => $dailyStatus->jam_keluar,
                'keterangan' => $dailyStatus->keterangan,
                'is_weekend' => $dailyStatus->is_libur_total,
                'jumlah_aktivitas' => $recordAktivitas ? $recordAktivitas->total : 0
            ];
            
            $detailHarian[] = (object)$dailyData;
        }
        
        // Format Tampilan Terlambat
        $totalMenitTerlambat = $rekap['terlambat'];
        $jamTerlambat = floor($totalMenitTerlambat / 60);
        $menitTerlambat = $totalMenitTerlambat % 60;
        $rekap['terlambat_formatted'] = $jamTerlambat . ' Jam ' . $menitTerlambat . ' Menit';

        $daftarBulan = collect(range(1, 12))->mapWithKeys(function ($bulan) {
            return [$bulan => Carbon::create()->month($bulan)->translatedFormat('F')];
        });
        $daftarTahun = range(now()->year, now()->year - 5);

        return view('users.absensi.rekap-absen-user', compact(
            'title',
            'detailHarian',
            'rekap',
            'bulanDipilih',
            'tahunDipilih',
            'daftarBulan',
            'daftarTahun'
        ));
    }
}
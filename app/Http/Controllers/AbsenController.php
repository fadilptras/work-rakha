<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\User;
use App\Models\Cuti;
use App\Models\Lembur;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\CarbonPeriod;
use Jenssegers\Agent\Agent;
use App\Notifications\AbsensiNotification;
use App\Models\Holiday;

class AbsenController extends Controller
{
    public function absen()
    {
        $title = 'Form Absensi';
        $user = Auth::user();
        $today = Carbon::today();
        
        // 1. Cek Hari Minggu
        $isSunday = $today->isSunday();

        // 2. Cek Sabtu
        $isSaturday = $today->isSaturday();

        // 3. Cek Libur Nasional dari DB
        $holidayDb = Holiday::whereDate('tanggal', $today)->first();

        // Kategori "Hari Libur" (Minggu ATAU Tanggal Merah)
        $isHoliday = $isSunday || $holidayDb;

        // Kategori "Sabtu Masuk Opsional" (Sabtu DAN BUKAN Tanggal Merah)
        $isSaturdayOpen = $isSaturday && !$holidayDb;

        $yesterday = Carbon::yesterday();
        
        $unfinishedAbsensi = Absensi::where('user_id', $user->id)
            ->where('tanggal', $yesterday->toDateString())
            ->whereNotNull('jam_masuk')
            ->whereNull('jam_keluar')
            ->first();

        $absensiHariIni = Absensi::where('user_id', $user->id)
            ->where('tanggal', $today->toDateString())
            ->first();

        $lemburHariIni = Lembur::where('user_id', $user->id)
            ->where('tanggal', $today->toDateString())
            ->first();

        // Asumsi function helper ada di controller ini
        $rekapAbsen = $this->rekapAbsensiBulanan($user, $today);
        $daftarRekan = $this->getDaftarRekan($user, $today);

        // Masukkan variable ke compact
        $data = compact(
            'title', 
            'user', 
            'unfinishedAbsensi', 
            'absensiHariIni', 
            'lemburHariIni', 
            'rekapAbsen', 
            'daftarRekan',
            'isHoliday',      // True jika Minggu/Libur Nasional
            'isSaturdayOpen', // True jika Sabtu biasa
            'holidayDb' 
        );

        return view('users.absensi.absen-user', $data);
    }

    protected function rekapAbsensiBulanan(User $user, Carbon $date): array
    {
        $startDate = $date->copy()->startOfMonth();
        $endDate = $date->copy()->endOfMonth();

        $absensiDalamPeriode = Absensi::where('user_id', $user->id)
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->get()
            ->keyBy(fn($item) => Carbon::parse($item->tanggal)->toDateString());

        $lemburDalamPeriode = Lembur::where('user_id', $user->id)
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->whereNotNull('jam_keluar_lembur')
            ->get()
            ->keyBy(fn($item) => Carbon::parse($item->tanggal)->toDateString());
            
        $holidays = Holiday::whereBetween('tanggal', [$startDate, $endDate])
            ->get()
            ->mapWithKeys(function ($item) {
                return [Carbon::parse($item->tanggal)->format('Y-m-d') => $item->keterangan];
            })
            ->toArray();

        $rekap = [
            'hadir' => 0,
            'sakit' => 0,
            'izin'  => 0,
            'cuti'  => 0,
            'tidak hadir' => 0, 
            'lembur' => 0,
            'terlambat' => 0,
            'total_menit_kerja' => 0
        ];
        
        $period = CarbonPeriod::create($startDate, $endDate);

        foreach ($period as $day) {
            $tanggalFormatted = $day->toDateString();
            $recordAbsensi = $absensiDalamPeriode->get($tanggalFormatted);
            $recordLembur = $lemburDalamPeriode->get($tanggalFormatted);
            $holidayString = $holidays[$tanggalFormatted] ?? null;

            $dailyStatus = \App\Services\AttendanceService::calculateDailyStatus($day, $recordAbsensi, $recordLembur, $holidayString);

            if ($dailyStatus->status_key === 'H' || strpos($dailyStatus->status_key, 'H ') === 0) $rekap['hadir']++;
            if ($dailyStatus->status_key === 'S' || strpos($dailyStatus->status_key, 'S ') === 0) $rekap['sakit']++;
            if ($dailyStatus->status_key === 'I' || strpos($dailyStatus->status_key, 'I ') === 0) $rekap['izin']++;
            if ($dailyStatus->status_key === 'C' || strpos($dailyStatus->status_key, 'C ') === 0) $rekap['cuti']++;
            if ($dailyStatus->status_key === 'A' || strpos($dailyStatus->status_key, 'A ') === 0) $rekap['tidak hadir']++;
            
            if (strpos($dailyStatus->status_key, 'L') !== false) {
                $rekap['lembur']++;
            }
            
            $rekap['terlambat'] += $dailyStatus->terlambat_menit;
            $rekap['total_menit_kerja'] += $dailyStatus->kerja_menit;
        }
        
        $totalMenitTerlambat = $rekap['terlambat'];
        $jamTerlambat = floor($totalMenitTerlambat / 60);
        $menitTerlambat = $totalMenitTerlambat % 60;
        $rekap['terlambat'] = $jamTerlambat . ' Jam ' . $menitTerlambat . ' Menit';

        return $rekap;
    }

    protected function getDaftarRekan(User $user, Carbon $date): array
    {
        $daftarRekan = [];
        $jabatanUser = $user->jabatan;

        // Logika pengambilan user diperbaiki di sini
        if (in_array($jabatanUser, ['HRD', 'Manajer', 'Direktur'])) {
            // Ambil semua user KECUALI diri sendiri DAN harus role 'user'
            $rekanDilihat = User::where('id', '!=', $user->id)
                ->where('role', 'user') 
                ->get();
        } else if ($user->divisi) {
            // Ambil satu divisi KECUALI diri sendiri DAN harus role 'user'
            $rekanDilihat = User::where('divisi', $user->divisi)
                ->where('id', '!=', $user->id)
                ->where('role', 'user')
                ->get();
        } else {
            $rekanDilihat = collect();
        }

        if ($rekanDilihat->isNotEmpty()) {
            $absensiRekanHariIni = Absensi::whereIn('user_id', $rekanDilihat->pluck('id'))
                ->where('tanggal', $date->toDateString())
                ->get()
                ->keyBy('user_id');

            foreach ($rekanDilihat as $rekan) {
                $statusAbsensi = $absensiRekanHariIni->get($rekan->id);
                $daftarRekan[] = (object) [
                    'user'   => $rekan,
                    'status' => $statusAbsensi ? $statusAbsensi->status : 'Belum Absen'
                ];
            }
        }

        return $daftarRekan;
    }

    public function store(Request $request)
    {
        $request->validate([
            'status'     => 'required|in:hadir,sakit,izin',
            'keterangan' => 'nullable|string|max:1000',
            'lampiran'   => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'latitude'   => 'nullable|string|max:255',
            'longitude'  => 'nullable|string|max:255',
        ]);

        if (Absensi::where('user_id', Auth::id())->where('tanggal', today()->toDateString())->exists()) {
            return redirect()->route('absen')->with('error', 'Anda sudah melakukan absensi hari ini.');
        }

        $absensiKemarin = Absensi::where('user_id', Auth::id())
            ->where('tanggal', today()->subDay()->toDateString())
            ->where('status', 'hadir')
            ->first();

        if ($absensiKemarin && is_null($absensiKemarin->jam_keluar)) {
            return redirect()->route('absen')->with('error', 'Anda belum melakukan absen keluar pada hari kerja sebelumnya.');
        }

        if (in_array($request->status, ['sakit', 'izin'])) {
            if (!$request->filled('keterangan') && !$request->hasFile('lampiran')) {
                return redirect()->back()->withInput()
                    ->withErrors(['keterangan' => 'Untuk Sakit/Izin, wajib isi keterangan atau lampiran.']);
            }
        }
        
        if ($request->status === 'hadir' && (!$request->latitude || (!$request->longitude))) {
            return redirect()->back()->with('error', 'Lokasi GPS wajib diaktifkan untuk absen hadir.');
        }

        $pathLampiran = null;
        if ($request->hasFile('lampiran')) {
            $pathLampiran = $request->file('lampiran')->store('lampiran_absensi', 'public');
        }

        $inputStatus = $request->status;
        $jamMasuk = now();
        $notPresentThreshold = Carbon::parse('23:59:00', 'Asia/Jakarta');
        $standardWorkHour = Carbon::parse('08:00:00', 'Asia/Jakarta');
        $keterangan = $request->keterangan;

        if ($inputStatus === 'hadir') {
            if ($jamMasuk->gt($notPresentThreshold)) {
                $inputStatus = 'tidak hadir';
                $keterangan = null;
            }
            else if ($jamMasuk->gt($standardWorkHour) && !today()->isSaturday()) {
                $diffInMinutes = abs($jamMasuk->diffInMinutes($standardWorkHour));
                $jamTerlambat = floor($diffInMinutes / 60);
                $menitTerlambat = $diffInMinutes % 60;
                $keteranganTerlambat = "Terlambat {$jamTerlambat} Jam {$menitTerlambat} Menit.";
                $keterangan = trim($keteranganTerlambat . ' ' . ($keterangan ? 'Keterangan: ' . $keterangan : ''));
            }
        }

        $absensi = Absensi::create([
            'user_id'   => Auth::id(),
            'tanggal'   => today()->toDateString(),
            'jam_masuk' => $jamMasuk->toTimeString(),
            'status'    => $inputStatus,
            'keterangan'=> $keterangan,
            'lampiran'  => $pathLampiran,
            'latitude'  => $request->latitude,
            'longitude' => $request->longitude,
        ]);

        $user = Auth::user();
        if ($inputStatus === 'hadir' || $inputStatus === 'terlambat') {
            $user->notify(new AbsensiNotification($absensi, 'masuk'));
        } elseif ($inputStatus === 'izin') {
            $user->notify(new AbsensiNotification($absensi, 'izin'));
        } elseif ($inputStatus === 'sakit') {
            $user->notify(new AbsensiNotification($absensi, 'sakit'));
        }

        if ($inputStatus === 'tidak hadir') {
            return redirect()->route('absen')->with('error', 'Absensi masuk Anda melewati batas waktu. Status Anda otomatis menjadi Tidak Hadir.');
        }

        return redirect()->route('absen')->with('success', 'Absensi berhasil direkam!');
    }

    public function updateKeluar(Request $request, Absensi $absensi)
    {
        $request->validate([
            'lampiran_keluar'    => 'required|file|mimes:jpg,jpeg,png|max:2048',
            'latitude_keluar'    => 'required|string',
            'longitude_keluar'   => 'required|string',
        ]);

        if ($absensi->user_id !== Auth::id()) {
            return back()->with('error', 'Aksi tidak diizinkan.');
        }

        $pathLampiranKeluar = null;
        if ($request->hasFile('lampiran_keluar')) {
            $pathLampiranKeluar = $request->file('lampiran_keluar')->store('lampiran_absensi_keluar', 'public');
        }

        $absensi->update([
            'jam_keluar'        => now()->toTimeString(),
            'tanggal_keluar'    => now()->toDateString(), // Mengisi kolom baru
            'lampiran_keluar'   => $pathLampiranKeluar,
            'latitude_keluar'   => $request->latitude_keluar,
            'longitude_keluar'  => $request->longitude_keluar,
        ]);

        // Perbaikan: menembak dari Auth::user() agar menghindari error jika relasi terputus
        Auth::user()->notify(new AbsensiNotification($absensi, 'keluar'));

        return redirect()->route('absen')->with('success', 'Absensi keluar berhasil direkam!');
    }

    public function storeLembur(Request $request)
    {
        $request->validate([
            'keterangan' => 'required|string|max:1000',
            'lampiran_masuk'    => 'required|file|mimes:jpg,jpeg,png|max:2048',
            'latitude_masuk'    => 'required|string|max:255',
            'longitude_masuk'   => 'required|string|max:255',
        ]);
    
        $user = Auth::user();
        $todayCarbon = Carbon::today();
        $todayString = $todayCarbon->toDateString();
    
        $absensiHariIni = Absensi::where('user_id', $user->id)
                                         ->where('tanggal', $todayString)
                                         ->first();
    
        // 1. TAMBAHKAN PENGECEKAN HARI LIBUR NASIONAL DI SINI
        $isHolidayDb = \App\Models\Holiday::whereDate('tanggal', $todayString)->exists();
    
        // 2. PERBAIKI LOGIKA IF INI
        // Jika BUKAN weekend DAN BUKAN libur nasional, barulah paksa cek absen reguler
        if (!$todayCarbon->isWeekend() && !$isHolidayDb) {
            if (!$absensiHariIni || !$absensiHariIni->jam_keluar) {
                return redirect()->route('absen')->with('error', 'Anda harus absen pulang terlebih dahulu untuk memulai lembur.');
            }
        }
    
        if (Lembur::where('user_id', $user->id)->where('tanggal', $todayString)->exists()) {
           return redirect()->route('absen')->with('error', 'Anda sudah melakukan absensi lembur masuk hari ini.');
        }
    
        $pathLampiranMasuk = null;
        if ($request->hasFile('lampiran_masuk')) {
            $pathLampiranMasuk = $request->file('lampiran_masuk')->store('lampiran_lembur', 'public');
        }
    
        $lembur = Lembur::create([
            'user_id'   => $user->id,
            'tanggal'   => $todayString,
            'jam_masuk_lembur' => now()->toTimeString(),
            'keterangan'=> $request->keterangan,
            'lampiran_masuk'   => $pathLampiranMasuk,
            'latitude_masuk'   => $request->latitude_masuk,
            'longitude_masuk'  => $request->longitude_masuk,
        ]);
    
        $user->notify(new \App\Notifications\AbsensiNotification($lembur, 'lembur_masuk'));
    
        return redirect()->route('absen')->with('success', 'Absensi lembur masuk berhasil direkam!');
    }

    public function updateLemburKeluar(Request $request, Lembur $lembur)
    {
        $request->validate([
            'lampiran_keluar'    => 'required|file|mimes:jpg,jpeg,png|max:2048',
            'latitude_keluar'    => 'required|string',
            'longitude_keluar'   => 'required|string',
        ]);
    
        if ($lembur->user_id !== Auth::id()) {
            return redirect()->route('absen')->with('error', 'Aksi tidak diizinkan.');
        }
        
        if ($lembur->jam_keluar_lembur) {
            return redirect()->route('absen')->with('error', 'Anda sudah melakukan absensi keluar lembur.');
        }
        
        $pathLampiranKeluar = null;
        if ($request->hasFile('lampiran_keluar')) {
            $pathLampiranKeluar = $request->file('lampiran_keluar')->store('lampiran_lembur_keluar', 'public');
        }
    
        $lembur->update([
            'jam_keluar_lembur' => now()->toTimeString(),
            'lampiran_keluar'    => $pathLampiranKeluar,
            'latitude_keluar'    => $request->latitude_keluar,
            'longitude_keluar'   => $request->longitude_keluar,
        ]);

        // Perbaikan: sama seperti absen biasa, menggunakan Auth::user() langsung
        Auth::user()->notify(new AbsensiNotification($lembur, 'lembur_keluar'));
    
        return redirect()->route('absen')->with('success', 'Absen keluar lembur berhasil direkam. Terima kasih!');
    }
}
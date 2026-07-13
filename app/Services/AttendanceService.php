<?php

namespace App\Services;

use Carbon\Carbon;

class AttendanceService
{
    /**
     * Menghitung status harian absensi secara sentral.
     */
    public static function calculateDailyStatus(Carbon $date, $recordAbsensi, $recordLembur, $holidayString = null)
    {
        $standardWorkHour = Carbon::createFromTime(8, 0, 0, 'Asia/Jakarta');
        
        $isWeekend = $date->isWeekend();
        $isHoliday = !empty($holidayString);
        $isLiburTotal = $isWeekend || $isHoliday;
        
        $statusKey = '-';
        $statusTeks = '-';
        $keterangan = null;
        $keterlambatan = 0;
        $totalMenitKerja = 0;
        $jamMasuk = null;
        $jamKeluar = null;

        if ($recordAbsensi) {
            $statusTeks = ucfirst($recordAbsensi->status);
            $statusKey = strtoupper(substr($recordAbsensi->status, 0, 1));
            if (strtolower($recordAbsensi->status) === 'cuti') {
                $statusKey = 'C';
                $statusTeks = 'Cuti';
            }
            if (strtolower($recordAbsensi->status) === 'tidak hadir') {
                $statusKey = 'A';
                $statusTeks = 'Alpa';
            }
            
            $keterangan = $recordAbsensi->keterangan;
            $jamMasuk = $recordAbsensi->jam_masuk;
            $jamKeluar = $recordAbsensi->jam_keluar;

            if (strtolower($recordAbsensi->status) === 'hadir' && $recordAbsensi->jam_masuk) {
                $waktuMasuk = Carbon::parse($recordAbsensi->jam_masuk, 'Asia/Jakarta');
                
                if ($waktuMasuk->gt($standardWorkHour)) {
                    $keterlambatan = abs($waktuMasuk->diffInMinutes($standardWorkHour));
                }
                
                if ($recordAbsensi->jam_keluar) {
                    $tglKeluar = $recordAbsensi->tanggal_keluar ?? $recordAbsensi->tanggal;
                    $wktMasukFull = Carbon::parse($recordAbsensi->tanggal . ' ' . $recordAbsensi->jam_masuk);
                    $wktKeluarFull = Carbon::parse($tglKeluar . ' ' . $recordAbsensi->jam_keluar);

                    if (is_null($recordAbsensi->tanggal_keluar) && $wktKeluarFull->lt($wktMasukFull)) {
                        $wktKeluarFull->addDay();
                    }

                    $totalMenitKerja = $wktMasukFull->diffInMinutes($wktKeluarFull);
                }
            }
        }
        
        // Logika Override Libur (Jika tidak ada record Absensi/Cuti)
        if (!$recordAbsensi && $isLiburTotal) {
            $statusKey = 'Libur';
            $statusTeks = 'Libur';
            $keterangan = $isHoliday ? 'Libur Nasional: ' . $holidayString : 'Akhir Pekan';
        }
        
        // Logika Override Alpa (Jika hari kerja sudah lewat dan tidak ada record absen/libur)
        if (!$recordAbsensi && !$isLiburTotal) {
            if ($date->lt(now()->startOfDay())) {
                $statusKey = 'A';
                $statusTeks = 'Alpa';
                $keterangan = 'Tanpa Keterangan';
            }
        }
        
        // Gabungkan Logika Lembur
        if ($recordLembur) {
            if ($statusKey === '-' || $statusKey === 'Libur' || $statusKey === 'A') {
                $statusKey = 'L';
                $statusTeks = 'Lembur';
                $keterangan = $recordLembur->keterangan ?: 'Lembur';
                $jamMasuk = $recordLembur->jam_masuk_lembur;
                $jamKeluar = $recordLembur->jam_keluar_lembur;
            } else {
                $statusKey .= ' L';
                $ketLembur = '(Lembur: ' . ($recordLembur->jam_masuk_lembur ? substr($recordLembur->jam_masuk_lembur, 0, 5) : '?') . ' - ' . ($recordLembur->jam_keluar_lembur ? substr($recordLembur->jam_keluar_lembur, 0, 5) : '?') . ')';
                $keterangan = $keterangan && $keterangan !== '-' ? $keterangan . '. ' . $ketLembur : $ketLembur;
            }
        }
        
        return (object)[
            'status_key' => $statusKey,
            'status_teks' => $statusTeks,
            'keterangan' => $keterangan ?: '-',
            'jam_masuk' => $jamMasuk,
            'jam_keluar' => $jamKeluar,
            'terlambat_menit' => $keterlambatan,
            'kerja_menit' => $totalMenitKerja,
            'is_weekend' => $isWeekend,
            'is_holiday' => $isHoliday,
            'is_libur_total' => $isLiburTotal,
        ];
    }
}

<?php

namespace App\Services;

use Carbon\Carbon;

class AttendanceService
{
    /**
     * Menghitung status harian absensi secara sentral.
     */
    public static function calculateDailyStatus(Carbon $date, $recordAbsensi, $recordLembur, $holidayString = null, $user = null)
    {
        if ($user && ($user->email ?? null) === 'test@gmail.com') {
            return (object)[
                'status_key' => '-',
                'status_teks' => '-',
                'keterangan' => 'Akun Testing',
                'jam_masuk' => null,
                'jam_keluar' => null,
                'terlambat_menit' => 0,
                'kerja_menit' => 0,
                'is_weekend' => $date->isWeekend(),
                'is_holiday' => !empty($holidayString),
                'is_libur_total' => $date->isWeekend() || !empty($holidayString),
            ];
        }

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
            
            $keterangan = $recordAbsensi->keterangan ?? null;
            $jamMasuk = $recordAbsensi->jam_masuk ?? null;
            $jamKeluar = $recordAbsensi->jam_keluar ?? null;

            // Jika hari sabtu atau minggu dan statusnya hadir, maka terhitung Lembur
            if (strtolower($recordAbsensi->status) === 'hadir' && $isWeekend) {
                $statusKey = 'L';
                $statusTeks = 'Lembur';
                $keterangan = $keterangan && $keterangan !== '-' ? $keterangan : 'Lembur Akhir Pekan';
            }

            if (strtolower($recordAbsensi->status) === 'hadir' && ($recordAbsensi->jam_masuk ?? null)) {
                $waktuMasuk = Carbon::parse($recordAbsensi->jam_masuk, 'Asia/Jakarta');
                
                // Hanya hitung keterlambatan di hari kerja biasa (bukan libur/weekend)
                if (!$isLiburTotal && $waktuMasuk->gt($standardWorkHour)) {
                    $keterlambatan = abs($waktuMasuk->diffInMinutes($standardWorkHour));
                }
                
                if ($recordAbsensi->jam_keluar ?? null) {
                    $tglKeluar = $recordAbsensi->tanggal_keluar ?? $recordAbsensi->tanggal ?? $date->toDateString();
                    $wktMasukFull = Carbon::parse(($recordAbsensi->tanggal ?? $date->toDateString()) . ' ' . $recordAbsensi->jam_masuk);
                    $wktKeluarFull = Carbon::parse($tglKeluar . ' ' . $recordAbsensi->jam_keluar);

                    if (is_null($recordAbsensi->tanggal_keluar ?? null) && $wktKeluarFull->lt($wktMasukFull)) {
                        $wktKeluarFull->addDay();
                    }

                    $totalMenitKerja = $wktMasukFull->diffInMinutes($wktKeluarFull);
                }
            }
        }
        
        $joinDate = ($user && isset($user->tanggal_bergabung)) ? Carbon::parse($user->tanggal_bergabung)->startOfDay() : null;

        // Logika Override Libur (Jika tidak ada record Absensi/Cuti)
        if (!$recordAbsensi && $isLiburTotal) {
            if ($joinDate && $date->lt($joinDate)) {
                $statusKey = '-';
                $statusTeks = '-';
                $keterangan = 'Belum Bergabung';
            } else {
                $statusKey = 'Libur';
                $statusTeks = 'Libur';
                $keterangan = $isHoliday ? 'Libur Nasional: ' . $holidayString : 'Akhir Pekan';
            }
        }
        
        // Logika Override Alpa (Jika hari kerja sudah lewat dan tidak ada record absen/libur)
        if (!$recordAbsensi && !$isLiburTotal) {
            if ($date->lt(now()->startOfDay())) {
                if ($joinDate && $date->lt($joinDate)) {
                    $statusKey = '-';
                    $statusTeks = '-';
                    $keterangan = 'Belum Bergabung';
                } elseif ($user && ($user->divisi ?? null) === 'Top Management') {
                    $statusKey = '-';
                    $statusTeks = '-';
                    $keterangan = 'Bebas Absen';
                } else {
                    $statusKey = 'A';
                    $statusTeks = 'Alpa';
                    $keterangan = 'Tanpa Keterangan';
                }
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
                if (!in_array('L', explode(' ', $statusKey))) {
                    $statusKey .= ' L';
                }
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

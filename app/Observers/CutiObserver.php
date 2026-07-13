<?php

namespace App\Observers;

use App\Models\Cuti;
use App\Models\Absensi;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\Log;

class CutiObserver
{
    /**
     * Handle the Cuti "updated" event.
     */
    public function updated(Cuti $cuti): void
    {
        if ($cuti->isDirty('status') && $cuti->status === 'disetujui') {
            try {
                $period = CarbonPeriod::create($cuti->tanggal_mulai, $cuti->tanggal_selesai);
                foreach ($period as $date) {
                    Absensi::updateOrCreate(
                        [
                            'user_id' => $cuti->user_id,
                            'tanggal' => $date->format('Y-m-d'),
                        ],
                        [
                            'status' => 'cuti',
                            'jam_masuk' => '07:00:00',
                            'jam_keluar' => '17:00:00',
                            'keterangan' => 'Cuti: ' . $cuti->jenis_cuti . ($cuti->alasan ? ' - ' . $cuti->alasan : '')
                        ]
                    );
                }
            } catch (\Exception $e) {
                Log::error('CutiObserver Error (updated): ' . $e->getMessage());
            }
        }
    }

    /**
     * Handle the Cuti "deleted" event.
     */
    public function deleted(Cuti $cuti): void
    {
        if ($cuti->status === 'disetujui') {
            try {
                Absensi::where('user_id', $cuti->user_id)
                    ->where('status', 'cuti')
                    ->whereBetween('tanggal', [$cuti->tanggal_mulai, $cuti->tanggal_selesai])
                    ->delete();
            } catch (\Exception $e) {
                Log::error('CutiObserver Error (deleted): ' . $e->getMessage());
            }
        }
    }
}

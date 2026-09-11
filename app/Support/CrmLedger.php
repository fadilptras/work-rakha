<?php

namespace App\Support;

use App\Models\ClientInteraction;
use App\Models\PengajuanDana;

/**
 * Satu-satunya pintu pencatatan USAGE (OUT) CRM yang berasal dari
 * Pengajuan Dana — dipakai baik oleh alur approve normal maupun
 * admin override (markAsPaid), supaya tidak ada pengajuan selesai
 * yang luput dari history/saldo klien.
 *
 * Aturan:
 *  - Hanya pengajuan yang rincian_dana[0]-nya membawa client_id
 *    (ciri khas pengajuan dari halaman klien) yang dicatat.
 *  - product_name mengikuti pola USAGE : {deskripsi}.
 *  - interaction_date = tanggal pemakaian dari form (fallback tanggal pengajuan).
 *  - amount = total_dana saat penyelesaian.
 */
class CrmLedger
{
    public static function recordUsageFromPengajuan(PengajuanDana $pengajuan): ?ClientInteraction
    {
        $rincian = $pengajuan->rincian_dana;
        if (! is_array($rincian) || count($rincian) === 0 || ! isset($rincian[0]['client_id'])) {
            return null;
        }

        return ClientInteraction::create([
            'client_id' => $rincian[0]['client_id'],
            'transaction_type' => 'OUT',
            'product_name' => 'USAGE : ' . $rincian[0]['deskripsi'],
            'interaction_date' => $rincian[0]['interaction_date'] ?? $rincian[0]['tanggal_interaksi'] ?? $pengajuan->created_at,
            'sales_amount' => 0,
            'amount' => $pengajuan->total_dana,
            'notes' => $rincian[0]['crm_notes'] ?? $rincian[0]['catatan_crm'] ?? '',
        ]);
    }
}

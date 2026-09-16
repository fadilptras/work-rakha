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

        $clientId = $rincian[0]['client_id'];
        $deskripsi = $rincian[0]['deskripsi'] ?? '';
        $productName = 'USAGE : ' . $deskripsi;
        $amount = $pengajuan->total_dana;

        // Idempotency: cegah double entry jika sudah pernah tercatat (misal approve dipanggil 2x atau markAsPaid dipanggil ulang)
        $exists = ClientInteraction::where('client_id', $clientId)
            ->where('transaction_type', 'OUT')
            ->where('product_name', $productName)
            ->where('amount', $amount)
            ->exists();
        if ($exists) {
            return null;
        }

        return ClientInteraction::create([
            'client_id' => $clientId,
            'transaction_type' => 'OUT',
            'product_name' => $productName,
            'interaction_date' => $rincian[0]['interaction_date'] ?? $rincian[0]['tanggal_interaksi'] ?? $pengajuan->created_at,
            'sales_amount' => 0,
            'amount' => $amount,
            'notes' => $rincian[0]['crm_notes'] ?? $rincian[0]['catatan_crm'] ?? '',
        ]);
    }
}

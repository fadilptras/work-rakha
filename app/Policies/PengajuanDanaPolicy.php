<?php

namespace App\Policies;

use App\Models\PengajuanDana;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PengajuanDanaPolicy
{
    /**
     * Tentukan apakah user bisa melihat pengajuan dana.
     */
    public function view(User $user, PengajuanDana $pengajuanDana): bool
    {
        if ($user->id === $pengajuanDana->user_id) return true; // Pemohon
        if ($user->id === $pengajuanDana->approver_1_id) return true; // Approver 1
        if ($user->id === $pengajuanDana->approver_2_id) return true; // Approver 2
        if ($user->id === $pengajuanDana->user->manager_keuangan_id) return true; // Manager Keuangan yang ditugaskan

        if ($user->id === $pengajuanDana->finance_id) return true; // Finance yg memproses
        return $user->role === 'admin'; // Admin
    }

    /**
     * Tentukan apakah user bisa menyetujui/menolak pengajuan.
     */
    public function approve(User $user, PengajuanDana $pengajuanDana): bool
    {
        // Approver 1 saat status 'diajukan'
        if ($user->id === $pengajuanDana->approver_1_id && $pengajuanDana->status === 'diajukan') {
            return true;
        }
        // Approver 2 saat status 'diproses_appr_2'
        if ($user->id === $pengajuanDana->approver_2_id && $pengajuanDana->status === 'diproses_appr_2') {
            return true;
        }

        // Blok untuk Manager Keuangan REJECT sudah dihapus
        
        return false;
    }

    /**
     * Tentukan apakah user (Manager Keuangan yg ditugaskan) bisa menekan tombol "Proses Pembayaran".
     */
    public function prosesPembayaran(User $user, PengajuanDana $pengajuanDana): bool
    {
        // Hanya Manager Keuangan yg ditugaskan, saat status 'proses_pembayaran' DAN payment_status 'menunggu'
        return $user->id === $pengajuanDana->user->manager_keuangan_id
               && $pengajuanDana->status == 'proses_pembayaran'
               && $pengajuanDana->payment_status == 'menunggu';
    }

    /**
     * Tentukan apakah user (Manager Keuangan yg ditugaskan) bisa upload bukti transfer.
     */
    public function uploadBuktiTransfer(User $user, PengajuanDana $pengajuanDana): bool
    {
        // Hanya Manager Keuangan yg ditugaskan, saat status 'proses_pembayaran' DAN payment_status 'diproses'
        return $user->id === $pengajuanDana->user->manager_keuangan_id
               && $pengajuanDana->status == 'proses_pembayaran'
               && $pengajuanDana->payment_status == 'diproses';
    }

    /**
     * Tentukan apakah user (Pemohon) bisa membatalkan pengajuan.
     */
    public function cancel(User $user, PengajuanDana $pengajuanDana): bool
    {
        return $user->id === $pengajuanDana->user_id && in_array($pengajuanDana->status, ['diajukan', 'diproses_appr_2']);
    }

    /**
     * Tentukan apakah user bisa membuat pengajuan dana.
     */
    public function create(User $user): bool
    {
        return $user->role === 'user';
    }

     public function update(User $user, PengajuanDana $pengajuanDana): bool
    {
        return $user->role === 'admin';
    }
    public function delete(User $user, PengajuanDana $pengajuanDana): bool
    {
        return $user->role === 'admin';
    }
}
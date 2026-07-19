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
        if ($user->id === $pengajuanDana->approver_3_id) return true; // Approver 3
        if ($user->id === $pengajuanDana->approver_4_id) return true; // Approver 4
        return $user->role === 'admin'; // Admin
    }

    /**
     * Tentukan apakah user bisa menyetujui/menolak pengajuan.
     * CATATAN: Approver 3 TIDAK termasuk di sini karena tugasnya adalah
     * upload bukti transfer (bukan approval biasa). Mereka ditangani
     * oleh policy uploadBuktiTransfer() di bawah.
     */
    public function approve(User $user, PengajuanDana $pengajuanDana): bool
    {
        if ($user->id === $pengajuanDana->approver_1_id && $pengajuanDana->approver_1_status === 'menunggu') return true;
        if ($user->id === $pengajuanDana->approver_2_id && $pengajuanDana->approver_2_status === 'menunggu') return true;
        // Approver 3 SENGAJA dikecualikan — gunakan uploadBuktiTransfer()
        if ($user->id === $pengajuanDana->approver_4_id && $pengajuanDana->approver_4_status === 'menunggu') return true;
        return false;
    }


    /**
     * Tentukan apakah user (Approver 3) bisa menekan tombol "Proses Pembayaran".
     * Tidak dipakai lagi di alur baru, namun dipertahankan demi kompatibilitas.
     */
    public function prosesPembayaran(User $user, PengajuanDana $pengajuanDana): bool
    {
        return false; // Dihapus karena Approver 3 langsung upload bukti transfer
    }

    /**
     * Tentukan apakah user (Approver 3) bisa upload bukti transfer.
     */
    public function uploadBuktiTransfer(User $user, PengajuanDana $pengajuanDana): bool
    {
        return $user->id === $pengajuanDana->approver_3_id
               && $pengajuanDana->approver_3_status === 'menunggu';
    }

    /**
     * Tentukan apakah user (Pemohon) bisa membatalkan pengajuan.
     */
    public function cancel(User $user, PengajuanDana $pengajuanDana): bool
    {
        return $user->id === $pengajuanDana->user_id && !in_array($pengajuanDana->status, ['selesai', 'dibatalkan', 'ditolak']);
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
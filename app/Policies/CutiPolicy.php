<?php

namespace App\Policies;

use App\Models\Cuti;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Auth\Access\Response;

class CutiPolicy
{
    /**
     * Tentukan apakah user dapat melihat detail cuti.
     */
    public function view(User $user, Cuti $cuti): bool
    {
        // User dapat melihat jika dia adalah pemiliknya
        if ($user->id === $cuti->user_id) {
            return true;
        }

        // Cek apakah user adalah atasan yang berhak menyetujui
        $approver = $this->getApprover($cuti->user);
        if ($approver && $user->id === $approver->id) {
            return true;
        }
        
        // Izinkan HRD melihat semua pengajuan
        if ($user->jabatan === 'HRD') {
            return true;
        }

        return false;
    }

    /**
     * [BARU] Tentukan apakah user dapat menyetujui/menolak cuti.
     */
    public function update(User $user, Cuti $cuti): bool
    {
        // Cari siapa approver yang seharusnya untuk pengajuan ini
        $approver = $this->getApprover($cuti->user);

        // User boleh update JIKA dia adalah approver yang dituju
        return $approver && $user->id === $approver->id;
    }

    /**
     * [BARU] Tentukan apakah user dapat membatalkan cuti.
     */
    public function cancel(User $user, Cuti $cuti): bool
    {
        // User boleh cancel JIKA:
        // 1. Dia adalah pemilik pengajuan cuti
        // 2. Statusnya sudah disetujui
        // 3. Tanggal mulai cuti masih di masa depan
        return $user->id === $cuti->user_id &&
               $cuti->status === 'disetujui' &&
               Carbon::parse($cuti->tanggal_mulai)->isFuture();
    }

    /**
     * Helper privat untuk mendapatkan approver (logika yang sama seperti di Controller).
     */
    private function getApprover(User $user): ?User
    {
        if ($user->jabatan === 'Direktur') {
            return null;
        }

        if ($user->is_kepala_divisi) {
            return User::where('jabatan', 'Direktur')->first();
        }
        
        if ($user->divisi) {
            $approver = User::where('divisi', $user->divisi)
                            ->where('is_kepala_divisi', true)
                            ->where('id', '!=', $user->id)
                            ->first();
            if ($approver) {
                return $approver;
            }
        }

        return User::where('jabatan', 'Direktur')->first();
    }
}
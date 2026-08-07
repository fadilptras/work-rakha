<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Notification;
use Carbon\Carbon;
use App\Models\User;
use App\Notifications\BirthdayNotification;
use Illuminate\Support\Facades\Artisan;

class NotifikasiController extends Controller
{
    /**
     * Menampilkan halaman notifikasi dengan filter & auto-delete lawas.
     */
    public function index(Request $request)
    {
        $title = 'Notifikasi';
        $user = Auth::user();

        // === [FITUR BARU] AUTO DELETE > 1 BULAN ===
        // Menghapus notifikasi yang dibuat lebih dari 1 bulan yang lalu
        $user->notifications()
             ->where('created_at', '<', Carbon::now()->subMonth())
             ->delete();
        
        // Ambil filter tipe dari URL, defaultnya 'Pengajuan Dana'
        $filterType = $request->query('type', 'Pengajuan Dana');

        // === LOGIKA PENGELOMPOKAN (Sesuai Request) ===
        $determineType = function ($notification) {
            $url = strtolower($notification->data['url'] ?? '');
            $title = strtolower($notification->data['title'] ?? '');

            // 1. Pengajuan Dana
            if (Str::contains($url, 'pengajuan-dana') || Str::contains($title, 'dana')) {
                return 'Pengajuan Dana';
            }

            // 2. Pengajuan Barang
            if (Str::contains($url, 'pengajuan-barang') || Str::contains($url, 'inventory') || Str::contains($title, 'barang')) {
                return 'Pengajuan Barang';
            }

            // 3. Pengajuan Cuti
            if (Str::contains($url, 'cuti') || Str::contains($title, 'cuti')) {
                return 'Pengajuan Cuti';
            }

            // 4. Lainnya (Sistem, dll)
            return 'Lainnya';
        };

        // List tombol filter
        $availableTypes = [
            'Pengajuan Dana', 
            'Pengajuan Barang', 
            'Pengajuan Cuti', 
            'Lainnya'
        ];

        // Query notifikasi
        $query = $user->notifications()->latest();

        // Filter langsung di database menggunakan JSON LIKE query
        $keywordMap = [
            'Pengajuan Dana'    => ['dana'],
            'Pengajuan Barang'  => ['barang', 'inventory'],
            'Pengajuan Cuti'    => ['cuti'],
            'Lainnya'           => [],
        ];

        $keywords = $keywordMap[$filterType] ?? [];

        if ($filterType === 'Lainnya') {
            // Lainnya = tidak mengandung keyword manapun
            $query->where(function ($q) {
                foreach (['dana', 'barang', 'inventory', 'cuti'] as $kw) {
                    $q->where('data', 'NOT LIKE', "%{$kw}%");
                }
            });
        } elseif (!empty($keywords)) {
            $query->where(function ($q) use ($keywords, $filterType) {
                foreach ($keywords as $kw) {
                    $q->orWhere('data', 'LIKE', "%{$kw}%");
                }
            });

            // Exclude 'dana' if we are looking for 'barang', to prevent overlap
            if ($filterType === 'Pengajuan Barang') {
                $query->where('data', 'NOT LIKE', "%dana%");
            }
        }

        $notificationsToGroup = $query->take(50)->get();
        
        // Tandai terbaca saat dibuka
        $unreadIds = $notificationsToGroup->whereNull('read_at')->pluck('id');
        if ($unreadIds->isNotEmpty()) {
            $user->notifications()->whereIn('id', $unreadIds)->update(['read_at' => now()]);
            // Refresh data
            $notificationsToGroup = $user->notifications()->whereIn('id', $notificationsToGroup->pluck('id'))->latest()->get();
        }

        // Grouping data
        $groupedNotifications = $notificationsToGroup->groupBy($determineType);

        // Urutan Tampilan
        $groupOrder = [
            'Pengajuan Dana',
            'Pengajuan Barang',
            'Pengajuan Cuti',
            'Lainnya',
        ];

        return view('users.notifikasi.notifikasi-daftar', compact(
            'groupedNotifications', 
            'groupOrder', 
            'title', 
            'availableTypes',
            'filterType'
        ));
    }

    /**
     * Fitur Manual: Kirim Notifikasi Ulang Tahun
     */
    public function kirimUlangTahun()
    {
        try {
            Artisan::call('app:send-birthday-notifications');
            $output = Artisan::output();
            return redirect()->back()->with('success', 'Pengecekan dan pengiriman notifikasi ulang tahun berhasil dieksekusi.');
            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal mengeksekusi notifikasi: ' . $e->getMessage());
        }
    }
    
    /**
     * Menyimpan FCM Token dari Client (Browser/Device) ke Database
     */
    public function updateFcmToken(Request $request)
    {
        try {
            // [FIX] Validasi fcm_token
            $request->validate([
                'fcm_token' => 'required|string',
            ]);

            $user = Auth::user();
            
            // [FIX] Mengambil input dengan nama yang benar: fcm_token
            $user->update([
                'fcm_token' => $request->fcm_token
            ]);

            return response()->json([
                'success' => true, 
                'message' => 'FCM Token berhasil disimpan.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false, 
                'message' => 'Gagal menyimpan token: ' . $e->getMessage()
            ], 500);
        }
    }

    public function kirimHariLibur()
    {
        try {
            Artisan::call('app:send-holiday-info');
            return redirect()->back()->with('success', 'Pengecekan dan pengiriman notifikasi hari libur berhasil dieksekusi.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal mengeksekusi notifikasi libur: ' . $e->getMessage());
        }
    }
}
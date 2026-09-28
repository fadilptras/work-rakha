<?php

namespace App\Http\Controllers;

use App\Models\Aktivitas;
use App\Models\User; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str; 

class AktivitasController extends Controller
{
    /**
     * Batas jumlah aktivitas yang diambil (pribadi & tim).
     */
    private const MONITOR_LIMIT = 120;

    /**
     * Email exact Sales Supervisor (Arief Natanael).
     * DB MySQL sedang offline saat pengecekan, jadi deteksi juga via
     * jabatan & nama sebagai fallback. Isi email exact di sini kalau sudah tahu.
     * Contoh: ['arief.natanael@rakha.com']
     */
    private const SALES_SUPERVISOR_EMAILS = ['Paladin_arief@yahoo.com'];

    /**
     * Divisi yang dipantau oleh Sales Supervisor.
     */
    private const SALES_SUPERVISOR_DIVISI = 'Marketing dan Operasional';

    /**
     * Cek apakah user adalah Direktur.
     */
    private function isDirektur($user): bool
    {
        return Str::contains(strtolower($user->jabatan ?? ''), 'direktur');
    }

    /**
     * Cek apakah user adalah Kepala Divisi.
     */
    private function isKepalaDivisi($user): bool
    {
        return ($user->is_kepala_divisi == 1)
            || Str::contains(strtolower($user->jabatan ?? ''), 'kepala');
    }

    /**
     * Cek apakah user adalah Sales Supervisor (Arief Natanael).
     * Deteksi 3 lapis agar konsisten:
     * 1. jabatan mengandung "sales supervisor" (utama, future-proof)
     * 2. email exact di SALES_SUPERVISOR_EMAILS
     * 3. fallback nama "arief natanael" / "arief natanel" (ejaan varian)
     */
    private function isSalesSupervisor($user): bool
    {
        $jabatan = strtolower($user->jabatan ?? '');
        $email = strtolower($user->email ?? '');
        $nama = strtolower($user->name ?? '');

        if (Str::contains($jabatan, 'sales supervisor')) {
            return true;
        }

        if (in_array($email, array_map('strtolower', self::SALES_SUPERVISOR_EMAILS), true)) {
            return true;
        }

        // Fallback khusus Arief (toleransi typo natanel/natanael)
        if (Str::contains($nama, 'arief natanael haryanto') || Str::contains($nama, 'arief natanel haryanto')) {
            return true;
        }

        // Fallback via email mengandung "arief" + nama mengandung "arief"
        // (dipakai selama email exact belum diisi di konstanta di atas)
        if (Str::contains($email, 'arief') && Str::contains($nama, 'arief')) {
            return true;
        }

        return false;
    }

    /**
     * Ambil daftar user_id yang boleh dipantau oleh user login.
     * Prioritas: Direktur > Sales Supervisor > Kepala Divisi.
     *
     * @return \Illuminate\Support\Collection
     */
    private function getTargetUserIds($user)
    {
        if ($this->isDirektur($user)) {
            return User::where('id', '!=', $user->id)
                ->where('role', 'user')
                ->pluck('id');
        }

        if ($this->isSalesSupervisor($user)) {
            return User::where('id', '!=', $user->id)
                ->where('role', 'user')
                ->whereRaw('LOWER(divisi) = ?', [strtolower(self::SALES_SUPERVISOR_DIVISI)])
                ->pluck('id');
        }

        if ($this->isKepalaDivisi($user) && $user->divisi) {
            return User::where('id', '!=', $user->id)
                ->where('divisi', $user->divisi)
                ->where('role', 'user')
                ->pluck('id');
        }

        return collect();
    }

    /**
     * Menampilkan halaman utama Aktivitas.
     */
    public function index()
    {
        $user = Auth::user();

        // 1. Ambil Aktivitas Pribadi (limit konsisten via konstanta)
        $aktivitasDataPribadi = Aktivitas::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->take(self::MONITOR_LIMIT)
            ->get();

        $aktivitasHariIni = $aktivitasDataPribadi->map(function ($item) {
            $photo_url = $item->lampiran ? asset('storage/' . $item->lampiran) : null;
            return (object) [
                'id' => $item->id,
                'title' => $item->title,
                'created_at' => $item->created_at,
                'keterangan' => $item->keterangan,
                'photo_url' => $photo_url,
                'latitude' => $item->latitude,
                'longitude' => $item->longitude,
            ];
        });

        // 2. Tentukan role pemantau (terpusat di helper agar konsisten)
        $isDirektur = $this->isDirektur($user);
        $isKepalaDivisi = $this->isKepalaDivisi($user);
        $isSalesSupervisor = $this->isSalesSupervisor($user);
        $canPantauTim = $isDirektur || $isKepalaDivisi || $isSalesSupervisor;

        $targetUserIds = $this->getTargetUserIds($user);

        $aktivitasTim = collect();

        // 3. Ambil data aktivitas dari tim yang dipantau
        if ($targetUserIds->isNotEmpty()) {
            $aktivitasTim = Aktivitas::with('user:id,name,jabatan,profile_picture,divisi')
                ->whereIn('user_id', $targetUserIds)
                ->orderBy('created_at', 'desc')
                ->take(self::MONITOR_LIMIT)
                ->get()
                ->map(function ($item) {
                    $photo_url = $item->lampiran ? asset('storage/' . $item->lampiran) : null;
                    return (object) [
                        'id' => $item->id,
                        'user_name' => $item->user->name ?? 'User Dihapus',
                        'user_divisi' => $item->user->divisi ?? '-',
                        'user_photo' => $item->user->profile_picture ? asset('storage/' . $item->user->profile_picture) : null,
                        'title' => $item->title,
                        'keterangan' => $item->keterangan,
                        'created_at' => $item->created_at,
                        'photo_url' => $photo_url,
                        'latitude' => $item->latitude,
                        'longitude' => $item->longitude,
                    ];
                });
        }

        // 4. Kirim semua data ke view
        $agent = new \Jenssegers\Agent\Agent();
        $viewSuffix = $agent->isMobile() ? 'mobile' : 'desktop';

        return view("users.aktivitas.aktivitas_{$viewSuffix}", [
            'title' => 'Catat Aktivitas',
            'user' => $user,
            'aktivitasHariIni' => $aktivitasHariIni,
            'aktivitasTim' => $aktivitasTim, // Data aktivitas rekan kerja
            'isDirektur' => $isDirektur,
            'isKepalaDivisi' => $isKepalaDivisi,
            'isSalesSupervisor' => $isSalesSupervisor,
            'canPantauTim' => $canPantauTim,
        ]);
    }

    /**
     * Menyimpan data aktivitas baru dari form.
     */
    public function store(Request $request)
    {
        // 1. Validasi data yang masuk
        $request->validate([
            'keterangan' => 'required|string', 
            'lampiran' => 'required|image|mimes:jpeg,png,jpg|max:2048', 
            'latitude' => 'required', 
            'longitude' => 'required', 
        ]);

        $path = null;
        if ($request->hasFile('lampiran')) {
            // PERBAIKAN: Gunakan variabel $path, bukan $pathLampiran
            $path = $request->file('lampiran')->store('aktivitas', 'public');
        }

        // 3. Simpan data ke database
        Aktivitas::create([
            'user_id' => Auth::id(), 
            'title' => Str::limit($request->keterangan, 255), 
            'keterangan' => $request->keterangan, 
            'lampiran' => $path, // Sekarang $path berisi path file yang benar
            'latitude' => $request->latitude, 
            'longitude' => $request->longitude, 
        ]);

        // 4. Kembalikan ke halaman aktivitas dengan pesan sukses
        return redirect()->route('aktivitas.index')->with('success', 'Aktivitas berhasil dicatat!'); 
    }

    /**
     * Menyediakan data JSON untuk rekap aktivitas.
     * DIPERBARUI: Sekarang bisa mengambil data orang lain jika diizinkan (untuk modal).
     */
    public function getAktivitasJson(Request $request)
    {
        $user = Auth::user();
        $userIdToFetch = $user->id; // Default: ambil data diri sendiri
        $targetUserId = $request->query('user_id'); // Ambil ID dari request AJAX modal

        // Jika user meminta data orang lain
        if ($targetUserId && $targetUserId != $user->id) {
            // Daftar ID yang boleh dilihat = daftar tim yang dipantau (konsisten dengan index())
            $allowedUserIds = $this->getTargetUserIds($user)->toArray();

            // Cek apakah ID yang diminta ada di dalam daftar yang diizinkan
            if (in_array($targetUserId, $allowedUserIds)) {
                $userIdToFetch = $targetUserId;
            } else {
                return response()->json(['error' => 'Anda tidak diizinkan melihat data ini.'], 403);
            }
        }

        // 1. Tentukan tanggal
        $tanggal = $request->query('start', now()->toDateString());

        // 2. Ambil data aktivitas
        $aktivitas = Aktivitas::where('user_id', $userIdToFetch)
                            ->whereDate('created_at', $tanggal)
                            ->orderBy('created_at', 'asc')
                            ->get();

        // 3. Format data
        $events = $aktivitas->map(function ($item) {
            $photo_url = $item->lampiran ? asset('storage/' . $item->lampiran) : null;
            return [
                'id' => $item->id,
                'title' => $item->title,
                'start' => $item->created_at->toIso8601String(),
                'extendedProps' => [
                    'keterangan' => $item->keterangan,
                    'photo_url' => $photo_url,
                    'latitude' => $item->latitude,
                    'longitude' => $item->longitude,
                ]
            ];
        });

        return response()->json($events); 
    }
}
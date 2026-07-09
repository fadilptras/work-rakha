<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agenda;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Notifications\AgendaNotification;
use Illuminate\Support\Facades\Notification;


class AdminAgendaController extends Controller
{
    /**
     * Menampilkan halaman utama agenda untuk admin.
     */
    public function index(Request $request)
    {
        // Tidak ada perubahan di sini, sudah benar
        $query = Agenda::with('creator');

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereDate('start_time', '>=', $request->start_date)
                  ->whereDate('start_time', '<=', $request->end_date);
        }

        $allAgendas = $query->orderBy('start_time', 'desc')->paginate(10);
        
        return view('admin.agenda.index', [
            'title' => 'Kelola Agenda',
            'allAgendas' => $allAgendas
        ]);
    }

    /**
     * Menyediakan data SEMUA agenda dalam format JSON untuk di-fetch oleh kalender.
     */
    public function getAdminAgendas()
    {
        $agendas = Agenda::with(['creator', 'guests'])->get();

        $events = $agendas->map(function($agenda) {
            // HAPUS KONDISI IF DI SEKITAR RETURN
            // GANTI DENGAN PENGECEKAN NULL MENGGUNAKAN NULL COALESCING OPERATOR (??)
            return [
                'id' => $agenda->id, 
                'title' => \Illuminate\Support\Str::limit($agenda->title, 15), 
                'start' => $agenda->start_time,
                'end' => $agenda->end_time,
                'backgroundColor' => $agenda->color,
                'borderColor' => $agenda->color,
                'extendedProps' => [
                    'fullTitle' => $agenda->title, 
                    'description' => $agenda->description,
                    'location' => $agenda->location,
                    // INI PERUBAHANNYA
                    'organizer' => $agenda->creator->name ?? 'Pengguna Dihapus', 
                    'guests' => $agenda->guests->pluck('name')->toArray(),
                    'guest_ids' => $agenda->guests->pluck('id')->toArray(),
                ]
            ];
        }); // Hapus ->filter() karena tidak ada lagi nilai null

        return response()->json($events);
    }

    // ... (Fungsi store, update, destroy, getAllUsers tidak perlu diubah) ...
    // ... (Salin sisa fungsi dari file asli Anda ke sini) ...
    
    /**
     * Menyimpan agenda baru yang dibuat oleh admin.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_time' => 'required|date_format:Y-m-d H:i',
            'location' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:7',
            'guests' => 'nullable|array',
            'guests.*' => 'exists:users,id',
        ]);

        $startTime = Carbon::parse($validated['start_time']);
        $endTime = $startTime->copy()->addHour();

        $agenda = Agenda::create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'start_time' => $validated['start_time'],
            'end_time' => $endTime,
            'location' => $validated['location'],
            'color' => $validated['color'],
            'user_id' => Auth::id(), // Admin yang sedang login
        ]);

        if (!empty($validated['guests'])) {
            $agenda->guests()->sync($validated['guests']);

            // ===== TAMBAHKAN LOGIKA NOTIFIKASI DI SINI =====
            $guestsToNotify = User::whereIn('id', $validated['guests'])->get();
            $creatorName = Auth::user()->name;
            
            if ($guestsToNotify->isNotEmpty()) {
                Notification::send($guestsToNotify, new AgendaNotification($agenda, 'undangan_baru', $creatorName));
            }
            // ===============================================
        }

        return redirect()->route('admin.agenda.index')->with('success', 'Agenda berhasil dibuat!');
    }

    /**
     * Update agenda yang sudah ada.
     */
    public function update(Request $request, Agenda $agenda)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_time' => 'required|date_format:Y-m-d H:i',
            'location' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:7',
            'guests' => 'nullable|array',
            'guests.*' => 'exists:users,id',
        ]);

        $startTime = Carbon::parse($validated['start_time']);
        $endTime = $startTime->copy()->addHour();

        $agendaData = array_merge($validated, ['end_time' => $endTime]);

        $agenda->update($agendaData);
        $agenda->guests()->sync($validated['guests'] ?? []);

        // ===== TAMBAHKAN LOGIKA NOTIFIKASI DI SINI =====
        // PERBAIKAN KECIL: Tambahkan pengecekan jika creator null
        $creatorName = $agenda->creator->name ?? 'Sistem'; 
        $guestsToNotify = $agenda->fresh()->guests; 

        if ($guestsToNotify->isNotEmpty()) {
            Notification::send($guestsToNotify, new AgendaNotification($agenda, 'agenda_diperbarui', $creatorName));
        }
        // ===============================================

        return redirect()->route('admin.agenda.index')->with('success', 'Agenda berhasil diperbarui!');
    }

    /**
     * Hapus agenda.
     */
    public function destroy(Agenda $agenda)
    {
        // ===== TAMBAHKAN LOGIKA NOTIFIKASI DI SINI =====
        // Ambil daftar tamu SEBELUM relasinya dihapus
        $guestsToNotify = $agenda->guests; 
        // PERBAIKAN KECIL: Tambahkan pengecekan jika creator null
        $creatorName = $agenda->creator->name ?? 'Sistem';
        // ===============================================

        $agenda->guests()->sync([]); // Lepaskan relasi tamu
        $agenda->delete();

        // Kirim notifikasi setelah proses hapus
        if ($guestsToNotify->isNotEmpty()) {
            Notification::send($guestsToNotify, new AgendaNotification($agenda, 'agenda_dibatalkan', $creatorName));
        }

        return redirect()->route('admin.agenda.index')->with('success', 'Agenda berhasil dihapus!');
    }

    /**
     * Menyediakan daftar semua user (karyawan) untuk form.
     */
    public function getAllUsers()
    {
        $users = User::where('role', 'user')
                     ->select('id', 'name')
                     ->orderBy('name', 'asc')
                     ->get();
        return response()->json($users);
    }
}
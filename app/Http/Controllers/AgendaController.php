<?php

namespace App\Http\Controllers;

use App\Models\Agenda;
use App\Models\User;
use App\Models\Holiday;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Notifications\AgendaNotification;
use Illuminate\Support\Facades\Notification;
// =======================================

class AgendaController extends Controller
{
    /**
     * Menampilkan semua agenda untuk FullCalendar.
     * Mengambil agenda yang dibuat oleh user ATAU di mana user diundang.
     */
    public function index()
    {
        $user = Auth::user();

        // --- 1. MENGAMBIL DATA AGENDA ---
        $agendas = Agenda::where(function ($query) use ($user) {
            $query->where('user_id', $user->id)
                ->orWhereHas('guests', function ($subQuery) use ($user) {
                    $subQuery->where('user_id', $user->id);
                });
        })
        ->with(['creator', 'guests'])
        ->get();

        $events = [];
        foreach ($agendas as $agenda) {
            if ($agenda->creator) {
                $events[] = [
                    'id' => 'agenda_' . $agenda->id, 
                    'title' => $agenda->title,
                    'start' => $agenda->start_time,
                    'end' => $agenda->end_time,
                    'backgroundColor' => $agenda->color,
                    'borderColor' => $agenda->color,
                    'allDay' => false,
                    'extendedProps' => [
                        'type' => 'agenda',
                        'fullTitle' => $agenda->title,
                        'description' => $agenda->description,
                        'location' => $agenda->location,
                        'organizer' => $agenda->creator->name,
                        'guests' => $agenda->guests->pluck('name')->toArray(),
                        'guest_ids' => $agenda->guests->pluck('id')->toArray(),
                        'is_creator' => $agenda->user_id === $user->id
                    ]
                ];
            }
        }

        // --- 2. MENGAMBIL DATA HOLIDAYS ---
        $holidays = Holiday::all(); 

        foreach ($holidays as $holiday) {
            $color = $holiday->is_cuti_bersama ? '#F59E0B' : '#EF4444'; 

            $events[] = [
                'id' => 'holiday_' . $holiday->id,
                'title' => $holiday->keterangan, 
                'start' => $holiday->tanggal->format('Y-m-d'),
                'allDay' => true,
                'backgroundColor' => $color,
                'borderColor' => $color,
                // TAMBAHKAN BARIS INI:
                'className' => 'holiday-event', 
                'extendedProps' => [
                    'type' => 'holiday', 
                    'fullTitle' => $holiday->keterangan,
                    'description' => $holiday->is_cuti_bersama ? 'Cuti Bersama' : 'Libur Nasional',
                    'is_creator' => false 
                ]
            ];
        }

        return response()->json($events);
    }

    /**
     * Menyimpan agenda baru dari form modal.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_time' => 'required|date_format:Y-m-d H:i',
            'end_time' => 'required|date_format:Y-m-d H:i|after:start_time',
            'location' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:7',
            'guests' => 'nullable|array',
            'guests.*' => 'exists:users,id',
        ]);
        
        $agenda = Agenda::create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'location' => $validated['location'],
            'color' => $validated['color'],
            'user_id' => Auth::id(),
        ]);

        if (!empty($validated['guests'])) {
            $agenda->guests()->sync($validated['guests']);

            // =================== LOGIKA NOTIFIKASI (BARU) ===================
            // Ambil model User dari ID tamu yang diundang
            $guestsToNotify = User::whereIn('id', $validated['guests'])->get();
            $creatorName = Auth::user()->name;
            
            // Kirim notifikasi ke semua tamu
            if ($guestsToNotify->isNotEmpty()) {
                Notification::send($guestsToNotify, new AgendaNotification($agenda, 'undangan_baru', $creatorName));
            }
            // ================================================================
        }

        return redirect()->route('dashboard')->with('success', 'Agenda berhasil dibuat!');
    }
    
    /**
     * Update agenda yang sudah ada.
     */
    public function update(Request $request, Agenda $agenda)
    {
        // Pastikan hanya pembuat agenda yang bisa mengedit
        if ($agenda->user_id !== Auth::id()) {
            return response()->json(['error' => 'Anda tidak memiliki izin untuk mengedit agenda ini.'], 403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_time' => 'required|date_format:Y-m-d H:i',
            'end_time' => 'required|date_format:Y-m-d H:i|after:start_time',
            'location' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:7',
            'guests' => 'nullable|array',
            'guests.*' => 'exists:users,id',
        ]);

        $agenda->update($validated);
        $agenda->guests()->sync($validated['guests'] ?? []);

        // =================== LOGIKA NOTIFIKASI (BARU) ===================
        // Ambil daftar tamu yang terbaru setelah di-sync
        $guestsToNotify = $agenda->fresh()->guests; 
        $creatorName = $agenda->creator->name;

        if ($guestsToNotify->isNotEmpty()) {
            Notification::send($guestsToNotify, new AgendaNotification($agenda, 'agenda_diperbarui', $creatorName));
        }
        // ================================================================

        return redirect()->route('dashboard')->with('success', 'Agenda berhasil diperbarui!');
    }

    /**
     * Hapus agenda.
     */
    public function destroy(Agenda $agenda)
    {
        // Pastikan hanya pembuat agenda yang bisa menghapus
        if ($agenda->user_id !== Auth::id()) {
            return response()->json(['error' => 'Anda tidak memiliki izin untuk menghapus agenda ini.'], 403);
        }
        
        // =================== LOGIKA NOTIFIKASI (BARU) ===================
        // Ambil daftar tamu SEBELUM relasinya dihapus
        $guestsToNotify = $agenda->guests; 
        $creatorName = $agenda->creator->name;
        // ================================================================

        $agenda->guests()->sync([]); // Lepaskan semua relasi tamu
        $agenda->delete();

        // Kirim notifikasi setelah agenda benar-benar dihapus
        if ($guestsToNotify->isNotEmpty()) {
            Notification::send($guestsToNotify, new AgendaNotification($agenda, 'agenda_dibatalkan', $creatorName));
        }

        return redirect()->route('dashboard')->with('success', 'Agenda berhasil dihapus!');
    }

    /**
     * Menyediakan daftar user untuk form.
     */
    public function getUsers()
    {
        $users = User::where('id', '!=', Auth::id())
                     ->select('id', 'name')
                     ->orderBy('name', 'asc')
                     ->get();
        return response()->json($users);
    }
}
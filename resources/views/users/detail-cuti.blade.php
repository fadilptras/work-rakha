<x-layout-users>
    <x-slot:title>Detail Pengajuan Cuti</x-slot:title>
    
    {{-- Ikon (Font Awesome) --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <div class="bg-gradient-to-br from-sky-50 to-blue-100 min-h-screen p-0 md:p-8">
        <div class="container mx-auto max-w-5xl">

            {{-- 1. NAVIGASI & AKSI --}}
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3 mb-6">
                <a href="{{ route('cuti.create') }}" 
                   class="inline-flex items-center justify-center gap-2 px-5 py-2.5 text-sm font-semibold text-white bg-[#001A6E] rounded-xl shadow-lg hover:bg-[#0B1D51] hover:shadow-xl transition-all duration-300 w-full sm:w-auto">
                    <i class="fas fa-arrow-left"></i>
                    Kembali ke Riwayat
                </a>
                
                <a href="{{ route('cuti.download', $cuti->id) }}" 
                   class="inline-flex items-center justify-center gap-2 px-5 py-2.5 text-sm font-semibold text-white bg-red-600 rounded-xl shadow-lg hover:bg-red-700 hover:shadow-xl transition-all duration-300 w-full sm:w-auto">
                    <i class="fas fa-file-pdf"></i>
                    <span>Cetak PDF</span>
                </a>
            </div>

            {{-- 2. HEADER UTAMA --}}
            <div class="bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden mb-6">
                <div class="p-6 md:p-8 bg-[#001A6E] text-white">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div class="flex items-center gap-5">
                            <div class="h-14 w-14 rounded-full bg-white/10 flex items-center justify-center backdrop-blur-sm">
                                <i class="fas fa-calendar-alt text-3xl text-white"></i>
                            </div>
                            <div>
                                <h1 class="text-2xl font-bold tracking-tight">Pengajuan Cuti {{ $cuti->jenis_cuti }}</h1>
                                <p class="text-slate-300 text-sm mt-1">
                                    Diajukan pada {{ $cuti->created_at->translatedFormat('d F Y, H:i') }} WIB
                                </p>
                            </div>
                        </div>
                        
                        <div>
                            @php
                                $statusClass = match($cuti->status) {
                                    'disetujui' => 'bg-green-500 shadow-green-500/30',
                                    'ditolak' => 'bg-red-500 shadow-red-500/30',
                                    'proses_finalisasi' => 'bg-purple-500 shadow-purple-500/30',
                                    'dibatalkan' => 'bg-slate-500 shadow-slate-500/30',
                                    default => 'bg-blue-500 shadow-blue-500/30',
                                };
                                $label = str_replace('_', ' ', $cuti->status);
                            @endphp
                            <span class="inline-flex items-center px-4 py-2 rounded-full text-xs font-bold shadow-lg text-white uppercase tracking-wider {{ $statusClass }}">
                                {{ $label }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 3. INFO PENGAJUAN & TIMELINE --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                {{-- INFO DETAIL --}}
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-xl shadow-md border border-slate-200 overflow-hidden h-full flex flex-col">
                        <div class="px-5 py-4 border-b border-slate-100 bg-[#D0E1FD]">
                            <h2 class="text-base font-bold text-slate-800 flex items-center">
                                <i class="fas fa-info-circle text-slate-700 mr-2"></i> Detail Pengajuan
                            </h2>
                        </div>
                        <div class="p-5 text-sm space-y-4 flex-grow">
                            <div>
                                <span class="text-slate-400 text-[10px] font-bold uppercase tracking-widest block">Pemohon</span>
                                <span class="font-bold text-slate-800">{{ $cuti->user->name ?? 'N/A' }}</span>
                            </div>
                            <div>
                                <span class="text-slate-400 text-[10px] font-bold uppercase tracking-widest block">Rentang Tanggal</span>
                                <span class="font-bold text-slate-800">
                                    {{ \Carbon\Carbon::parse($cuti->tanggal_mulai)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($cuti->tanggal_selesai)->format('d/m/Y') }}
                                </span>
                                <p class="text-blue-600 font-bold mt-1 text-xs">{{ $cuti->total_hari }} Hari Kerja</p>
                            </div>
                            <div>
                                <span class="text-slate-400 text-[10px] font-bold uppercase tracking-widest block">Alasan</span>
                                <p class="text-slate-700 italic mt-1 leading-relaxed">"{{ $cuti->alasan }}"</p>
                            </div>
                            @if($cuti->lampiran)
                            <div class="pt-2">
                                <a href="{{ asset('storage/' . $cuti->lampiran) }}" target="_blank" class="inline-flex items-center text-blue-600 hover:text-blue-800 font-bold text-xs transition-colors">
                                    <i class="fas fa-paperclip mr-1"></i> Lihat Lampiran Dokumen
                                </a>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- TIMELINE PROGRESS (4 TAHAP DINAMIS) --}}
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-xl shadow-md border border-slate-200 overflow-hidden h-full flex flex-col">
                        <div class="px-5 py-3 border-b border-slate-100 bg-[#D0E1FD]">
                            <h2 class="text-sm font-bold text-slate-800 flex items-center">
                                <i class="fas fa-tasks text-slate-700 mr-2 text-lg"></i> Timeline Persetujuan
                            </h2>
                        </div>

                        <div class="p-5 flex-grow overflow-y-auto">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                @foreach([
                                    1 => ['nama' => 'Tahap 1', 'user' => $cuti->approver1, 'status' => $cuti->status_approver_1, 'catatan' => $cuti->catatan_approver_1],
                                    2 => ['nama' => 'Tahap 2', 'user' => $cuti->approver2, 'status' => $cuti->status_approver_2, 'catatan' => $cuti->catatan_approver_2],
                                    3 => ['nama' => 'Tahap 3', 'user' => $cuti->approver3, 'status' => $cuti->status_approver_3, 'catatan' => $cuti->catatan_approver_3],
                                    4 => ['nama' => 'Tahap 4 (Admin)', 'user' => $cuti->approver4, 'status' => $cuti->status_approver_4, 'catatan' => $cuti->catatan_approver_4],
                                ] as $stage => $data)
                                    @php
                                        $theme = match($data['status']) {
                                            'disetujui' => ['border' => 'border-l-green-500', 'bg' => 'bg-green-50/50', 'badge' => 'bg-green-100 text-green-700'],
                                            'ditolak' => ['border' => 'border-l-red-500', 'bg' => 'bg-red-50/50', 'badge' => 'bg-red-100 text-red-700'],
                                            'skipped' => ['border' => 'border-l-slate-300', 'bg' => 'bg-slate-50', 'badge' => 'bg-slate-200 text-slate-600'],
                                            default => ['border' => 'border-l-yellow-400', 'bg' => 'bg-yellow-50/50', 'badge' => 'bg-yellow-100 text-yellow-700'],
                                        };
                                    @endphp
                                    <div class="rounded-lg border border-slate-200 p-4 {{ $theme['bg'] }} {{ $theme['border'] }} border-l-[4px]">
                                        <div class="flex justify-between items-start mb-1">
                                            <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider">{{ $data['nama'] }}</span>
                                            <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase tracking-wider {{ $theme['badge'] }}">
                                                {{ $data['status'] }}
                                            </span>
                                        </div>
                                        <h4 class="text-xs font-bold text-slate-800 truncate">{{ $data['user']->name ?? 'Belum Ditentukan' }}</h4>
                                        
                                        @if($data['catatan'])
                                            <div class="mt-2 p-2 bg-white rounded border border-slate-100 text-[10px] text-slate-600 italic">
                                                "{{ $data['catatan'] }}"
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 4. FORM TINDAKAN (LOGIKA URUTAN KETAT) --}}
            @php
                $user = Auth::user();
                $app1Done = ($cuti->status_approver_1 != 'menunggu');
                $app2Done = ($cuti->status_approver_2 != 'menunggu');
                $app3Done = ($cuti->status_approver_3 != 'menunggu');

                $isAppr1 = ($user->id == $cuti->approver_cuti_1_id && $cuti->status_approver_1 == 'menunggu');
                $isAppr2 = ($user->id == $cuti->approver_cuti_2_id && $cuti->status_approver_2 == 'menunggu' && $app1Done);
                $isAppr3 = ($user->id == $cuti->approver_cuti_3_id && $cuti->status_approver_3 == 'menunggu' && $app1Done && $app2Done);
                $isAppr4 = ($user->id == $cuti->approver_cuti_4_id && $cuti->status_approver_4 == 'menunggu' && $app1Done && $app2Done && $app3Done);
                
                $showForm = $isAppr1 || $isAppr2 || $isAppr3 || $isAppr4;
            @endphp

            @if($showForm)
            <div class="bg-white rounded-2xl shadow-xl border border-slate-200 p-8 border-t-4 border-t-blue-500 mb-8 animate-pulse-subtle">
                <h3 class="text-xl font-bold text-slate-800 mb-6 flex items-center">
                    <i class="fas fa-gavel text-slate-800 mr-2"></i> Tindakan Persetujuan (Giliran Anda)
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    {{-- FORM SETUJU --}}
                    <form action="{{ route('cuti.updateStatus', $cuti->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="bg-green-50 p-6 rounded-2xl border border-green-100 text-center h-full flex flex-col hover:shadow-md transition-all">
                            <label class="block text-sm font-bold text-green-800 mb-3">Setujui Pengajuan Cuti</label>
                            <textarea name="catatan" rows="3" class="w-full p-3 border border-green-200 rounded-xl mb-4 text-sm focus:ring-green-500 focus:border-green-500 bg-white" placeholder="Berikan catatan persetujuan (opsional)..."></textarea>
                            <input type="hidden" name="status" value="disetujui">
                            <button type="submit" class="mt-auto w-full bg-green-600 text-white font-bold py-3.5 rounded-xl hover:bg-green-700 transition shadow-md active:scale-95">
                                <i class="fas fa-check-circle mr-2"></i> Setujui Sekarang
                            </button>
                        </div>
                    </form>

                    {{-- FORM TOLAK --}}
                    <form action="{{ route('cuti.updateStatus', $cuti->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="bg-red-50 p-6 rounded-2xl border border-red-100 text-center h-full flex flex-col hover:shadow-md transition-all">
                            <label class="block text-sm font-bold text-red-800 mb-3">Tolak Pengajuan <span class="text-red-500">*</span></label>
                            <textarea name="catatan" rows="3" class="w-full p-3 border border-red-200 rounded-xl mb-4 text-sm focus:ring-red-500 focus:border-red-500 bg-white" placeholder="Alasan penolakan wajib diisi..." required></textarea>
                            <input type="hidden" name="status" value="ditolak">
                            <button type="submit" class="mt-auto w-full bg-red-600 text-white font-bold py-3.5 rounded-xl hover:bg-red-700 transition shadow-md active:scale-95">
                                <i class="fas fa-times-circle mr-2"></i> Tolak Pengajuan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            @endif

            {{-- 5. BATALKAN (JIKA PEMILIK) --}}
            @if(Auth::id() == $cuti->user_id && in_array($cuti->status, ['diajukan', 'proses_finalisasi']))
            <div class="text-center mb-10">
                <form action="{{ route('cuti.cancel', $cuti) }}" method="POST" onsubmit="return confirm('Yakin ingin membatalkan pengajuan cuti ini?');">
                    @csrf
                    <button type="submit" class="text-slate-500 hover:text-red-600 text-xs font-bold transition-all uppercase tracking-widest border-b border-dotted border-slate-400 hover:border-red-600 pb-1">
                        <i class="fas fa-trash-alt mr-1"></i> Batalkan Pengajuan Saya
                    </button>
                </form>
            </div>
            @endif

        </div>
    </div>
</x-layout-users>
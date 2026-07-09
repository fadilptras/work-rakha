<x-layout-users>
    <x-slot:title>Detail Pengajuan Dana</x-slot:title>

    <div class="bg-gradient-to-br from-sky-50 to-blue-100 min-h-screen p-0 md:p-8">
        <div class="container mx-auto max-w-6xl">

            {{-- 1. NAVIGASI (BACK & DOWNLOAD) --}}
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3 mb-6">
                @if(Auth::id() == $pengajuanDana->user_id)
                    <a href="{{ route('pengajuan_dana.index') }}" 
                       class="inline-flex items-center justify-center gap-2 px-5 py-2.5 text-sm font-semibold text-white bg-[#043915] rounded-xl shadow-lg hover:bg-slate-700 hover:shadow-xl transition-all duration-300 w-full sm:w-auto">
                        <i class="fas fa-arrow-left"></i>
                        Kembali ke Rekap
                    </a>
                @else
                    <div></div> 
                @endif

                @if(Auth::id() == $pengajuanDana->user_id || Auth::user()->role == 'admin' || Auth::user()->can('approve', $pengajuanDana))
                    <a href="{{ route('pengajuan_dana.download', $pengajuanDana) }}" 
                       class="inline-flex items-center justify-center gap-2 px-5 py-2.5 text-sm font-semibold text-white bg-red-600 rounded-xl shadow-lg hover:bg-red-700 hover:shadow-xl transition-all duration-300 w-full sm:w-auto"
                       title="Unduh sebagai PDF">
                        <i class="fas fa-file-pdf"></i>
                        <span>Cetak PDF</span>
                    </a>
                @endif
            </div>

            {{-- 2. HEADER UTAMA --}}
            <div class="bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden mb-6">
                <div class="p-6 md:p-8 bg-[#043915] text-white">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                        <div class="flex items-center gap-5">
                            <div class="h-14 w-14 rounded-full bg-white/10 flex items-center justify-center backdrop-blur-sm">
                                <i class="fas fa-money-bill-wave text-3xl text-white"></i>
                            </div>
                            <div>
                                <h1 class="text-2xl font-bold tracking-tight">{{ $pengajuanDana->judul_pengajuan }}</h1>
                                <p class="text-slate-300 text-sm mt-1">
                                    Diajukan tanggal {{ $pengajuanDana->created_at->translatedFormat('d F Y') }}
                                </p>
                            </div>
                        </div>
                        
                        {{-- Status Badge Global --}}
                        <div>
                            @php
                                $statusClass = '';
                                $statusText = '';
                                switch ($pengajuanDana->status) {
                                    case 'selesai': $statusClass = 'bg-green-500 text-white shadow-green-500/30'; $statusText = 'Selesai'; break;
                                    case 'ditolak': $statusClass = 'bg-red-500 text-white shadow-red-500/30'; $statusText = 'Ditolak'; break;
                                    case 'proses_pembayaran': $statusClass = 'bg-blue-500 text-white shadow-blue-500/30'; $statusText = 'Proses Pembayaran'; break;
                                    case 'diproses_appr_2': $statusClass = 'bg-indigo-500 text-white shadow-indigo-500/30'; $statusText = 'Menunggu Approver 2'; break;
                                    case 'dibatalkan': $statusClass = 'bg-slate-500 text-white shadow-slate-500/30'; $statusText = 'Dibatalkan'; break;
                                    default: $statusClass = 'bg-yellow-500 text-white shadow-yellow-500/30'; $statusText = 'Menunggu Approver 1'; break;
                                }
                            @endphp
                            <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-bold shadow-lg {{ $statusClass }}">
                                {{ $statusText }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 3. GRID CONTENT (INFO & TIMELINE) --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                
                {{-- KOLOM KIRI: INFO PEMOHON & REKENING (COMPACT) --}}
                <div class="lg:col-span-1 space-y-4">
                    
                    {{-- Info User --}}
                    <div class="bg-white rounded-xl shadow-md border border-slate-200 overflow-hidden">
                        {{-- UPDATE: Icon text-slate-700 & text-lg --}}
                        <div class="px-5 py-4 border-b border-slate-100 bg-[#B0CE88]">
                            <h2 class="text-base font-bold text-slate-800 flex items-center">
                                <i class="fas fa-user-circle text-slate-700 mr-2 text-lg"></i> Informasi Pemohon
                            </h2>
                        </div>
                        <div class="p-5 text-sm space-y-3">
                            <div class="flex justify-between items-center border-b border-slate-50 pb-2">
                                <span class="text-slate-500">Nama</span>
                                <span class="font-semibold text-slate-800 text-right">{{ $pengajuanDana->user->name }}</span>
                            </div>
                            <div class="flex justify-between items-center border-b border-slate-50 pb-2">
                                <span class="text-slate-500">Divisi</span>
                                <span class="font-semibold text-slate-800 text-right">{{ $pengajuanDana->user->divisi }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-slate-500">ID Pengajuan</span>
                                <span class="font-mono font-bold text-slate-600 bg-slate-100 px-2 py-0.5 rounded text-xs">
                                    #{{ str_pad($pengajuanDana->id, 4, '0', STR_PAD_LEFT) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Info Bank --}}
                    <div class="bg-white rounded-xl shadow-md border border-slate-200 overflow-hidden">
                        {{-- UPDATE: Icon text-slate-700 & text-lg --}}
                        <div class="px-5 py-4 border-b border-slate-100 bg-[#B0CE88]">
                            <h2 class="text-base font-bold text-slate-800 flex items-center">
                                <i class="fas fa-university text-slate-700 mr-2 text-lg"></i> Rekening Tujuan
                            </h2>
                        </div>
                        <div class="p-5">
                            <div class="bg-blue-50/50 rounded-lg border border-blue-100 p-3 flex items-center justify-between gap-3">
                                <div class="flex items-center gap-3 overflow-hidden">
                                    <div class="w-8 h-8 rounded bg-blue-100 flex-shrink-0 flex items-center justify-center text-blue-600">
                                        <i class="fas fa-wallet text-sm"></i>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-xs text-slate-500 uppercase font-bold tracking-wider">Bank</p>
                                        <p class="font-bold text-slate-800 text-sm truncate">{{ $pengajuanDana->nama_bank }}</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-xs text-slate-500 uppercase font-bold tracking-wider">No. Rek</p>
                                    <p class="font-mono font-bold text-slate-800 text-sm">{{ $pengajuanDana->no_rekening }}</p>
                                </div>
                                
                            </div>
                            <div class="bg-blue-50/50 rounded-lg border border-blue-100 p-3 flex items-center justify-between gap-3">
                                <div class="flex items-center gap-2 overflow-hidden">
                                    <div class="text-right">
                                    <p class="text-xs text-slate-500 uppercase font-bold tracking-wider">A/N</p>
                                    <p class="font-mono font-bold text-slate-800 text-sm">{{ $pengajuanDana->nama_rek }}</p>
                                </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- KOLOM KANAN: TIMELINE (CARD STYLE BLOCKS) --}}
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-xl shadow-md border border-slate-200 overflow-hidden h-full flex flex-col">
                        {{-- UPDATE: Icon text-slate-700 & text-lg --}}
                        <div class="px-5 py-3 border-b border-slate-100 bg-[#B0CE88]">
                            <h2 class="text-sm font-bold text-slate-800 flex items-center">
                                <i class="fas fa-history text-slate-700 mr-2 text-lg"></i> Timeline Persetujuan
                            </h2>
                        </div>

                        {{-- Isi Timeline --}}
                        <div class="p-5 space-y-3 flex-1 overflow-y-auto">
                            
                            {{-- 1. APPROVER 1 --}}
                            @if ($pengajuanDana->approver_1_id)
                                @php
                                    $s1 = $pengajuanDana->approver_1_status;
                                    $c1 = $pengajuanDana->approver_1_catatan;
                                    $t1 = $pengajuanDana->approver_1_approved_at;
                                    
                                    $theme1 = match($s1) {
                                        'disetujui' => ['border' => 'border-l-green-500', 'bg' => 'bg-slate-50', 'badge' => 'bg-green-100 text-green-700'],
                                        'ditolak' => ['border' => 'border-l-red-500', 'bg' => 'bg-red-50', 'badge' => 'bg-red-100 text-red-700'],
                                        'skipped' => ['border' => 'border-l-slate-400', 'bg' => 'bg-slate-50', 'badge' => 'bg-slate-200 text-slate-600'],
                                        default => ['border' => 'border-l-yellow-400', 'bg' => 'bg-yellow-50', 'badge' => 'bg-yellow-100 text-yellow-700'],
                                    };
                                @endphp
                                <div class="rounded border border-slate-200 p-3 {{ $theme1['bg'] }} {{ $theme1['border'] }} border-l-[3px]">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Tahap 1</span>
                                                @if($t1)
                                                    <span class="text-[10px] text-slate-400">• {{ $t1->translatedFormat('d M, H:i') }}</span>
                                                @endif
                                            </div>
                                            <h4 class="text-sm font-bold text-slate-800 leading-tight mt-0.5">{{ $pengajuanDana->approver1->name ?? 'Approver 1' }}</h4>
                                        </div>
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $theme1['badge'] }}">
                                            {{ ucfirst($s1) }}
                                        </span>
                                    </div>
                                    @if($c1)
                                        <div class="mt-2 bg-white p-2 rounded border border-slate-200 text-xs text-slate-600 italic relative flex items-start gap-2">
                                            <i class="fas fa-quote-left text-slate-300 text-[10px] mt-0.5"></i>
                                            <span>{{ $c1 }}</span>
                                        </div>
                                    @endif
                                </div>
                            @endif

                            {{-- 2. APPROVER 2 --}}
                            @if ($pengajuanDana->approver_2_id)
                                @php
                                    $s2 = $pengajuanDana->approver_2_status;
                                    $c2 = $pengajuanDana->approver_2_catatan;
                                    $t2 = $pengajuanDana->approver_2_approved_at;
                                    
                                    $theme2 = match($s2) {
                                        'disetujui' => ['border' => 'border-l-green-500', 'bg' => 'bg-slate-50', 'badge' => 'bg-green-100 text-green-700'],
                                        'ditolak' => ['border' => 'border-l-red-500', 'bg' => 'bg-red-50', 'badge' => 'bg-red-100 text-red-700'],
                                        'skipped' => ['border' => 'border-l-slate-400', 'bg' => 'bg-slate-50', 'badge' => 'bg-slate-200 text-slate-600'],
                                        default => ['border' => 'border-l-yellow-400', 'bg' => 'bg-yellow-50', 'badge' => 'bg-yellow-100 text-yellow-700'],
                                    };
                                @endphp
                                <div class="rounded border border-slate-200 p-3 {{ $theme2['bg'] }} {{ $theme2['border'] }} border-l-[3px]">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Tahap 2</span>
                                                @if($t2)
                                                    <span class="text-[10px] text-slate-400">• {{ $t2->translatedFormat('d M, H:i') }}</span>
                                                @endif
                                            </div>
                                            <h4 class="text-sm font-bold text-slate-800 leading-tight mt-0.5">{{ $pengajuanDana->approver2->name ?? 'Approver 2' }}</h4>
                                        </div>
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $theme2['badge'] }}">
                                            {{ ucfirst($s2) }}
                                        </span>
                                    </div>
                                    @if($c2)
                                        <div class="mt-2 bg-white p-2 rounded border border-slate-200 text-xs text-slate-600 italic relative flex items-start gap-2">
                                            <i class="fas fa-quote-left text-slate-300 text-[10px] mt-0.5"></i>
                                            <span>{{ $c2 }}</span>
                                        </div>
                                    @endif
                                </div>
                            @endif

                            {{-- 3. MANAGER KEUANGAN --}}
                            @if ($pengajuanDana->user->managerKeuangan)
                                @php
                                    $sF = $pengajuanDana->payment_status; 
                                    $cF = $pengajuanDana->catatan_finance;
                                    $tF = $pengajuanDana->finance_processed_at;
                                    if ($sF === 'selesai') $tF = $pengajuanDana->updated_at;

                                    $themeF = match($sF) {
                                        'selesai' => ['border' => 'border-l-green-500', 'bg' => 'bg-slate-50', 'badge' => 'bg-green-100 text-green-700'],
                                        'diproses' => ['border' => 'border-l-blue-500', 'bg' => 'bg-blue-50', 'badge' => 'bg-blue-100 text-blue-700'],
                                        'ditolak' => ['border' => 'border-l-red-500', 'bg' => 'bg-red-50', 'badge' => 'bg-red-100 text-red-700'],
                                        'skipped' => ['border' => 'border-l-slate-400', 'bg' => 'bg-slate-50', 'badge' => 'bg-slate-200 text-slate-600'],
                                        default => ['border' => 'border-l-yellow-400', 'bg' => 'bg-yellow-50', 'badge' => 'bg-yellow-100 text-yellow-700'],
                                    };
                                    
                                    $textF = match($sF) {
                                        'selesai' => 'Selesai',
                                        'diproses' => 'Diproses',
                                        default => ucfirst($sF)
                                    };
                                @endphp
                                <div class="rounded border border-slate-200 p-3 {{ $themeF['bg'] }} {{ $themeF['border'] }} border-l-[3px]">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <div class="flex items-center gap-2">
                                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Tahap 3 (Final)</span>
                                                @if($tF)
                                                    <span class="text-[10px] text-slate-400">• {{ $tF->translatedFormat('d M, H:i') }}</span>
                                                @endif
                                            </div>
                                            <h4 class="text-sm font-bold text-slate-800 leading-tight mt-0.5">{{ $pengajuanDana->user->managerKeuangan->name ?? 'Finance' }}</h4>
                                        </div>
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $themeF['badge'] }}">
                                            {{ $textF }}
                                        </span>
                                    </div>
                                    @if($cF)
                                        <div class="mt-2 bg-white p-2 rounded border border-slate-200 text-xs text-slate-600 italic relative flex items-start gap-2">
                                            <i class="fas fa-quote-left text-slate-300 text-[10px] mt-0.5"></i>
                                            <span>{{ $cF }}</span>
                                        </div>
                                    @endif
                                </div>
                            @endif

                        </div>
                    </div>
                </div>
            </div>

            {{-- 4. RINCIAN DANA (TABEL) --}}
            <div class="bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden mb-6">
                {{-- UPDATE: Icon text-slate-700 & text-lg --}}
                <div class="px-5 py-4 border-b border-slate-100 bg-[#B0CE88]">
                    <h2 class="text-lg font-bold text-slate-800 flex items-center">
                        <i class="fas fa-list-ul text-slate-700 mr-2 text-lg"></i> Rincian Penggunaan Dana
                    </h2>
                </div>

                <div class="p-6">
                    <div class="overflow-hidden rounded-xl border border-slate-200">
                        <table class="min-w-full text-sm">
                            <thead class="bg-slate-50 text-slate-600 uppercase text-xs tracking-wider font-semibold">
                                <tr>
                                    <th class="px-6 py-4 text-center w-16">No</th>
                                    <th class="px-6 py-4 text-left">Deskripsi Kebutuhan</th>
                                    <th class="px-6 py-4 text-right w-48">Nominal (Rp)</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($pengajuanDana->rincian_dana as $index => $rincian)
                                <tr class="hover:bg-slate-50 transition-colors">
                                    <td class="px-6 py-4 text-center text-slate-500">{{ $loop->iteration }}</td>
                                    <td class="px-6 py-4 text-slate-800 font-medium">{{ $rincian['deskripsi'] ?? '-' }}</td>
                                    <td class="px-6 py-4 text-right font-mono text-slate-700">
                                        {{ number_format($rincian['jumlah'] ?? 0, 0, ',', '.') }}
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="px-6 py-8 text-center text-slate-500">Data rincian tidak tersedia.</td>
                                </tr>
                                @endforelse
                            </tbody>
                            <tfoot class="bg-emerald-50/50 border-t border-emerald-100">
                                <tr>
                                    <td colspan="2" class="px-6 py-4 text-right font-bold text-emerald-900">TOTAL PENGAJUAN</td>
                                    <td class="px-6 py-4 text-right font-bold font-mono text-emerald-700 text-lg">
                                        Rp {{ number_format($pengajuanDana->total_dana, 0, ',', '.') }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            {{-- 5. DOKUMEN & BUKTI --}}
            <div class="bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden mb-6">
                {{-- UPDATE: Icon text-slate-700 & text-lg --}}
                <div class="px-5 py-4 border-b border-slate-100 bg-[#B0CE88]">
                    <h2 class="text-lg font-bold text-slate-800 flex items-center">
                        <i class="fas fa-paperclip text-slate-700 mr-2 text-lg"></i> Dokumen Pendukung
                    </h2>
                </div>

                <div class="p-6">
                    @if(empty($pengajuanDana->lampiran) && !$pengajuanDana->bukti_transfer && !$pengajuanDana->invoice)
                        <div class="text-center py-8 bg-slate-50 rounded-xl border border-dashed border-slate-300">
                            <i class="fas fa-file-excel text-4xl text-slate-300 mb-3"></i>
                            <p class="text-slate-500">Tidak ada dokumen yang dilampirkan.</p>
                        </div>
                    @else
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                            {{-- Lampiran Array --}}
                            @if ($pengajuanDana->lampiran)
                                @foreach ($pengajuanDana->lampiran as $lampiran)
                                <div class="relative group">
                                    <a href="{{ asset('storage/' . $lampiran) }}"
                                       class="flex flex-col items-center p-6 bg-slate-50 border border-slate-200 rounded-xl hover:bg-emerald-50 hover:border-emerald-200 transition-all text-center h-full">
                                        <i class="fas fa-file-alt text-3xl text-emerald-500 mb-3 group-hover:scale-110 transition-transform"></i>
                                        <p class="text-sm font-semibold text-slate-700 truncate w-full">{{ basename($lampiran) }}</p>
                                        <p class="text-xs text-slate-500 mt-1">Lampiran {{ $loop->iteration }}</p>
                                    </a>
                                    <a href="{{ asset('storage/' . $lampiran) }}" download
                                       class="absolute top-2 right-2 p-1.5 bg-slate-200 text-slate-600 rounded-lg hover:bg-emerald-600 hover:text-white transition shadow-sm"
                                       title="Download">
                                        <i class="fas fa-download text-xs"></i>
                                    </a>
                                </div>
                                @endforeach
                            @endif

                            {{-- Bukti Transfer --}}
                            @if($pengajuanDana->bukti_transfer)
                                <div class="relative group">
                                    <a href="{{ asset('storage/' . $pengajuanDana->bukti_transfer) }}"
                                       class="flex flex-col items-center p-6 bg-green-50 border border-green-200 rounded-xl hover:bg-green-100 transition-all text-center h-full">
                                        <i class="fas fa-receipt text-3xl text-green-600 mb-3 group-hover:scale-110 transition-transform"></i>
                                        <p class="text-sm font-semibold text-green-800">Bukti Transfer</p>
                                        <p class="text-xs text-green-600 mt-1">Telah diupload</p>
                                    </a>
                                    <a href="{{ asset('storage/' . $pengajuanDana->bukti_transfer) }}" download
                                       class="absolute top-2 right-2 p-1.5 bg-green-200 text-green-700 rounded-lg hover:bg-green-600 hover:text-white transition shadow-sm">
                                        <i class="fas fa-download text-xs"></i>
                                    </a>
                                </div>
                            @endif

                            {{-- Invoice --}}
                            @if($pengajuanDana->invoice)
                                <div class="relative group">
                                    <a href="{{ asset('storage/' . $pengajuanDana->invoice) }}"
                                       class="flex flex-col items-center p-6 bg-purple-50 border border-purple-200 rounded-xl hover:bg-purple-100 transition-all text-center h-full">
                                        <i class="fas fa-file-invoice-dollar text-3xl text-purple-600 mb-3 group-hover:scale-110 transition-transform"></i>
                                        <p class="text-sm font-semibold text-purple-800">Invoice Final</p>
                                        <p class="text-xs text-purple-600 mt-1">Telah diupload</p>
                                    </a>
                                    <a href="{{ asset('storage/' . $pengajuanDana->invoice) }}" download
                                       class="absolute top-2 right-2 p-1.5 bg-purple-200 text-purple-700 rounded-lg hover:bg-purple-600 hover:text-white transition shadow-sm">
                                        <i class="fas fa-download text-xs"></i>
                                    </a>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            {{-- 6. AREA TINDAKAN / ACTION --}}
            <div class="space-y-6 mt-8 pb-12">
                
                {{-- A. APPROVAL --}}
                @can('approve', $pengajuanDana)
                <div class="bg-white rounded-2xl shadow-xl border border-slate-200 p-6 md:p-8 border-t-4 border-t-blue-500">
                    <h3 class="text-xl font-bold text-slate-800 mb-6 flex items-center">
                        <i class="fas fa-gavel text-slate-800 mr-2"></i> Tindakan Persetujuan
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <form action="{{ route('pengajuan_dana.approve', $pengajuanDana) }}" method="POST" class="bg-green-50 p-6 rounded-2xl border border-green-100">
                            @csrf
                            <label class="block text-sm font-bold text-green-800 mb-2">
                                Catatan Persetujuan <span class="font-normal text-green-600">(Opsional)</span>
                            </label>
                            <textarea name="catatan_persetujuan" rows="3" 
                                class="w-full p-3 border border-green-200 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500 bg-white text-sm"
                                placeholder="Tulis catatan jika perlu..."></textarea>
                            <button type="submit" class="mt-4 w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-4 rounded-xl transition-colors shadow-md shadow-green-200 flex items-center justify-center gap-2">
                                <i class="fas fa-check-circle"></i> Setujui Pengajuan
                            </button>
                        </form>
                        
                        <form action="{{ route('pengajuan_dana.reject', $pengajuanDana) }}" method="POST" class="bg-red-50 p-6 rounded-2xl border border-red-100">
                            @csrf
                            <label class="block text-sm font-bold text-red-800 mb-2">
                                Catatan Penolakan <span class="text-red-600">*</span>
                            </label>
                            <textarea name="catatan_penolakan" rows="3" 
                                class="w-full p-3 border border-red-200 rounded-xl focus:ring-2 focus:ring-red-500 focus:border-red-500 bg-white text-sm"
                                placeholder="Alasan penolakan..." required></textarea>
                            <button type="submit" class="mt-4 w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-4 rounded-xl transition-colors shadow-md shadow-red-200 flex items-center justify-center gap-2">
                                <i class="fas fa-times-circle"></i> Tolak Pengajuan
                            </button>
                        </form>
                    </div>
                </div>
                @endcan

                {{-- B. PROSES PEMBAYARAN --}}
                @can('prosesPembayaran', $pengajuanDana)
                <div class="bg-white rounded-2xl shadow-xl border border-slate-200 p-6 md:p-8 border-t-4 border-t-indigo-500">
                    <h3 class="text-xl font-bold text-slate-800 mb-4 flex items-center">
                        <i class="fas fa-sync-alt text-indigo-600 mr-2"></i> Proses Pembayaran
                    </h3>
                    <p class="text-sm text-slate-500 mb-4">Ubah status menjadi "Sedang Diproses" sebelum melakukan transfer.</p>
                    <form action="{{ route('pengajuan_dana.proses_pembayaran', $pengajuanDana) }}" method="POST" class="bg-indigo-50 p-6 rounded-2xl border border-indigo-100">
                        @csrf
                        <label class="block text-sm font-bold text-indigo-800 mb-2">Catatan Proses (Opsional)</label>
                        <textarea name="catatan_proses" rows="2" class="w-full p-3 border border-indigo-200 rounded-xl focus:ring-2 focus:ring-indigo-500 bg-white text-sm mb-3"></textarea>
                        <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-4 rounded-xl transition flex items-center justify-center gap-2 shadow-lg shadow-indigo-200">
                            <i class="fas fa-sync-alt"></i> Tandai Sedang Diproses
                        </button>
                    </form>
                </div>
                @endcan

                {{-- C. UPLOAD BUKTI --}}
                @can('uploadBuktiTransfer', $pengajuanDana)
                <div class="bg-white rounded-2xl shadow-xl border border-slate-200 p-6 md:p-8 border-t-4 border-t-green-500">
                    <h3 class="text-xl font-bold text-slate-800 mb-4 flex items-center">
                        <i class="fas fa-file-invoice-dollar text-green-600 mr-2"></i> Penyelesaian & Upload Bukti
                    </h3>
                    <form action="{{ route('pengajuan_dana.upload_bukti_transfer', $pengajuanDana) }}" method="POST" enctype="multipart/form-data" class="bg-slate-50 p-6 rounded-2xl border border-slate-200">
                        @csrf
                        <label class="block text-sm font-bold text-slate-700 mb-2">Upload Bukti Transfer</label>
                        <input type="file" name="bukti_transfer" class="block w-full text-sm text-slate-500
                            file:mr-4 file:py-2.5 file:px-4
                            file:rounded-xl file:border-0
                            file:text-sm file:font-semibold
                            file:bg-green-50 file:text-green-700
                            hover:file:bg-green-100 mb-4" required>
                        <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-4 rounded-xl transition flex items-center justify-center gap-2 shadow-lg shadow-green-200">
                            <i class="fas fa-check-double"></i> Upload & Selesaikan
                        </button>
                    </form>
                </div>
                @endcan

                {{-- D. BATALKAN --}}
                @can('cancel', $pengajuanDana)
                <div class="bg-red-50 rounded-2xl border border-red-100 p-6 md:p-8 text-center">
                    <h3 class="text-lg font-bold text-red-800 mb-2">Batalkan Pengajuan?</h3>
                    <p class="text-sm text-red-600 mb-4">Tindakan ini tidak dapat diurungkan. Pengajuan akan ditandai sebagai dibatalkan.</p>
                    <form action="{{ route('pengajuan_dana.cancel', $pengajuanDana) }}" method="POST" onsubmit="return confirm('Yakin ingin membatalkan pengajuan ini?');">
                        @csrf
                        <button type="submit" class="bg-white border border-red-200 text-red-600 hover:bg-red-600 hover:text-white font-bold py-2.5 px-6 rounded-xl transition shadow-sm">
                            Batalkan Pengajuan
                        </button>
                    </form>
                </div>
                @endcan

            </div>

        </div>
    </div>
</x-layout-users>
<x-layout-users :title="$title">

    {{-- Library Chart & Axios --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

    <div class="min-h-screen bg-gradient-to-br from-sky-50 to-blue-100 font-sans text-sm pb-20">
        
        <div class="max-w-7xl mx-auto pt-0 px-0 md:px-0">

            {{-- TOMBOL KEMBALI --}}
            <div class="mb-6">
                <a href="{{ route('dashboard') }}" 
                class="inline-flex items-center justify-center w-auto h-10 px-4 rounded-lg bg-gradient-to-r from-blue-700 to-blue-600 text-white shadow-md hover:shadow-lg hover:brightness-110 transition-all gap-2"
                title="Kembali ke Dashboard">
                    <i class="fas fa-arrow-left"></i>
                    <span class="font-medium text-sm">Kembali</span>
                </a>
            </div>

            {{-- 2. HEADER --}}
            <div class="bg-[#001BB7] rounded-3xl shadow-xl shadow-blue-900/20 mb-8 overflow-hidden relative border border-blue-900/10">
                <div class="absolute top-0 right-0 -mt-10 -mr-10 w-64 h-64 bg-white opacity-10 rounded-full blur-3xl pointer-events-none z-0"></div>
                <div class="absolute bottom-0 left-0 -mb-10 -ml-10 w-40 h-40 bg-blue-400 opacity-20 rounded-full blur-2xl pointer-events-none z-0"></div>

                <div class="p-6 md:p-8 relative z-10 text-white">
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                        <div>
                            <div class="flex items-center gap-2 mb-2">
                                <span class="bg-white/20 backdrop-blur-md text-white text-[10px] font-bold px-3 py-1 rounded-full uppercase tracking-wider border border-white/30 shadow-sm">
                                    Rekapitulasi
                                </span>
                            </div>
                            <h1 class="text-2xl md:text-4xl font-extrabold tracking-tight text-white drop-shadow-sm mb-2">
                                Riwayat Absensi
                            </h1>
                            <p class="text-blue-100 opacity-90 text-sm max-w-xl leading-relaxed">
                                Pantau catatan kehadiran, keterlambatan, lembur, dan aktivitas harian Anda dalam satu periode.
                            </p>
                        </div>

                        {{-- Form Filter --}}
                        <div class="bg-white/10 backdrop-blur-md border border-white/20 p-4 rounded-2xl w-full md:w-auto min-w-[300px]">
                            <form method="GET" action="{{ route('rekap_absen.index') }}">
                                <div class="flex flex-col gap-3">
                                    <div class="grid grid-cols-2 gap-2">
                                        <div>
                                            <label class="block text-[10px] uppercase font-bold text-blue-200 mb-1">Bulan</label>
                                            <select name="bulan" class="w-full bg-white/90 text-gray-800 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 p-2 font-bold border-none cursor-pointer">
                                                @foreach($daftarBulan as $num => $nama)
                                                    <option value="{{ $num }}" {{ $num == $bulanDipilih ? 'selected' : '' }}>{{ $nama }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-[10px] uppercase font-bold text-blue-200 mb-1">Tahun</label>
                                            <select name="tahun" class="w-full bg-white/90 text-gray-800 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 p-2 font-bold border-none cursor-pointer">
                                                @foreach($daftarTahun as $tahun)
                                                    <option value="{{ $tahun }}" {{ $tahun == $tahunDipilih ? 'selected' : '' }}>{{ $tahun }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <button type="submit" class="w-full bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 rounded-lg shadow-md transition transform active:scale-95 flex items-center justify-center gap-2">
                                        <i class="fas fa-filter text-xs"></i> Tampilkan Data
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 3. GRID STATISTIK --}}
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-3 md:gap-4 mb-8">
                {{-- Card Hadir --}}
                <div class="bg-emerald-500 text-white p-4 rounded-2xl shadow-lg relative overflow-hidden group hover:-translate-y-1 transition-transform">
                    <div class="absolute top-0 right-0 -mt-2 -mr-2 w-12 h-12 bg-white/20 rounded-full blur-xl"></div>
                    <p class="text-xs font-bold uppercase tracking-wider text-emerald-100 mb-1">Hadir</p>
                    <div class="flex justify-between items-end">
                        <span class="text-3xl font-extrabold">{{ $rekap['hadir'] }}</span>
                        <i class="fas fa-check-circle text-2xl text-emerald-200/50 group-hover:scale-110 transition"></i>
                    </div>
                </div>
                {{-- Card Sakit --}}
                <div class="bg-rose-500 text-white p-4 rounded-2xl shadow-lg relative overflow-hidden group hover:-translate-y-1 transition-transform">
                    <div class="absolute top-0 right-0 -mt-2 -mr-2 w-12 h-12 bg-white/20 rounded-full blur-xl"></div>
                    <p class="text-xs font-bold uppercase tracking-wider text-rose-100 mb-1">Sakit</p>
                    <div class="flex justify-between items-end">
                        <span class="text-3xl font-extrabold">{{ $rekap['sakit'] }}</span>
                        <i class="fas fa-clinic-medical text-2xl text-rose-200/50 group-hover:scale-110 transition"></i>
                    </div>
                </div>
                {{-- Card Izin --}}
                <div class="bg-amber-500 text-white p-4 rounded-2xl shadow-lg relative overflow-hidden group hover:-translate-y-1 transition-transform">
                    <div class="absolute top-0 right-0 -mt-2 -mr-2 w-12 h-12 bg-white/20 rounded-full blur-xl"></div>
                    <p class="text-xs font-bold uppercase tracking-wider text-amber-100 mb-1">Izin</p>
                    <div class="flex justify-between items-end">
                        <span class="text-3xl font-extrabold">{{ $rekap['izin'] }}</span>
                        <i class="fas fa-envelope-open-text text-2xl text-amber-200/50 group-hover:scale-110 transition"></i>
                    </div>
                </div>
                {{-- Card Cuti --}}
                <div class="bg-purple-600 text-white p-4 rounded-2xl shadow-lg relative overflow-hidden group hover:-translate-y-1 transition-transform">
                    <div class="absolute top-0 right-0 -mt-2 -mr-2 w-12 h-12 bg-white/20 rounded-full blur-xl"></div>
                    <p class="text-xs font-bold uppercase tracking-wider text-purple-200 mb-1">Cuti</p>
                    <div class="flex justify-between items-end">
                        <span class="text-3xl font-extrabold">{{ $rekap['cuti'] }}</span>
                        <i class="fas fa-plane-departure text-2xl text-purple-300/50 group-hover:scale-110 transition"></i>
                    </div>
                </div>
                {{-- Card Lembur --}}
                <div class="bg-indigo-600 text-white p-4 rounded-2xl shadow-lg relative overflow-hidden group hover:-translate-y-1 transition-transform">
                    <div class="absolute top-0 right-0 -mt-2 -mr-2 w-12 h-12 bg-white/20 rounded-full blur-xl"></div>
                    <p class="text-xs font-bold uppercase tracking-wider text-indigo-200 mb-1">Lembur</p>
                    <div class="flex justify-between items-end">
                        <span class="text-3xl font-extrabold">{{ $rekap['lembur'] }}</span>
                        <i class="fas fa-business-time text-2xl text-indigo-300/50 group-hover:scale-110 transition"></i>
                    </div>
                </div>
                {{-- Card Alpa --}}
                <div class="bg-slate-600 text-white p-4 rounded-2xl shadow-lg relative overflow-hidden group hover:-translate-y-1 transition-transform">
                    <div class="absolute top-0 right-0 -mt-2 -mr-2 w-12 h-12 bg-white/20 rounded-full blur-xl"></div>
                    <p class="text-xs font-bold uppercase tracking-wider text-slate-200 mb-1">Alpa</p>
                    <div class="flex justify-between items-end">
                        <span class="text-3xl font-extrabold">{{ $rekap['alpa'] }}</span>
                        <i class="fas fa-user-slash text-2xl text-slate-300/50 group-hover:scale-110 transition"></i>
                    </div>
                </div>
                {{-- Card Terlambat --}}
                <div class="col-span-2 md:col-span-2 lg:col-span-1 bg-orange-500 text-white p-4 rounded-2xl shadow-lg relative overflow-hidden group hover:-translate-y-1 transition-transform">
                    <div class="absolute top-0 right-0 -mt-2 -mr-2 w-12 h-12 bg-white/20 rounded-full blur-xl"></div>
                    <p class="text-xs font-bold uppercase tracking-wider text-orange-100 mb-1">Total Terlambat</p>
                    <div class="flex justify-between items-end">
                        <div class="flex flex-col">
                            @php
                                $parts = explode(' ', $rekap['terlambat_formatted']);
                                $jam = isset($parts[0]) ? $parts[0] : 0;
                                $menit = isset($parts[2]) ? $parts[2] : 0;
                            @endphp
                            <span class="text-xl font-extrabold leading-none">{{ $jam }}<span class="text-xs font-normal opacity-80">j</span> {{ $menit }}<span class="text-xs font-normal opacity-80">m</span></span>
                        </div>
                        <i class="fas fa-exclamation-triangle text-2xl text-orange-200/50 group-hover:scale-110 transition"></i>
                    </div>
                </div>
            </div>

            {{-- 4. CHART SECTION --}}
            <div class="bg-white rounded-3xl shadow-lg border border-gray-100 p-6 md:p-8 mb-8 relative overflow-hidden">
                <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-blue-500 to-indigo-500"></div>
                <div class="flex flex-col md:flex-row items-center gap-8">
                    <div class="w-full md:w-1/3">
                        <h3 class="text-xl font-bold text-gray-800 mb-2">Visualisasi Kehadiran</h3>
                        <p class="text-gray-500 text-sm mb-4">Grafik representasi persentase kehadiran Anda bulan ini.</p>
                    </div>
                    <div class="w-full md:w-2/3 h-64 md:h-72 relative">
                        <canvas id="rekapAbsensiChart"></canvas>
                    </div>
                </div>
            </div>

            {{-- 5. DETAIL HARIAN --}}
            <div class="bg-white rounded-3xl shadow-lg border border-gray-100 overflow-hidden relative">
                <div class="px-6 py-5 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
                    <h3 class="font-bold text-gray-800 text-lg flex items-center gap-2">
                        <span class="bg-blue-100 text-blue-600 p-1.5 rounded-lg text-xs"><i class="fas fa-list"></i></span>
                        Detail Harian
                    </h3>
                </div>

                {{-- TAMPILAN DESKTOP (TABEL) --}}
                <div class="hidden md:block overflow-x-auto custom-scrollbar">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-gray-50/80 text-gray-500 uppercase font-bold text-[11px] tracking-widest border-b border-gray-200">
                            <tr>
                                <th class="px-6 py-4">Tanggal</th>
                                <th class="px-6 py-4 text-center">Status</th>
                                <th class="px-6 py-4 text-center">Jam Kerja</th>
                                <th class="px-6 py-4 text-center">Aktivitas</th>
                                <th class="px-6 py-4">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse($detailHarian as $item)
                                @php
                                    $isLate = false;
                                    if ($item->status == 'hadir' && $item->jam_masuk) {
                                         $waktuMasuk = \Carbon\Carbon::parse($item->jam_masuk, 'Asia/Jakarta');
                                         $batas = \Carbon\Carbon::createFromTimeString('08:00:00', 'Asia/Jakarta');
                                         $isLate = $waktuMasuk->gt($batas);
                                    }
                                    
                                    $rowClass = $item->is_weekend ? 'bg-slate-50/70' : 'hover:bg-blue-50/30 transition duration-200';
                                    
                                    $statusStyle = 'bg-gray-100 text-gray-600 border-gray-200';
                                    switch(strtolower($item->status)) {
                                        case 'hadir': $statusStyle = 'bg-emerald-100 text-emerald-700 border-emerald-200'; break;
                                        case 'sakit': $statusStyle = 'bg-rose-100 text-rose-700 border-rose-200'; break;
                                        case 'izin':  $statusStyle = 'bg-amber-100 text-amber-700 border-amber-200'; break;
                                        case 'cuti':  $statusStyle = 'bg-purple-100 text-purple-700 border-purple-200'; break;
                                        case 'lembur':$statusStyle = 'bg-indigo-100 text-indigo-700 border-indigo-200'; break;
                                        case 'alpa':  $statusStyle = 'bg-slate-200 text-slate-700 border-slate-300'; break;
                                    }
                                @endphp
                                <tr class="{{ $rowClass }}">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex flex-col">
                                            <span class="text-sm font-bold text-gray-800">{{ $item->tanggal->format('d') }}</span>
                                            <span class="text-xs text-gray-500">{{ $item->tanggal->translatedFormat('F Y') }}</span>
                                            <span class="text-[10px] font-bold uppercase mt-1 {{ $item->is_weekend ? 'text-red-400' : 'text-blue-500' }}">
                                                {{ $item->tanggal->translatedFormat('l') }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center align-middle">
                                        <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wide border {{ $statusStyle }}">
                                            {{ $item->status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center align-middle">
                                        <div class="inline-flex items-center bg-gray-50 rounded-lg px-3 py-1.5 border border-gray-200 shadow-sm">
                                            <span class="font-mono font-bold text-emerald-600 text-xs">{{ $item->jam_masuk ? \Carbon\Carbon::parse($item->jam_masuk)->format('H:i') : '--:--' }}</span>
                                            <span class="text-gray-300 mx-2">|</span>
                                            <span class="font-mono font-bold text-red-500 text-xs">{{ $item->jam_keluar ? \Carbon\Carbon::parse($item->jam_keluar)->format('H:i') : '--:--' }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center align-middle">
                                        @if($item->jumlah_aktivitas > 0)
                                            <button onclick="openModalAktivitas('{{ $item->tanggal->toDateString() }}')" class="group inline-flex items-center px-3 py-1.5 text-xs font-bold text-blue-600 bg-blue-50 border border-blue-200 rounded-lg hover:bg-blue-600 hover:text-white hover:border-blue-600 transition-all shadow-sm">
                                                <i class="fas fa-clipboard-list mr-1.5 group-hover:text-white"></i> {{ $item->jumlah_aktivitas }} Log
                                            </button>
                                        @else
                                            <span class="text-gray-300 text-xs italic">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 align-middle">
                                        <div class="text-sm text-gray-600 truncate max-w-xs" title="{{ $item->keterangan }}">
                                            @if($isLate)
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-orange-100 text-orange-700 mr-1 border border-orange-200">Terlambat</span>
                                            @endif
                                            {{ $item->keterangan ?? '-' }}
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center text-gray-400 bg-gray-50/30">
                                        <div class="flex flex-col items-center">
                                            <i class="far fa-calendar-times text-3xl mb-2 opacity-50"></i>
                                            <span>Tidak ada data absensi untuk periode ini.</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- TAMPILAN MOBILE (KARTU) --}}
                <div class="block md:hidden bg-gray-50 p-4 space-y-4">
                    @forelse($detailHarian as $item)
                        @php
                            $isLate = false;
                            if ($item->status == 'hadir' && $item->jam_masuk) {
                                $waktuMasuk = \Carbon\Carbon::parse($item->jam_masuk, 'Asia/Jakarta');
                                $batas = \Carbon\Carbon::createFromTimeString('08:00:00', 'Asia/Jakarta');
                                $isLate = $waktuMasuk->gt($batas);
                            }

                            $borderColor = 'border-gray-300'; $headerBg = 'bg-gray-100 text-gray-600';
                            switch(strtolower($item->status)) {
                                case 'hadir': $borderColor = 'border-emerald-500'; $headerBg = 'bg-emerald-100 text-emerald-700'; break;
                                case 'sakit': $borderColor = 'border-rose-500'; $headerBg = 'bg-rose-100 text-rose-700'; break;
                                case 'izin':  $borderColor = 'border-amber-500'; $headerBg = 'bg-amber-100 text-amber-700'; break;
                                case 'lembur':$borderColor = 'border-indigo-500'; $headerBg = 'bg-indigo-100 text-indigo-700'; break;
                                case 'alpa':  $borderColor = 'border-slate-400'; $headerBg = 'bg-slate-200 text-slate-700'; break;
                            }
                        @endphp
                        
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden relative">
                            <div class="absolute left-0 top-0 bottom-0 w-1.5 {{ str_replace('border-', 'bg-', $borderColor) }}"></div>
                            <div class="pl-5 p-4">
                                <div class="flex justify-between items-start mb-3">
                                    <div>
                                        <div class="flex items-baseline gap-2">
                                            <span class="text-lg font-bold text-gray-800">{{ $item->tanggal->format('d') }}</span>
                                            <span class="text-xs font-medium text-gray-500 uppercase">{{ $item->tanggal->translatedFormat('M Y') }}</span>
                                        </div>
                                        <p class="text-[11px] font-bold uppercase tracking-wide {{ $item->is_weekend ? 'text-red-500' : 'text-blue-500' }}">
                                            {{ $item->tanggal->translatedFormat('l') }}
                                        </p>
                                    </div>
                                    <span class="px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-wide {{ $headerBg }}">
                                        {{ $item->status }}
                                    </span>
                                </div>

                                <div class="grid grid-cols-2 gap-px bg-gray-100 rounded-lg overflow-hidden border border-gray-200 mb-3">
                                    <div class="bg-white p-2 text-center">
                                        <span class="block text-[9px] uppercase text-gray-400 font-bold mb-0.5">Masuk</span>
                                        <span class="block text-sm font-mono font-bold text-emerald-600">
                                            {{ $item->jam_masuk ? \Carbon\Carbon::parse($item->jam_masuk)->format('H:i') : '--:--' }}
                                        </span>
                                    </div>
                                    <div class="bg-white p-2 text-center">
                                        <span class="block text-[9px] uppercase text-gray-400 font-bold mb-0.5">Keluar</span>
                                        <span class="block text-sm font-mono font-bold text-red-500">
                                            {{ $item->jam_keluar ? \Carbon\Carbon::parse($item->jam_keluar)->format('H:i') : '--:--' }}
                                        </span>
                                    </div>
                                </div>

                                @if($item->keterangan || $isLate)
                                    <div class="mb-3 text-xs text-gray-600 bg-gray-50 p-2.5 rounded-lg border border-gray-100 flex items-start gap-2">
                                        <i class="fas fa-info-circle text-blue-400 mt-0.5"></i>
                                        <div class="leading-relaxed">
                                            @if($isLate) <span class="text-orange-600 font-bold block mb-0.5">Terlambat</span> @endif
                                            {{ $item->keterangan ?? 'Tidak ada keterangan.' }}
                                        </div>
                                    </div>
                                @endif

                                @if($item->jumlah_aktivitas > 0)
                                    <button onclick="openModalAktivitas('{{ $item->tanggal->toDateString() }}')" class="w-full flex items-center justify-center text-xs font-bold text-blue-700 border border-blue-200 bg-blue-50/50 hover:bg-blue-100 py-2.5 rounded-lg transition shadow-sm active:scale-95">
                                        <i class="fas fa-eye mr-2"></i> Lihat {{ $item->jumlah_aktivitas }} Catatan Aktivitas
                                    </button>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-12 text-gray-400">
                            <i class="far fa-calendar-times text-2xl opacity-50 mb-2"></i>
                            <p class="text-sm font-medium">Tidak ada data absensi.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL AKTIVITAS --}}
    <div id="modalAktivitas" class="fixed inset-0 z-[9999] hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-gray-900 bg-opacity-60 transition-opacity backdrop-blur-sm" onclick="closeModalAktivitas()"></div>
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-gray-200">
                <div class="bg-blue-600 px-5 py-4 flex justify-between items-center">
                    <h3 class="text-lg font-bold text-white flex items-center gap-2"><i class="fas fa-list-alt"></i> Detail Aktivitas</h3>
                    <button onclick="closeModalAktivitas()" class="text-blue-100 hover:text-white transition bg-white/10 w-8 h-8 rounded-full flex items-center justify-center"><i class="fas fa-times"></i></button>
                </div>
                <div class="bg-blue-50 px-5 py-3 border-b border-blue-100 flex items-center gap-2">
                    <i class="far fa-calendar-alt text-blue-500"></i>
                    <p class="text-sm font-bold text-blue-800" id="modal-date">Memuat tanggal...</p>
                </div>
                <div class="px-5 py-5 max-h-[60vh] overflow-y-auto bg-gray-50 custom-scrollbar">
                    <div id="loading-aktivitas" class="hidden text-center py-8">
                        <div class="inline-block w-8 h-8 border-4 border-blue-200 border-t-blue-600 rounded-full animate-spin mb-2"></div>
                        <p class="text-xs text-gray-500 font-medium">Sedang memuat data...</p>
                    </div>
                    <div id="list-aktivitas" class="space-y-4"></div>
                </div>
                <div class="bg-white px-5 py-4 sm:flex sm:flex-row-reverse border-t border-gray-100">
                    <button type="button" onclick="closeModalAktivitas()" class="w-full inline-flex justify-center rounded-xl bg-gray-100 px-4 py-2.5 text-sm font-bold text-gray-700 shadow-sm hover:bg-gray-200 sm:w-auto transition">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            // --- CHART LOGIC ---
            document.addEventListener('DOMContentLoaded', function() {
                const ctx = document.getElementById('rekapAbsensiChart');
                if (ctx) {
                    const rekapData = @json($rekap);
                    new Chart(ctx, {
                        type: 'doughnut',
                        data: {
                            labels: ['Hadir', 'Sakit', 'Izin', 'Cuti', 'Alpa', 'Lembur'],
                            datasets: [{
                                data: [rekapData.hadir, rekapData.sakit, rekapData.izin, rekapData.cuti, rekapData.alpa, rekapData.lembur],
                                backgroundColor: ['#10B981', '#F43F5E', '#F59E0B', '#9333EA', '#64748B', '#4F46E5'],
                                borderWidth: 2,
                                borderColor: '#ffffff'
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            cutout: '70%',
                            plugins: { legend: { position: 'bottom', labels: { usePointStyle: true, padding: 20, font: { size: 11, family: "'Inter', sans-serif" } } } }
                        }
                    });
                }
            });

            // --- MODAL LOGIC (FIXED URL) ---
            const modal = document.getElementById('modalAktivitas');
            const listContainer = document.getElementById('list-aktivitas');
            const loadingSpinner = document.getElementById('loading-aktivitas');
            const modalDate = document.getElementById('modal-date');

            function openModalAktivitas(date) {
                modal.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
                listContainer.innerHTML = ''; 
                loadingSpinner.classList.remove('hidden');
                
                const d = new Date(date);
                const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
                modalDate.textContent = d.toLocaleDateString('id-ID', options);

                // FIXED: MENGGUNAKAN ROUTE HELPER UNTUK URL YANG BENAR
                axios.get('{{ route('aktivitas.getJson') }}', {
                    params: { start: date, user_id: '{{ auth()->id() }}' }
                })
                .then(function (response) {
                    loadingSpinner.classList.add('hidden');
                    const data = response.data;

                    if (data.length === 0) {
                        listContainer.innerHTML = `
                            <div class="text-center py-8 bg-white rounded-xl border border-dashed border-gray-300">
                                <i class="far fa-sticky-note text-2xl text-gray-300 mb-2"></i>
                                <p class="text-gray-400 text-sm">Tidak ada catatan aktivitas.</p>
                            </div>`;
                    } else {
                        data.forEach(item => {
                            const props = item.extendedProps;
                            const photoHtml = props.photo_url 
                                ? `<div class="mt-3 rounded-lg overflow-hidden border border-gray-200 relative group">
                                     <img src="${props.photo_url}" class="w-full h-40 object-cover transform transition group-hover:scale-105">
                                     <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent opacity-0 group-hover:opacity-100 transition duration-300"></div>
                                   </div>` 
                                : '';
                            
                            const html = `
                                <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm relative pl-5">
                                    <div class="absolute left-0 top-4 bottom-4 w-1 bg-blue-500 rounded-r"></div>
                                    <div class="flex justify-between items-start mb-2">
                                        <h4 class="font-bold text-gray-800 text-sm leading-tight">${item.title}</h4>
                                        <span class="flex-shrink-0 text-[10px] bg-gray-100 px-2 py-1 rounded text-gray-500 font-mono border border-gray-200">
                                            ${new Date(item.start).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}
                                        </span>
                                    </div>
                                    <p class="text-xs text-gray-600 whitespace-pre-wrap leading-relaxed">${props.keterangan}</p>
                                    ${photoHtml}
                                </div>
                            `;
                            listContainer.innerHTML += html;
                        });
                    }
                })
                .catch(function (error) {
                    loadingSpinner.classList.add('hidden');
                    listContainer.innerHTML = '<div class="p-4 bg-red-50 text-red-600 rounded-lg text-sm text-center">Gagal memuat data aktivitas.</div>';
                });
            }

            function closeModalAktivitas() {
                modal.classList.add('hidden');
                document.body.style.overflow = 'auto';
            }
        </script>
        <style>
            .custom-scrollbar::-webkit-scrollbar { width: 5px; height: 5px; }
            .custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; }
            .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
            .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        </style>
    @endpush
</x-layout-users>
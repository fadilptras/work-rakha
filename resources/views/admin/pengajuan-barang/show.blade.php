<x-layout-admin>
    <x-slot:title>Detail Pengajuan Barang</x-slot:title>

    <div class="p-6">
        {{-- TOMBOL KEMBALI --}}
        @php
            $isFromNotif = str_contains(url()->previous(), 'notifikasi');
            $backUrl = $isFromNotif ? route('notifikasi.index') : route('admin.pengajuan_barang.index');
            $backText = $isFromNotif ? 'Kembali ke Notifikasi' : 'Kembali ke Rekap Barang';
        @endphp
        <x-back-button href="{{ $backUrl }}">{{ $backText }}</x-back-button>

        
        

        <div class="bg-zinc-800 rounded-lg shadow-lg p-6 md:p-8 text-zinc-300 border border-zinc-700">
            
            {{-- HEADER SECTIONS (TETAP DI ATAS FULL WIDTH) --}}
            <div class="flex flex-col md:flex-row justify-between md:items-center mb-6 gap-4">
                <div class="flex items-center gap-4">
                    <div class="h-12 w-12 rounded-full bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-400 text-xl font-bold">
                        <i class="fas fa-boxes"></i>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-white">{{ $pengajuanBarang->judul_pengajuan }}</h2>
                        <p class="text-xs text-zinc-400 mt-0.5">Diajukan pada: {{ $pengajuanBarang->created_at->translatedFormat('d F Y, H:i') }} WIB</p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <span class="text-sm font-medium text-zinc-400">Status Akhir:</span>
                    @php
                        $statusClass = match($pengajuanBarang->status) {
                            'selesai', 'disetujui' => 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30',
                            'ditolak' => 'bg-red-500/20 text-red-400 border-red-500/30',
                            'diproses' => 'bg-blue-500/20 text-blue-400 border-blue-500/30',
                            'dibatalkan' => 'bg-zinc-500/20 text-zinc-400 border-zinc-500/30',
                            default => 'bg-amber-500/20 text-amber-400 border-amber-500/30',
                        };
                    @endphp
                    <span class="px-4 py-1.5 font-semibold leading-tight rounded-full text-sm uppercase tracking-wider border {{ $statusClass }}">
                        {{ str_replace('_', ' ', $pengajuanBarang->status) }}
                    </span>
                </div>
            </div>
            
            <hr class="my-6 border-zinc-700">

            {{-- DUA KOLOM LAYOUT --}}
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                
                {{-- KOLOM KIRI: INFO UTAMA (8/12) --}}
                <div class="lg:col-span-7 xl:col-span-8 space-y-8">
                    
                    {{-- INFORMASI UTAMA PEMOHON --}}
                    <div class="bg-zinc-900/50 rounded-xl border border-zinc-700/50 p-5 sm:p-6">
                        <h3 class="text-sm font-bold text-zinc-300 mb-5 border-b border-zinc-700/50 pb-3"><i class="fas fa-user-circle mr-2 text-zinc-400"></i>Informasi Pemohon</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                            <div>
                                <label class="block text-xs font-medium text-zinc-500 mb-1">Nama Pemohon</label>
                                <p class="font-semibold text-white text-base">{{ $pengajuanBarang->user->name ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-zinc-500 mb-1">Divisi</label>
                                <p class="font-semibold text-white text-base">{{ $pengajuanBarang->divisi ?? '-' }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-zinc-500 mb-1">Nomor Surat</label>
                                <p class="font-semibold text-amber-400 text-base">{{ $pengajuanBarang->nomor_surat ?? '-' }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- RINCIAN BARANG --}}
                    <div class="bg-zinc-900/50 rounded-xl border border-zinc-700/50 p-5 sm:p-6">
                        <h3 class="text-sm font-bold text-zinc-300 mb-5 border-b border-zinc-700/50 pb-3"><i class="fas fa-list mr-2 text-zinc-400"></i>Rincian Barang Diajukan</h3>
                        <div class="overflow-x-auto rounded-lg border border-zinc-700/80 bg-zinc-900/40">
                            <table class="min-w-full divide-y divide-zinc-700/80 text-sm">
                                <thead class="bg-zinc-800/80">
                                    <tr>
                                        <th class="px-4 py-3 text-left font-semibold text-zinc-400 w-10 text-xs uppercase">No</th>
                                        <th class="px-4 py-3 text-left font-semibold text-zinc-400 text-xs uppercase">Nama Barang</th>
                                        <th class="px-4 py-3 text-center font-semibold text-zinc-400 w-28 text-xs uppercase">Diminta</th>
                                        <th class="px-4 py-3 text-center font-semibold text-zinc-400 w-28 text-xs uppercase">Diproses</th>
                                        <th class="px-4 py-3 text-left font-semibold text-zinc-400 text-xs uppercase">Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-zinc-700/60">
                                    @if(is_array($pengajuanBarang->rincian_barang))
                                        @foreach($pengajuanBarang->rincian_barang as $index => $item)
                                            <tr class="hover:bg-zinc-700/30 transition-colors">
                                                <td class="px-4 py-3 text-zinc-400 text-center">{{ $loop->iteration }}</td>
                                                <td class="px-4 py-3 text-white font-medium">
                                                    {{ $item['nama_barang'] ?? $item['deskripsi'] ?? '-' }}
                                                    @if(!empty($item['supplier']))
                                                    <div class="text-[10px] text-zinc-500 mt-0.5"><i class="fas fa-building mr-1"></i>{{ $item['supplier'] }}</div>
                                                    @endif
                                                </td>
                                                <td class="px-4 py-3 text-center font-bold text-amber-400 bg-amber-500/5">{{ $item['jumlah'] ?? 0 }} {{ $item['satuan'] ?? '' }}</td>
                                                <td class="px-4 py-3 text-center font-bold text-emerald-400 bg-emerald-500/5">
                                                    {{ $item['jumlah_diproses'] ?? '0' }} {{ $item['satuan'] ?? '' }}
                                                </td>
                                                <td class="px-4 py-3 text-zinc-300 font-medium text-xs">{{ $item['keterangan'] ?? '-' }}</td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr><td colspan="5" class="px-4 py-6 text-center text-zinc-500">Tidak ada rincian barang.</td></tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                        @if(!empty($pengajuanBarang->catatan_pemohon))
                        <div class="mt-4 p-3 bg-amber-500/5 border border-amber-500/20 rounded-lg">
                            <span class="text-[10px] font-bold text-amber-500 uppercase block mb-1"><i class="fas fa-sticky-note mr-1"></i> Catatan Khusus Pemohon:</span>
                            <p class="text-xs text-zinc-300">{{ $pengajuanBarang->catatan_pemohon }}</p>
                        </div>
                        @endif
                    </div>

                    {{-- TIMELINE 4 TAHAP APPROVAL --}}
                    <div class="bg-zinc-900/50 rounded-xl border border-zinc-700/50 p-5 sm:p-6">
                        <h3 class="text-sm font-bold text-zinc-300 mb-5 border-b border-zinc-700/50 pb-3"><i class="fas fa-sitemap mr-2 text-zinc-400"></i>Alur Persetujuan (4 Tahap)</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            @foreach([
                                1 => ['nama' => 'Tahap 1 (Atasan)', 'user' => $pengajuanBarang->approver1, 'status' => $pengajuanBarang->status_appr_1, 'catatan' => $pengajuanBarang->catatan_approver_1, 'tgl' => $pengajuanBarang->tanggal_approved_1],
                                2 => ['nama' => 'Tahap 2 (Direktur)', 'user' => $pengajuanBarang->approver2, 'status' => $pengajuanBarang->status_appr_2, 'catatan' => $pengajuanBarang->catatan_approver_2, 'tgl' => $pengajuanBarang->tanggal_approved_2],
                                3 => ['nama' => 'Tahap 3 (Finance)', 'user' => $pengajuanBarang->approver3, 'status' => $pengajuanBarang->status_appr_3, 'catatan' => $pengajuanBarang->catatan_approver_3, 'tgl' => $pengajuanBarang->tanggal_approved_3],
                                4 => ['nama' => 'Tahap 4 (Admin/Purchasing)', 'user' => $pengajuanBarang->approver4, 'status' => $pengajuanBarang->status_appr_4, 'catatan' => $pengajuanBarang->catatan_approver_4, 'tgl' => $pengajuanBarang->tanggal_approved_4],
                            ] as $stage => $data)
                                @php
                                    $borderAppr = match($data['status']) {
                                        'disetujui', 'selesai' => 'border-emerald-500 bg-emerald-500/5',
                                        'ditolak' => 'border-red-500 bg-red-500/5',
                                        'skipped' => 'border-zinc-600 bg-zinc-800/40',
                                        default => 'border-amber-500 bg-amber-500/5',
                                    };
                                    $badgeAppr = match($data['status']) {
                                        'disetujui', 'selesai' => 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30',
                                        'ditolak' => 'bg-red-500/20 text-red-400 border-red-500/30',
                                        'skipped' => 'bg-zinc-600/20 text-zinc-400 border-zinc-600/30',
                                        default => 'bg-amber-500/20 text-amber-400 border-amber-500/30',
                                    };
                                @endphp
                                <div class="rounded-lg border border-zinc-700/80 p-3 border-l-[3px] {{ $borderAppr }} bg-zinc-800/30 shadow-sm">
                                    <div class="flex justify-between items-start mb-2 gap-2">
                                        <span class="text-[10px] font-bold text-zinc-400 uppercase tracking-wider">{{ $data['nama'] }}</span>
                                        <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase border {{ $badgeAppr }}">
                                            {{ $data['status'] ?? 'menunggu' }}
                                        </span>
                                    </div>
                                    <h4 class="text-xs font-bold text-white truncate">{{ $data['user']->name ?? 'Belum Ditentukan' }}</h4>
                                    
                                    @if($data['tgl'])
                                        <p class="text-[9px] text-zinc-500 mt-1 flex items-center gap-1">
                                            <i class="fas fa-clock"></i> {{ \Carbon\Carbon::parse($data['tgl'])->translatedFormat('d/m/y H:i') }}
                                        </p>
                                    @endif

                                    @if($data['catatan'])
                                    <div class="mt-2 p-1.5 bg-zinc-900/60 rounded border border-zinc-700/50 text-[10px]">
                                        <span class="text-zinc-500 block uppercase font-bold text-[8px]">Catatan:</span>
                                        <p class="text-zinc-300 italic">"{{ $data['catatan'] }}"</p>
                                    </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- KOLOM KANAN: PANEL KONTROL & MONITORING (4/12) --}}
                <div class="lg:col-span-5 xl:col-span-4 space-y-6">
                    
                    {{-- TOMBOL PDF --}}
                    <a href="{{ route('admin.pengajuan_barang.downloadPdf', $pengajuanBarang->id) }}" class="flex items-center justify-center gap-2 w-full py-3 bg-red-600 hover:bg-red-700 text-white font-bold rounded-xl shadow-lg transition text-sm">
                        <i class="fas fa-file-pdf"></i> Cetak PDF Pengajuan
                    </a>

                    {{-- FORM TINDAKAN ADMIN (JIKA ADA GILIRAN) --}}
                    @php
                        $admin = Auth::user();
                        $app1Done = ($pengajuanBarang->status_appr_1 != 'menunggu');
                        $app2Done = ($pengajuanBarang->status_appr_2 != 'menunggu');
                        $app3Done = ($pengajuanBarang->status_appr_3 != 'menunggu');

                        $isAppr1 = ($admin->id == $pengajuanBarang->approver_barang_1_id && $pengajuanBarang->status_appr_1 == 'menunggu');
                        $isAppr2 = ($admin->id == $pengajuanBarang->approver_barang_2_id && $pengajuanBarang->status_appr_2 == 'menunggu' && $app1Done);
                        $isAppr3 = ($admin->id == $pengajuanBarang->approver_barang_3_id && $pengajuanBarang->status_appr_3 == 'menunggu' && $app1Done && $app2Done);
                        
                        $showApprovalForm = $isAppr1 || $isAppr2 || $isAppr3;
                    @endphp

                    @if($showApprovalForm)
                    <div class="bg-amber-500/10 rounded-xl border border-amber-500/40 p-5 shadow-xl animate-pulse-subtle">
                        <h3 class="text-sm font-bold text-amber-400 mb-2 flex items-center gap-2">
                            <i class="fas fa-gavel"></i> Form Persetujuan (Giliran Anda)
                        </h3>
                        <p class="text-[10px] text-zinc-400 mb-4 leading-relaxed">Berikan keputusan dan catatan persetujuan untuk tahap ini.</p>

                        <form action="{{ route('admin.pengajuan_barang.updateStatus', $pengajuanBarang->id) }}" method="POST" class="mb-3">
                            @csrf @method('PUT')
                            <textarea name="alasan" rows="2" class="w-full p-2.5 bg-zinc-900 border border-emerald-500/30 rounded-lg text-xs text-white focus:ring-emerald-500 mb-3" placeholder="Catatan persetujuan (opsional)..."></textarea>
                            <input type="hidden" name="status" value="disetujui">
                            <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2.5 rounded-lg transition shadow-md flex items-center justify-center gap-2 text-xs">
                                <i class="fas fa-check-circle"></i> Setujui
                            </button>
                        </form>

                        <form action="{{ route('admin.pengajuan_barang.updateStatus', $pengajuanBarang->id) }}" method="POST">
                            @csrf @method('PUT')
                            <textarea name="alasan" rows="2" class="w-full p-2.5 bg-zinc-900 border border-red-500/30 rounded-lg text-xs text-white focus:ring-red-500 mb-3" placeholder="Alasan penolakan (wajib)..." required></textarea>
                            <input type="hidden" name="status" value="ditolak">
                            <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-2.5 rounded-lg transition shadow-md flex items-center justify-center gap-2 text-xs" onclick="return confirm('Yakin ingin menolak pengajuan ini?');">
                                <i class="fas fa-times-circle"></i> Tolak
                            </button>
                        </form>
                    </div>
                    @endif

                    @if(Auth::id() == $pengajuanBarang->approver_barang_4_id || (Auth::check() && Auth::user()->role === 'admin'))
                        @if(in_array($pengajuanBarang->status, ['disetujui', 'diproses', 'selesai']))
                            @php
                                $adaSisa = false;
                                foreach($pengajuanBarang->rincian_barang ?? [] as $item) {
                                    $sisaCheck = ($item['jumlah'] ?? 0) - ($item['jumlah_diproses'] ?? 0);
                                    if ($sisaCheck > 0) { $adaSisa = true; break; }
                                }
                            @endphp
                            
                            <div class="bg-emerald-500/10 rounded-xl border border-emerald-500/40 p-5 shadow-xl mb-6">
                                <h3 class="text-sm font-bold text-emerald-400 mb-2 flex items-center gap-2">
                                    <i class="fas fa-boxes"></i> Proses Barang (Buat Termin Baru)
                                </h3>
                                <p class="text-[10px] text-zinc-400 mb-4 leading-relaxed">Pilih jumlah sisa barang yang akan diproses / dikirim saat ini untuk membentuk <strong>Termin Pengiriman Baru</strong>.</p>

                                @if($adaSisa)
                                <form action="{{ route('admin.pengajuan_barang.konfirmasiProses', $pengajuanBarang->id) }}" method="POST">
                                    @csrf
                                    <div class="space-y-3 mb-4 max-h-60 overflow-y-auto pr-2">
                                        @foreach($pengajuanBarang->rincian_barang ?? [] as $index => $item)
                                            @php
                                                $diminta = $item['jumlah'] ?? 0;
                                                $diproses = $item['jumlah_diproses'] ?? 0;
                                                $sisa = $diminta - $diproses;
                                                $satuan = $item['satuan'] ?? '';
                                            @endphp
                                            
                                            @if($sisa > 0)
                                            <div class="flex items-center justify-between gap-3 bg-zinc-900/60 p-3 rounded-lg border border-emerald-700/50">
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-xs font-bold text-white truncate">{{ $item['nama_barang'] ?? $item['deskripsi'] ?? '-' }}</p>
                                                    <div class="flex gap-3 text-[10px] mt-1">
                                                        <span class="text-zinc-400">Total Diminta: <strong>{{ $diminta }}</strong></span>
                                                        <span class="text-emerald-500">Menunggu Diproses: <strong>{{ $sisa }} {{ $satuan }}</strong></span>
                                                    </div>
                                                </div>
                                                <div class="w-28 relative">
                                                    <input type="number" name="jumlah_diproses[{{ $index }}]" value="0" min="0" max="{{ $sisa }}" class="w-full bg-zinc-800 border border-emerald-500/50 rounded-md text-sm text-emerald-400 font-black p-2 text-center focus:ring-emerald-500" title="Jumlah yang diproses di termin ini">
                                                    <div class="absolute inset-y-0 right-0 pr-2 flex items-center pointer-events-none text-[9px] text-emerald-600/70 font-bold">{{ $satuan }}</div>
                                                </div>
                                            </div>
                                            @endif
                                        @endforeach
                                    </div>
                                    <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3 rounded-lg transition shadow-md flex items-center justify-center gap-2 text-xs">
                                        <i class="fas fa-layer-group"></i> Simpan Sebagai Termin Baru
                                    </button>
                                </form>
                                @else
                                <div class="bg-zinc-900/80 rounded-xl p-5 border border-emerald-500/30 text-center flex flex-col items-center justify-center gap-2">
                                    <div class="w-10 h-10 bg-emerald-500/20 text-emerald-400 rounded-full flex items-center justify-center text-lg mb-1"><i class="fas fa-check-double"></i></div>
                                    <h5 class="text-xs font-bold text-emerald-400">Semua Barang Telah Lunas Diproses!</h5>
                                    <p class="text-[10px] text-zinc-400">Tidak ada sisa barang yang perlu dibuatkan termin baru.</p>
                                </div>
                                @endif
                            </div>
                        @endif
                    @endif

                    {{-- MIGRASI TERMIN LAMA --}}
                    @if(empty($pengajuanBarang->data_termin) && in_array($pengajuanBarang->status, ['diproses', 'selesai']))
                    <div class="bg-zinc-900/80 rounded-xl border border-sky-500/30 p-5 mb-6 text-center">
                        <div class="w-12 h-12 bg-sky-500/20 text-sky-400 rounded-full flex items-center justify-center text-xl mx-auto mb-3"><i class="fas fa-magic"></i></div>
                        <h4 class="text-sm font-bold text-sky-400 mb-1">Fitur Termin Tersedia</h4>
                        <p class="text-[10px] text-zinc-400 mb-4 max-w-xs mx-auto">Pengajuan lama ini belum masuk ke sistem termin. Migrasikan untuk menggunakan pelacakan pengiriman yang lebih rinci.</p>
                        <form action="{{ route('admin.pengajuan_barang.migrasiTerminLama', $pengajuanBarang->id) }}" method="POST">
                            @csrf
                            <button type="submit" onclick="return confirm('Rangkum semua barang jadi Termin 1?')" class="bg-sky-600 hover:bg-sky-700 text-white font-bold text-xs px-5 py-2.5 rounded-lg transition shadow-md inline-flex items-center gap-2">
                                Migrasi ke Termin 1
                            </button>
                        </form>
                    </div>
                    </div>
                @endif

                </div> <!-- Tutup Kolom Kanan -->
            </div> <!-- Tutup Grid 2 Kolom -->

            {{-- STATUS MONITORING & DAFTAR TERMIN (NEW UI) --}}
            <div class="mt-8 border-t border-zinc-700/80 pt-8">
                <div class="mb-8 mt-2">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-xl bg-sky-500/20 text-sky-400 flex items-center justify-center text-xl border border-sky-500/30"><i class="fas fa-shipping-fast"></i></div>
                                <div>
                                    <h3 class="text-lg font-bold text-zinc-200 uppercase tracking-wider">Pemantauan & Pelacakan</h3>
                                    <p class="text-xs text-zinc-400 mt-1">Lacak status setiap pengiriman barang (Termin).</p>
                                </div>
                            </div>
                            <span class="px-4 py-2 rounded-xl text-sm font-bold bg-sky-500/10 text-sky-400 border border-sky-500/30 shadow-sm flex items-center gap-2">
                                <div class="w-2 h-2 rounded-full bg-sky-400 animate-pulse"></div>
                                {{ $pengajuanBarang->status_monitoring ?? 'Menunggu Diproses' }}
                            </span>
                        </div>

                        {{-- GLOBAL SUMMARY --}}
                        @php
                            $totalDiminta = 0;
                            $totalDikirim = 0;
                            foreach($pengajuanBarang->rincian_barang ?? [] as $item) {
                                $totalDiminta += ($item['jumlah'] ?? 0);
                                $totalDikirim += ($item['jumlah_diproses'] ?? 0);
                            }
                            $totalSisa = $totalDiminta - $totalDikirim;
                            $percentProgress = $totalDiminta > 0 ? round(($totalDikirim / $totalDiminta) * 100) : 0;
                        @endphp
                        <div class="bg-zinc-900/50 rounded-xl border-l-4 border-l-sky-500 border border-zinc-700/50 p-5 mb-6">
                            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
                                <div class="flex-1">
                                    <h4 class="text-sm font-bold text-zinc-300 mb-2 uppercase tracking-wide">Proses Keseluruhan</h4>
                                    <div class="w-full bg-zinc-800 rounded-full h-3 mb-2 overflow-hidden border border-zinc-700">
                                        <div class="bg-sky-500 h-3 rounded-full transition-all duration-1000 relative" style="width: {{ $percentProgress }}%"></div>
                                    </div>
                                    <div class="flex justify-between text-[11px] font-bold">
                                        <span class="text-sky-400">{{ $percentProgress }}% Diproses</span>
                                        <span class="text-zinc-500">100%</span>
                                    </div>
                                </div>
                                <div class="flex gap-4 md:gap-6 shrink-0">
                                    <div class="text-center">
                                        <span class="block text-[10px] font-bold text-zinc-500 uppercase mb-1">Diminta</span>
                                        <span class="text-lg font-black text-zinc-200">{{ $totalDiminta }}</span>
                                    </div>
                                    <div class="w-px bg-zinc-700"></div>
                                    <div class="text-center">
                                        <span class="block text-[10px] font-bold text-emerald-400 uppercase mb-1">Dikirim</span>
                                        <span class="text-lg font-black text-emerald-500">{{ $totalDikirim }}</span>
                                    </div>
                                    <div class="w-px bg-zinc-700"></div>
                                    <div class="text-center">
                                        <span class="block text-[10px] font-bold text-amber-500 uppercase mb-1">Sisa</span>
                                        <span class="text-lg font-black text-amber-500">{{ $totalSisa }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- FEED TERMIN --}}
                        @if(!empty($pengajuanBarang->data_termin) && is_array($pengajuanBarang->data_termin) && count($pengajuanBarang->data_termin) > 0)
                            <div class="space-y-6">
                                @foreach($pengajuanBarang->data_termin as $index => $termin)
                                    <div class="bg-zinc-900/50 border border-zinc-700/50 rounded-xl overflow-hidden relative group">
                                        @php
                                            $terminStatus = strtolower($termin['status_monitoring'] ?? '');
                                            $accentColor = 'bg-sky-500';
                                            $iconStatus = 'fa-truck-loading';
                                            
                                            if (str_contains($terminStatus, 'selesai') || str_contains($terminStatus, 'diterima')) {
                                                $accentColor = 'bg-emerald-500';
                                                $iconStatus = 'fa-check-circle';
                                            } elseif (str_contains($terminStatus, 'kirim') || str_contains($terminStatus, 'perjalanan')) {
                                                $accentColor = 'bg-amber-500';
                                                $iconStatus = 'fa-truck-fast';
                                            }
                                        @endphp
                                        <div class="absolute top-0 left-0 w-1.5 h-full {{ $accentColor }}"></div>
                                        
                                        <div class="bg-zinc-900 border-b border-zinc-800 p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4 pl-6">
                                            <div class="flex items-center gap-4">
                                                <div class="w-10 h-10 rounded-full {{ str_replace('bg-', 'bg-', $accentColor) }}/10 text-{{ str_replace('bg-', '', $accentColor) }} flex items-center justify-center text-lg border border-{{ str_replace('bg-', '', $accentColor) }}/30">
                                                    <i class="fas {{ $iconStatus }}"></i>
                                                </div>
                                                <div>
                                                    <h3 class="text-sm font-bold text-zinc-200 flex items-center gap-2">
                                                        Termin Pengiriman #{{ $termin['id_termin'] ?? $loop->iteration }}
                                                        <span class="px-2 py-0.5 rounded text-[9px] font-bold uppercase tracking-wider {{ str_replace('bg-', 'bg-', $accentColor) }}/20 text-{{ str_replace('bg-', '', $accentColor) }} border border-{{ str_replace('bg-', '', $accentColor) }}/30">
                                                            {{ $termin['status_monitoring'] ?? 'Proses' }}
                                                        </span>
                                                    </h3>
                                                    <p class="text-[10px] text-zinc-400 mt-1"><i class="far fa-calendar-alt mr-1"></i>Dibuat: {{ $termin['tanggal_dibuat'] ?? '-' }}</p>
                                                </div>
                                            </div>
                                            <button type="button" onclick="document.getElementById('modal_termin_{{ $index }}').classList.remove('hidden')" class="px-4 py-2 bg-zinc-800 hover:bg-zinc-700 text-zinc-300 font-bold text-xs rounded-lg transition-colors border border-zinc-700">
                                                <i class="fas fa-edit mr-1"></i> Update Status
                                            </button>
                                        </div>

                                        <div class="grid grid-cols-1 md:grid-cols-5 divide-y md:divide-y-0 md:divide-x divide-zinc-800">
                                            <div class="md:col-span-2 p-4">
                                                <h6 class="text-[10px] font-bold text-zinc-500 uppercase mb-3"><i class="fas fa-box-open mr-1"></i> Isi Termin</h6>
                                                <div class="space-y-2 max-h-[200px] overflow-y-auto pr-2 custom-scrollbar">
                                                    @foreach($termin['rincian'] ?? [] as $idx => $rincian)
                                                        <div class="flex items-start gap-3 p-2 rounded bg-zinc-800/50 border border-zinc-700/50">
                                                            <div class="w-6 h-6 rounded bg-zinc-700 flex items-center justify-center text-[10px] font-bold text-zinc-400 shrink-0">{{ $idx + 1 }}</div>
                                                            <div class="flex-1 min-w-0">
                                                                <p class="font-bold text-zinc-300 text-xs truncate">{{ $rincian['nama_barang'] ?? 'Item' }}</p>
                                                                <p class="font-black text-sky-400 text-[11px] mt-0.5">{{ $rincian['jumlah'] ?? 0 }} <span class="text-zinc-500">{{ $rincian['satuan'] ?? '' }}</span></p>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                            
                                            <div class="md:col-span-3 p-4">
                                                <h6 class="text-[10px] font-bold text-zinc-500 uppercase mb-4"><i class="fas fa-map-marker-alt mr-1"></i> Riwayat Pelacakan</h6>
                                                <div class="relative pl-1 space-y-0">
                                                    @forelse($termin['riwayat'] ?? [] as $idx => $log)
                                                        <div class="relative z-10 flex gap-4 group/timeline pb-4">
                                                            <div class="flex flex-col items-center relative w-4 shrink-0">
                                                                <div class="z-10 w-2.5 h-2.5 rounded-full {{ $idx === 0 ? 'bg-sky-500 ring-[3px] ring-sky-900' : 'bg-zinc-600' }} mt-1.5"></div>
                                                                @if(!$loop->last)
                                                                    <div class="absolute top-4 bottom-[-16px] w-[2px] {{ $idx === 0 ? 'bg-sky-900/50' : 'bg-zinc-700' }} z-0"></div>
                                                                @endif
                                                            </div>
                                                            <div class="flex-1 pb-1">
                                                                <div class="flex justify-between items-start mb-1">
                                                                    <h5 class="text-xs font-bold {{ $idx === 0 ? 'text-sky-400' : 'text-zinc-300' }}">{{ $log['status'] ?? 'Status Update' }}</h5>
                                                                    <span class="text-[9px] font-semibold text-zinc-500 whitespace-nowrap ml-3">{{ $log['waktu'] ?? '' }}</span>
                                                                </div>
                                                                <p class="text-[10px] text-zinc-500 mb-1.5 font-medium flex items-center gap-1.5"><i class="fas fa-user text-zinc-600"></i> {{ $log['oleh'] ?? 'Sistem' }}</p>
                                                                @if(!empty($log['catatan']) && $log['catatan'] != '-')
                                                                    <div class="bg-zinc-800/80 p-2 rounded-md border border-zinc-700/50 text-[10px] text-zinc-400 italic">"{{ $log['catatan'] }}"</div>
                                                                @endif
                                                                @if(!empty($log['lampiran']))
                                                                    <a href="{{ Storage::url($log['lampiran']) }}" target="_blank" class="mt-2 inline-flex items-center gap-1.5 px-2.5 py-1.5 bg-zinc-800 border border-zinc-700 rounded-md text-[9px] font-bold text-zinc-300 hover:text-sky-400 hover:bg-zinc-700 hover:border-sky-500/30 transition-all">
                                                                        <i class="fas fa-paperclip"></i> Lihat Lampiran
                                                                    </a>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    @empty
                                                        <p class="text-xs text-zinc-500 italic">Belum ada riwayat update untuk termin ini.</p>
                                                    @endforelse
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="bg-zinc-900/50 py-12 rounded-2xl border border-zinc-700 border-dashed flex flex-col items-center justify-center gap-4 text-center">
                                <div class="w-16 h-16 bg-zinc-800 text-zinc-500 rounded-full flex items-center justify-center text-3xl shadow-inner"><i class="fas fa-box-open"></i></div>
                                <div>
                                    <h5 class="text-sm font-bold text-zinc-400 mb-1">Belum Ada Pengiriman</h5>
                                    <p class="text-[10px] text-zinc-500 max-w-sm mx-auto">Barang pada pengajuan ini belum mulai diproses.</p>
                                </div>
                            </div>
                        @endif
                    </div>
            
            <script>
                function confirmTandaiSelesaiAdmin(btn) {
                    Swal.fire({
                        position: 'center',
                        title: 'Konfirmasi',
                        text: 'Tandai pengajuan ini sebagai SELESAI?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, Tandai',
                        cancelButtonText: 'Batal',
                        customClass: {
                            popup: 'bg-zinc-900 border border-zinc-800 rounded-3xl p-6 text-center shadow-2xl',
                            title: 'text-lg font-black text-white tracking-tight mt-2 m-0',
                            htmlContainer: 'text-sm text-zinc-400 font-medium leading-relaxed m-0 mt-3 mb-6',
                            icon: 'scale-75 m-0 mx-auto border-0 text-amber-500 -mt-2',
                            actions: 'flex justify-center gap-3 w-full m-0',
                            confirmButton: 'bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold px-5 py-2.5 rounded-xl transition-all shadow-md m-0',
                            cancelButton: 'bg-zinc-700 hover:bg-zinc-600 text-white text-sm font-bold px-5 py-2.5 rounded-xl transition-all m-0'
                        },
                        width: '320px',
                        background: '#18181b',
                        backdrop: 'rgba(0,0,0,0.6)'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            const input = document.createElement('input');
                            input.type = 'hidden';
                            input.name = 'tandai_selesai';
                            input.value = '1';
                            btn.form.appendChild(input);
                            btn.form.submit();
                        }
                    });
                }
            </script>

        </div>
    </div>

    {{-- KUMPULAN MODAL UPDATE STATUS RENDERED DI LUAR CONTAINER --}}
    @if(!empty($pengajuanBarang->data_termin) && is_array($pengajuanBarang->data_termin) && count($pengajuanBarang->data_termin) > 0)
        @foreach($pengajuanBarang->data_termin as $index => $termin)
            @if(Auth::id() == $pengajuanBarang->approver_barang_4_id || (Auth::check() && Auth::user()->role === 'admin'))
                <div id="modal_termin_{{ $index }}" 
                     class="hidden fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm transition-opacity">
                    
                    <div class="absolute inset-0" onclick="document.getElementById('modal_termin_{{ $index }}').classList.add('hidden')"></div>

                    <div class="bg-zinc-900 rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden border border-zinc-700 relative z-10 animate-[zoomIn_0.2s_ease-out]" style="animation: zoomIn 0.2s ease-out;">
                         
                        <div class="bg-sky-600 p-4 md:p-5 flex justify-between items-center text-white">
                            <div>
                                <h3 class="font-black text-sm uppercase tracking-wider">Update Status & Resi</h3>
                                <p class="text-[10px] text-sky-200 font-semibold mt-0.5">Pengiriman #{{ $termin['id_termin'] ?? $loop->iteration }}</p>
                            </div>
                            <button type="button" onclick="document.getElementById('modal_termin_{{ $index }}').classList.add('hidden')" class="w-8 h-8 rounded-lg bg-sky-700/50 hover:bg-sky-700 flex items-center justify-center transition-colors">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        
                        <form action="{{ route('admin.pengajuan_barang.updateMonitoring', $pengajuanBarang->id) }}" method="POST" enctype="multipart/form-data" class="p-5 md:p-6">
                            @csrf
                            <input type="hidden" name="termin_id" value="{{ $termin['id_termin'] }}">
                            
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-[10px] font-bold text-zinc-400 uppercase tracking-wider mb-1.5">Status Baru</label>
                                    <input type="text" name="status_monitoring" list="statusOptions_{{ $index }}" class="w-full bg-zinc-800 border border-zinc-700 rounded-xl text-sm font-bold text-white p-2.5 focus:ring-2 focus:ring-sky-500/30 focus:border-sky-500 outline-none transition-all" placeholder="Pilih atau ketik status..." required autocomplete="off">
                                    <datalist id="statusOptions_{{ $index }}">
                                        <option value="Proses Purchasing">
                                        <option value="PO Diterbitkan">
                                        <option value="Barang Dikirim/Ekspedisi">
                                        <option value="Barang Diterima & Selesai">
                                    </datalist>
                                </div>

                                <div>
                                    <label class="block text-[10px] font-bold text-zinc-400 uppercase tracking-wider mb-1.5">Keterangan / Nomor Resi <span class="text-zinc-600 font-normal">(Opsional)</span></label>
                                    <textarea name="catatan_monitoring" rows="3" class="w-full bg-zinc-800 border border-zinc-700 rounded-xl text-xs text-white p-3 focus:ring-2 focus:ring-sky-500/30 focus:border-sky-500 outline-none transition-all placeholder-zinc-500" placeholder="Tuliskan keterangan detail, posisi terkini, atau nomor resi pengiriman..."></textarea>
                                </div>
                                
                                <div>
                                    <label class="block text-[10px] font-bold text-zinc-400 uppercase tracking-wider mb-1.5">Lampiran <span class="text-zinc-600 font-normal">(Opsional)</span></label>
                                    <div class="relative border-2 border-dashed border-zinc-700 rounded-xl bg-zinc-800/50 p-4 text-center hover:border-sky-500 hover:bg-sky-500/5 transition-colors group">
                                        <i class="fas fa-cloud-upload-alt text-2xl text-zinc-600 group-hover:text-sky-500 mb-2"></i>
                                        <p class="text-[10px] font-semibold text-zinc-500 mb-1">Klik untuk memilih file lampiran (PDF/JPG/PNG)</p>
                                        <input type="file" name="lampiran_monitoring" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                                    </div>
                                </div>
                            </div>

                            <div class="mt-6 flex gap-3 pt-5 border-t border-zinc-800">
                                <button type="button" onclick="document.getElementById('modal_termin_{{ $index }}').classList.add('hidden')" class="px-5 py-2.5 bg-zinc-800 hover:bg-zinc-700 text-zinc-300 font-bold rounded-xl text-xs transition-colors flex-1 text-center border border-zinc-700">Batal</button>
                                <button type="submit" class="px-5 py-2.5 bg-sky-600 hover:bg-sky-700 text-white font-black rounded-xl text-xs transition-colors flex-[2] flex items-center justify-center gap-2 shadow-md">
                                    <i class="fas fa-save"></i> Simpan Pembaruan
                                </button>
                            </div>
                            
                            @if($pengajuanBarang->status != 'selesai')
                            <div class="mt-3">
                                <button type="button" onclick="confirmTandaiSelesaiAdmin(this)" class="w-full px-5 py-2.5 bg-zinc-900 hover:bg-emerald-900/30 text-emerald-500 border border-emerald-500/30 font-black rounded-xl text-xs transition-colors flex items-center justify-center gap-2 shadow-sm">
                                    <i class="fas fa-check-double"></i> Selesaikan Seluruh Pengajuan
                                </button>
                            </div>
                            @endif
                        </form>
                    </div>
                </div>
            @endif
        @endforeach
    @endif
</x-layout-admin>
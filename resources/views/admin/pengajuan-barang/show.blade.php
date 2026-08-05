<x-layout-admin>
    <x-slot:title>Detail Pengajuan Barang</x-slot:title>

    <div class="p-6">
        {{-- TOMBOL KEMBALI --}}
        <x-back-button href="{{ route('admin.pengajuan_barang.index') }}">Kembali ke Rekap Barang</x-back-button>

        
        

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
                                        <th class="px-4 py-3 text-center font-semibold text-zinc-400 w-28 text-xs uppercase">Jumlah</th>
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
                                                <td class="px-4 py-3 text-zinc-300 font-medium text-xs">{{ $item['keterangan'] ?? '-' }}</td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr><td colspan="4" class="px-4 py-6 text-center text-zinc-500">Tidak ada rincian barang.</td></tr>
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

                    {{-- UPDATE MONITORING --}}
                    <div class="bg-zinc-900 rounded-xl border border-zinc-700 p-5">
                        <div class="flex justify-between items-center mb-4 pb-3 border-b border-zinc-800">
                            <h3 class="text-sm font-bold text-sky-400 flex items-center gap-2">
                                <i class="fas fa-truck-loading"></i> Update Tracking
                            </h3>
                            <span class="px-2 py-1 rounded text-[9px] font-bold bg-sky-500/20 text-sky-300 border border-sky-500/40">
                                {{ $pengajuanBarang->status_monitoring ?? 'Belum Diproses' }}
                            </span>
                        </div>

                        <form action="{{ route('admin.pengajuan_barang.updateMonitoring', $pengajuanBarang->id) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                            @csrf
                            <div>
                                <label class="block text-[10px] font-bold text-zinc-400 mb-1.5 uppercase">Status</label>
                                <select name="status_monitoring" class="w-full bg-zinc-800 border border-zinc-700 rounded-lg text-xs text-white p-2.5 focus:ring-sky-500">
                                    <option value="Proses Purchasing" {{ ($pengajuanBarang->status_monitoring == 'Proses Purchasing') ? 'selected' : '' }}>Proses Purchasing</option>
                                    <option value="PO Diterbitkan" {{ ($pengajuanBarang->status_monitoring == 'PO Diterbitkan') ? 'selected' : '' }}>PO Diterbitkan</option>
                                    <option value="Sedang Diproses Vendor" {{ ($pengajuanBarang->status_monitoring == 'Sedang Diproses Vendor') ? 'selected' : '' }}>Sedang Diproses Vendor</option>
                                    <option value="Dalam Pengiriman (ekspedisi)" {{ ($pengajuanBarang->status_monitoring == 'Dalam Pengiriman (ekspedisi)') ? 'selected' : '' }}>Dalam Pengiriman (ekspedisi)</option>
                                    <option value="Barang Tiba di Gudang Rakha" {{ ($pengajuanBarang->status_monitoring == 'Barang Tiba di Gudang Rakha') ? 'selected' : '' }}>Barang Tiba di Gudang Rakha</option>
                                    <option value="Selesai / Barang Diterima" {{ ($pengajuanBarang->status_monitoring == 'Selesai / Barang Diterima') ? 'selected' : '' }}>Selesai / Barang Diterima</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-[10px] font-bold text-zinc-400 mb-1.5 uppercase">Catatan</label>
                                <input type="text" name="catatan_monitoring" class="w-full bg-zinc-800 border border-zinc-700 rounded-lg text-xs text-white p-2.5 focus:ring-sky-500" placeholder="Resi / Note...">
                            </div>

                            <div>
                                <label class="block text-[10px] font-bold text-zinc-400 mb-1.5 uppercase">Upload Lampiran (Opsional)</label>
                                <input type="file" name="lampiran_monitoring" 
                                    class="w-full bg-zinc-800 border border-zinc-700 rounded-lg text-xs text-zinc-400 p-1.5 file:cursor-pointer file:mr-3 file:py-1 file:px-3 file:rounded file:border-0 file:text-[10px] file:font-bold file:bg-zinc-700 file:text-sky-400 hover:file:bg-zinc-600 transition-all cursor-pointer" 
                                    accept=".jpg,.jpeg,.png,.pdf,.doc,.docx">
                            </div>

                            <button type="submit" class="w-full bg-sky-600 hover:bg-sky-700 text-white font-bold text-xs py-2.5 rounded-lg transition flex items-center justify-center gap-2">
                                <i class="fas fa-sync-alt"></i> Update
                            </button>

                            @if($pengajuanBarang->status != 'selesai')
                            <button type="button" onclick="confirmTandaiSelesaiAdmin(this)" class="w-full mt-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs py-2.5 rounded-lg transition flex items-center justify-center gap-2">
                                <i class="fas fa-check-double"></i> Tandai Selesai
                            </button>
                            @endif
                        </form>
                    </div>

                    {{-- RIWAYAT LOG --}}
                    @if(!empty($pengajuanBarang->riwayat_monitoring))
                    <div class="bg-zinc-900 rounded-xl border border-zinc-700 p-5">
                        <h4 class="text-xs font-bold text-zinc-400 uppercase mb-4"><i class="fas fa-history mr-2"></i>Log Riwayat</h4>
                        <div class="relative border-l-2 border-sky-500/30 ml-2 space-y-4 pl-4">
                            @foreach(array_reverse($pengajuanBarang->riwayat_monitoring) as $log)
                            <div class="relative">
                                <div class="absolute w-2.5 h-2.5 rounded-full bg-sky-500 border-2 border-zinc-900" style="left: -21px; top: 3px;"></div>
                                <span class="font-bold text-sky-400 text-xs block mb-0.5">{{ $log['status'] }}</span>
                                <span class="text-[9px] text-zinc-500 block mb-1"><i class="fas fa-clock mr-1"></i>{{ $log['waktu'] }} • {{ $log['oleh'] }}</span>
                                @if(!empty($log['catatan']) && $log['catatan'] != '-')
                                    <p class="text-[10px] text-zinc-300 bg-zinc-800/80 p-2 rounded-md border border-zinc-700/50">{{ $log['catatan'] }}</p>
                                @endif
                                @if(!empty($log['lampiran']))
                                <a href="{{ Storage::url($log['lampiran']) }}" target="_blank" class="mt-1.5 inline-flex items-center gap-1 px-2.5 py-1 bg-zinc-800 border border-zinc-700 rounded text-[9px] font-bold text-zinc-300 hover:text-sky-400 hover:bg-zinc-700 transition">
                                    <i class="fas fa-paperclip"></i> Buka Lampiran
                                </a>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                </div>
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
</x-layout-admin>
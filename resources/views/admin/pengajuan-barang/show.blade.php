<x-layout-admin>
    <x-slot:title>Detail Pengajuan Barang</x-slot:title>

    <div class="p-6">
        {{-- TOMBOL KEMBALI --}}
        <x-back-button href="{{ route('admin.pengajuan_barang.index') }}">Kembali ke Rekap Barang</x-back-button>

        
        

        <div class="bg-zinc-800 rounded-lg shadow-lg p-6 md:p-8 text-zinc-300 border border-zinc-700">
            
            {{-- 1. HEADER DETAIL --}}
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

                {{-- Status Akhir yang Lebih Menonjol --}}
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

            {{-- 2. INFORMASI UTAMA PEMOHON --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                <div>
                    <label class="block text-sm font-medium text-zinc-400 mb-1">Nama Pemohon</label>
                    <p class="font-semibold text-white text-lg">{{ $pengajuanBarang->user->name ?? 'N/A' }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-zinc-400 mb-1">Divisi / Bagian</label>
                    <p class="font-semibold text-white text-lg">{{ $pengajuanBarang->divisi ?? '-' }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-zinc-400 mb-1">ID Pengajuan</label>
                    <p class="font-semibold text-amber-400 text-lg">#REQ-{{ str_pad($pengajuanBarang->id, 4, '0', STR_PAD_LEFT) }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-zinc-400 mb-1">Tanggal Pengajuan</label>
                    <p class="font-semibold text-white">{{ $pengajuanBarang->created_at->translatedFormat('d F Y') }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-zinc-400 mb-1">Waktu</label>
                    <p class="font-semibold text-white">{{ $pengajuanBarang->created_at->format('H:i') }} WIB</p>
                </div>
            </div>

            {{-- 3. RINCIAN BARANG YANG DIAJUKAN --}}
            <div class="mb-8">
                <label class="block text-sm font-medium text-zinc-400 mb-2">Daftar Rincian Barang</label>
                <div class="overflow-x-auto rounded-lg border border-zinc-700 bg-zinc-900/40">
                    <table class="min-w-full divide-y divide-zinc-700 text-sm">
                        <thead class="bg-zinc-900/80">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-zinc-400 w-12 text-xs uppercase tracking-wider">No</th>
                                <th class="px-4 py-3 text-left font-semibold text-zinc-400 text-xs uppercase tracking-wider">Nama Barang</th>
                                <th class="px-4 py-3 text-left font-semibold text-zinc-400 text-xs uppercase tracking-wider">Supplier</th>
                                <th class="px-4 py-3 text-center font-semibold text-zinc-400 w-32 text-xs uppercase tracking-wider">Jumlah & Satuan</th>
                                <th class="px-4 py-3 text-left font-semibold text-zinc-400 text-xs uppercase tracking-wider">Keterangan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-700/60">
                            @if(is_array($pengajuanBarang->rincian_barang))
                                @foreach($pengajuanBarang->rincian_barang as $index => $item)
                                    <tr class="hover:bg-zinc-700/30 transition-colors">
                                        <td class="px-4 py-3 text-zinc-400 text-center">{{ $loop->iteration }}</td>
                                        <td class="px-4 py-3 text-white font-medium">{{ $item['nama_barang'] ?? $item['deskripsi'] ?? '-' }}</td>
                                        <td class="px-4 py-3 text-zinc-300 font-medium">{{ $item['supplier'] ?? '-' }}</td>
                                        <td class="px-4 py-3 text-center font-bold text-amber-400 bg-amber-500/5">{{ $item['jumlah'] ?? 0 }} {{ $item['satuan'] ?? '' }}</td>
                                        <td class="px-4 py-3 text-zinc-300 font-medium">{{ $item['keterangan'] ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            @else
                                <tr><td colspan="5" class="px-4 py-6 text-center text-zinc-500">Tidak ada rincian barang.</td></tr>
                            @endif
                        </tbody>
                    </table>
                </div>

                @if(!empty($pengajuanBarang->catatan_pemohon))
                <div class="mt-4 p-4 bg-zinc-800/80 border border-zinc-700/80 rounded-xl">
                    <span class="text-xs font-bold text-amber-400 block mb-1"><i class="fas fa-sticky-note mr-1"></i> Catatan Pemohon:</span>
                    <p class="text-xs text-zinc-300 leading-relaxed">{{ $pengajuanBarang->catatan_pemohon }}</p>
                </div>
                @endif
            </div>

            {{-- 4. LAMPIRAN DOKUMEN --}}
            @if ($pengajuanBarang->lampiran && is_array($pengajuanBarang->lampiran) && count($pengajuanBarang->lampiran) > 0)
                <div class="mb-8">
                    <label class="block text-sm font-medium text-zinc-400 mb-2">Lampiran Dokumen</label>
                    <div class="flex flex-wrap gap-3">
                        @foreach ($pengajuanBarang->lampiran as $lampiran)
                            <div class="p-4 bg-zinc-700/60 rounded-lg border border-zinc-600/60 inline-block">
                                <a href="{{ asset('storage/' . $lampiran) }}" target="_blank" class="font-semibold text-amber-400 hover:text-amber-300 transition-colors flex items-center gap-2 text-sm">
                                    <i class="fas fa-paperclip text-base"></i> Lihat Berkas Lampiran {{ $loop->iteration }}
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
            
            <hr class="my-8 border-zinc-700">

            {{-- 5. TIMELINE 4 TAHAP APPROVAL --}}
            <div>
                <div class="flex items-center gap-2 mb-4">
                    <i class="fas fa-tasks text-amber-400"></i>
                    <h3 class="text-lg font-bold text-white tracking-wide">Timeline & Status Persetujuan (4 Tahap)</h3>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                    @foreach([
                        1 => ['nama' => 'Tahap 1 (Atasan)', 'user' => $pengajuanBarang->approver1, 'status' => $pengajuanBarang->status_appr_1, 'catatan' => $pengajuanBarang->catatan_approver_1, 'tgl' => $pengajuanBarang->tanggal_approved_1],
                        2 => ['nama' => 'Tahap 2 (Manager)', 'user' => $pengajuanBarang->approver2, 'status' => $pengajuanBarang->status_appr_2, 'catatan' => $pengajuanBarang->catatan_approver_2, 'tgl' => $pengajuanBarang->tanggal_approved_2],
                        3 => ['nama' => 'Tahap 3 (Keuangan)', 'user' => $pengajuanBarang->approver3, 'status' => $pengajuanBarang->status_appr_3, 'catatan' => $pengajuanBarang->catatan_approver_3, 'tgl' => $pengajuanBarang->tanggal_approved_3],
                        4 => ['nama' => 'Tahap 4 (Direktur)', 'user' => $pengajuanBarang->approver4, 'status' => $pengajuanBarang->status_appr_4, 'catatan' => $pengajuanBarang->catatan_approver_4, 'tgl' => $pengajuanBarang->tanggal_approved_4],
                    ] as $stage => $data)
                        @php
                            $borderAppr = match($data['status']) {
                                'disetujui' => 'border-emerald-500 bg-emerald-500/5',
                                'ditolak' => 'border-red-500 bg-red-500/5',
                                'skipped' => 'border-zinc-600 bg-zinc-900/40',
                                default => 'border-amber-500 bg-amber-500/5',
                            };
                            $badgeAppr = match($data['status']) {
                                'disetujui' => 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30',
                                'ditolak' => 'bg-red-500/20 text-red-400 border-red-500/30',
                                'skipped' => 'bg-zinc-600/20 text-zinc-400 border-zinc-600/30',
                                default => 'bg-amber-500/20 text-amber-400 border-amber-500/30',
                            };
                        @endphp
                        <div class="rounded-lg border border-zinc-700 p-4 border-l-[4px] {{ $borderAppr }}">
                            <div class="flex justify-between items-start mb-2 gap-2">
                                <span class="text-[10px] font-bold text-zinc-400 uppercase tracking-wider">{{ $data['nama'] }}</span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase border {{ $badgeAppr }}">
                                    {{ $data['status'] ?? 'menunggu' }}
                                </span>
                            </div>
                            <h4 class="text-sm font-bold text-white truncate">{{ $data['user']->name ?? 'Belum Ditentukan' }}</h4>
                            
                            @if($data['tgl'])
                                <p class="text-[10px] text-zinc-500 mt-1 flex items-center gap-1">
                                    <i class="fas fa-clock text-[9px]"></i> {{ \Carbon\Carbon::parse($data['tgl'])->translatedFormat('d/m/Y H:i') }}
                                </p>
                            @endif

                            {{-- Notes/Catatan --}}
                            <div class="mt-3 p-2 bg-zinc-900/60 rounded border border-zinc-700/50 text-xs">
                                <span class="text-zinc-500 block text-[9px] uppercase font-bold">Catatan:</span>
                                <p class="text-zinc-300 italic mt-0.5">
                                    {{ $data['catatan'] ? '"' . $data['catatan'] . '"' : 'Tidak ada catatan.' }}
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- MONITORING & TRACKING STATUS BARANG (KONTROL ADMIN) --}}
            <div class="mt-8 pt-6 border-t border-zinc-700">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                    <div>
                        <h3 class="text-lg font-bold text-white flex items-center gap-2">
                            <i class="fas fa-truck-loading text-sky-400"></i> Monitoring & Tracking Proses Barang
                        </h3>
                        <p class="text-xs text-zinc-400 mt-1">Pantau & perbarui status pengiriman/pengadaan barang secara real-time untuk pemohon.</p>
                    </div>
                    
                    {{-- Status Monitoring Badge Saat Ini --}}
                    <div>
                        <span class="text-xs text-zinc-400 mr-2 font-medium">Status Monitoring:</span>
                        <span class="px-3 py-1.5 rounded-full text-xs font-bold bg-sky-500/20 text-sky-300 border border-sky-500/40">
                            <i class="fas fa-shipping-fast mr-1"></i> {{ $pengajuanBarang->status_monitoring ?? 'Belum Diproses' }}
                        </span>
                    </div>
                </div>

                {{-- Form Update Monitoring oleh Admin --}}
                <div class="bg-zinc-900 rounded-xl border border-zinc-700 p-6 mb-6">
                    <h4 class="text-sm font-bold text-white mb-3 flex items-center gap-2">
                        <i class="fas fa-edit text-sky-400"></i> Perbarui Status Monitoring Barang
                    </h4>
                    <form action="{{ route('admin.pengajuan_barang.updateMonitoring', $pengajuanBarang->id) }}" method="POST" class="space-y-4">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="md:col-span-1">
                                <label class="block text-xs font-bold text-zinc-400 mb-1.5 uppercase">Status Monitoring</label>
                                <select name="status_monitoring" id="status_monitoring_select" class="w-full bg-zinc-800 border-zinc-700 rounded-lg text-xs text-white p-2.5 focus:ring-sky-500 focus:border-sky-500">
                                    <option value="Proses Pembelian" {{ ($pengajuanBarang->status_monitoring == 'Proses Pembelian') ? 'selected' : '' }}>Proses Pembelian</option>
                                    <option value="Sedang Dipesan" {{ ($pengajuanBarang->status_monitoring == 'Sedang Dipesan') ? 'selected' : '' }}>Sedang Dipesan</option>
                                    <option value="Dikirim Kurir (Dalam Pengiriman)" {{ ($pengajuanBarang->status_monitoring == 'Dikirim Kurir (Dalam Pengiriman)') ? 'selected' : '' }}>Dikirim Kurir (Dalam Pengiriman)</option>
                                    <option value="Barang Tiba di Kantor" {{ ($pengajuanBarang->status_monitoring == 'Barang Tiba di Kantor') ? 'selected' : '' }}>Barang Tiba di Kantor</option>
                                    <option value="Siap Diambil Pemohon" {{ ($pengajuanBarang->status_monitoring == 'Siap Diambil Pemohon') ? 'selected' : '' }}>Siap Diambil Pemohon</option>
                                    <option value="Selesai / Barang Diterima" {{ ($pengajuanBarang->status_monitoring == 'Selesai / Barang Diterima') ? 'selected' : '' }}>Selesai / Barang Diterima</option>
                                </select>
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-xs font-bold text-zinc-400 mb-1.5 uppercase">Catatan / Resi / Note Update</label>
                                <input type="text" name="catatan_monitoring" class="w-full bg-zinc-800 border-zinc-700 rounded-lg text-xs text-white p-2.5 focus:ring-sky-500 focus:border-sky-500" placeholder="Contoh: Resi JNE: 12345678, barang diperkirakan sampai sore ini...">
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center justify-between gap-3 pt-2">
                            <button type="submit" class="bg-sky-600 hover:bg-sky-700 text-white font-bold text-xs py-2.5 px-5 rounded-lg transition flex items-center gap-2">
                                <i class="fas fa-sync-alt"></i> Update Status Monitoring
                            </button>

                            @if($pengajuanBarang->status != 'selesai')
                            <button type="submit" name="tandai_selesai" value="1" onclick="return confirm('Apakah Anda yakin ingin menandai pengajuan barang ini sebagai SELESAI?');" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs py-2.5 px-5 rounded-lg transition flex items-center gap-2">
                                <i class="fas fa-check-double"></i> Tandai Selesai & Diterima
                            </button>
                            @endif
                        </div>
                    </form>
                </div>

                {{-- Riwayat / Timeline Monitoring --}}
                @if(!empty($pengajuanBarang->riwayat_monitoring))
                <div class="bg-zinc-900/70 rounded-xl border border-zinc-700/60 p-5">
                    <h4 class="text-xs font-bold text-zinc-400 uppercase mb-4 tracking-wider">Riwayat Log Monitoring & Tracking</h4>
                    <div class="relative border-l-2 border-sky-500/40 ml-3 space-y-4 pl-4">
                        @foreach(array_reverse($pengajuanBarang->riwayat_monitoring) as $log)
                        <div class="relative">
                            <div class="absolute -left-[23px] top-0.5 w-3 h-3 rounded-full bg-sky-500 border-2 border-zinc-900"></div>
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-bold text-sky-400">{{ $log['status'] }}</span>
                                <span class="text-[10px] text-zinc-500"><i class="fas fa-clock mr-1"></i> {{ $log['waktu'] }} oleh {{ $log['oleh'] }}</span>
                            </div>
                            <p class="text-xs text-zinc-300 mt-1 font-medium bg-zinc-800/80 p-2.5 rounded-lg border border-zinc-700/50">{{ $log['catatan'] }}</p>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            {{-- 6. FORM TINDAKAN ADMIN (BERURUTAN KETAT / STRICTLY SEQUENTIAL) --}}
            @php
                $admin = Auth::user();
                
                // Cek apakah tahap sebelumnya sudah selesai dilakukan (bukan 'menunggu')
                $app1Done = ($pengajuanBarang->status_appr_1 != 'menunggu');
                $app2Done = ($pengajuanBarang->status_appr_2 != 'menunggu');
                $app3Done = ($pengajuanBarang->status_appr_3 != 'menunggu');

                // Form hanya muncul jika Admin adalah approver di tahap tersebut DAN tahap sebelumnya sudah tuntas!
                $isAppr1 = ($admin->id == $pengajuanBarang->approver_barang_1_id && $pengajuanBarang->status_appr_1 == 'menunggu');
                $isAppr2 = ($admin->id == $pengajuanBarang->approver_barang_2_id && $pengajuanBarang->status_appr_2 == 'menunggu' && $app1Done);
                $isAppr3 = ($admin->id == $pengajuanBarang->approver_barang_3_id && $pengajuanBarang->status_appr_3 == 'menunggu' && $app1Done && $app2Done);
                $isAppr4 = ($admin->id == $pengajuanBarang->approver_barang_4_id && $pengajuanBarang->status_appr_4 == 'menunggu' && $app1Done && $app2Done && $app3Done);
                
                $showApprovalForm = $isAppr1 || $isAppr2 || $isAppr3 || $isAppr4;
            @endphp

            @if($showApprovalForm)
                <div class="bg-zinc-900 rounded-xl border border-amber-500/40 p-6 mb-8 shadow-xl mt-8 animate-pulse-subtle">
                    <h3 class="text-lg font-bold text-white mb-2 flex items-center gap-2">
                        <i class="fas fa-gavel text-amber-400"></i> Form Persetujuan Approver (Giliran Anda)
                    </h3>
                    <p class="text-xs text-zinc-400 mb-6">Antrean persetujuan saat ini telah sampai di akun Admin Anda. Silakan berikan keputusan dan catatan untuk tahap ini.</p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- FORM SETUJU --}}
                        <form action="{{ route('admin.pengajuan_barang.updateStatus', $pengajuanBarang->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="bg-emerald-500/10 p-5 rounded-xl border border-emerald-500/20 h-full flex flex-col hover:shadow-md transition-all">
                                <label class="block text-sm font-bold text-emerald-400 mb-2">Setujui Pengajuan Barang</label>
                                <textarea name="alasan" rows="3" class="w-full p-3 bg-zinc-800 border border-zinc-700 rounded-lg text-sm text-white focus:ring-emerald-500 focus:border-emerald-500 mb-4" placeholder="Berikan catatan persetujuan (opsional)..."></textarea>
                                <input type="hidden" name="status" value="disetujui">
                                <button type="submit" class="mt-auto w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3 rounded-lg transition shadow-md flex items-center justify-center gap-2 active:scale-95">
                                    <i class="fas fa-check-circle"></i> Setujui Pengajuan Ini
                                </button>
                            </div>
                        </form>

                        {{-- FORM TOLAK --}}
                        <form action="{{ route('admin.pengajuan_barang.updateStatus', $pengajuanBarang->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="bg-red-500/10 p-5 rounded-xl border border-red-500/20 h-full flex flex-col hover:shadow-md transition-all">
                                <label class="block text-sm font-bold text-red-400 mb-2">Tolak Pengajuan Barang <span class="text-red-500">*</span></label>
                                <textarea name="alasan" rows="3" class="w-full p-3 bg-zinc-800 border border-zinc-700 rounded-lg text-sm text-white focus:ring-red-500 focus:border-red-500 mb-4" placeholder="Tuliskan alasan penolakan wajib diisi..." required></textarea>
                                <input type="hidden" name="status" value="ditolak">
                                <button type="submit" class="mt-auto w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3 rounded-lg transition shadow-md flex items-center justify-center gap-2 active:scale-95" onclick="return confirm('Yakin ingin menolak pengajuan ini?');">
                                    <i class="fas fa-times-circle"></i> Tolak Pengajuan Ini
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif

            {{-- 7. FOOTER ACTION --}}
            <div class="mt-8 pt-6 border-t border-zinc-700 flex justify-end gap-3">
                <a href="{{ route('admin.pengajuan_barang.downloadPdf', $pengajuanBarang->id) }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg shadow-lg transition text-sm">
                    <i class="fas fa-file-pdf"></i>
                    Cetak PDF
                </a>
            </div>

        </div>
    </div>
</x-layout-admin>
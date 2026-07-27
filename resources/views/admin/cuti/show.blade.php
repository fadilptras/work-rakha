<x-layout-admin>
    <x-slot:title>Detail Pengajuan Cuti</x-slot:title>

    <div class="p-6">
        <x-back-button href="{{ route('admin.cuti.index') }}">Kembali ke Rekap Cuti</x-back-button>

        
        

        <div class="bg-zinc-800 rounded-lg shadow-lg p-6 md:p-8 text-zinc-300 border border-zinc-700">
            
            {{-- 1. HEADER --}}
            <div class="flex flex-col md:flex-row justify-between md:items-center mb-6 gap-4">
                <div class="flex items-center gap-4">
                    <div class="h-12 w-12 rounded-full bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-400 text-xl font-bold">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-white">Pengajuan Cuti: {{ $cuti->user->name ?? 'N/A' }}</h2>
                        <p class="text-xs text-zinc-400 mt-0.5">Diajukan pada: {{ $cuti->created_at->translatedFormat('d F Y, H:i') }} WIB</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-sm font-medium text-zinc-400">Status Akhir:</span>
                    @php
                        $statusClass = match($cuti->status) {
                            'selesai', 'disetujui' => 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30',
                            'ditolak' => 'bg-red-500/20 text-red-400 border-red-500/30',
                            'proses_finalisasi' => 'bg-blue-500/20 text-blue-400 border-blue-500/30',
                            'dibatalkan' => 'bg-zinc-500/20 text-zinc-400 border-zinc-500/30',
                            default => 'bg-amber-500/20 text-amber-400 border-amber-500/30',
                        };
                    @endphp
                    <span class="px-4 py-1.5 font-semibold rounded-full text-sm uppercase tracking-wider border {{ $statusClass }}">
                        {{ str_replace('_', ' ', $cuti->status) }}
                    </span>
                </div>
            </div>
            
            <hr class="my-6 border-zinc-700">

            {{-- 2. INFORMASI CUTI --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                <div>
                    <label class="block text-sm font-medium text-zinc-400 mb-1">Nama Pemohon</label>
                    <p class="font-semibold text-white text-lg">{{ $cuti->user->name ?? 'N/A' }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-zinc-400 mb-1">Divisi / Jabatan</label>
                    <p class="font-semibold text-white text-lg">{{ $cuti->user->divisi ?? '-' }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-zinc-400 mb-1">Jenis Cuti</label>
                    <p class="font-semibold text-amber-400 text-lg uppercase">{{ $cuti->jenis_cuti }}</p>
                </div>
                
                <div class="lg:col-span-3 grid grid-cols-1 md:grid-cols-3 gap-6 bg-zinc-900/50 p-4 rounded-lg border border-zinc-700 mt-2">
                    <div>
                        <label class="block text-sm font-medium text-zinc-400 mb-1">Tanggal Mulai</label>
                        <p class="font-semibold text-white">{{ \Carbon\Carbon::parse($cuti->tanggal_mulai)->translatedFormat('d F Y') }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-zinc-400 mb-1">Tanggal Selesai</label>
                        <p class="font-semibold text-white">{{ \Carbon\Carbon::parse($cuti->tanggal_selesai)->translatedFormat('d F Y') }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-zinc-400 mb-1">Total Hari Diambil</label>
                        <p class="font-semibold text-emerald-400 text-lg">{{ $cuti->total_hari }} Hari</p>
                    </div>
                </div>

                <div class="lg:col-span-3">
                    <label class="block text-sm font-medium text-zinc-400 mb-1">Alasan Cuti</label>
                    <p class="text-white bg-zinc-900 p-4 rounded-lg border border-zinc-700 leading-relaxed">{{ $cuti->alasan }}</p>
                </div>
            </div>

            {{-- 3. LAMPIRAN DOKUMEN --}}
            @if ($cuti->lampiran)
                <div class="mb-8">
                    <label class="block text-sm font-medium text-zinc-400 mb-2">Dokumen Pendukung</label>
                    <a href="{{ asset('storage/' . $cuti->lampiran) }}" target="_blank" class="p-3 bg-zinc-700/60 rounded-lg border border-zinc-600 hover:border-amber-500 font-semibold text-amber-400 text-sm flex items-center gap-2 transition w-max">
                        <i class="fas fa-paperclip text-base"></i> Lihat Berkas Lampiran Cuti
                    </a>
                </div>
            @endif
            
            <hr class="my-8 border-zinc-700">

            {{-- 4. TIMELINE 4 TAHAP APPROVAL --}}
            <div>
                <div class="flex items-center gap-2 mb-4">
                    <i class="fas fa-tasks text-amber-400"></i>
                    <h3 class="text-lg font-bold text-white tracking-wide">Timeline & Status Persetujuan (4 Tahap)</h3>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                    @foreach([
                        1 => ['nama' => 'Tahap 1', 'user' => $cuti->approver1, 'status' => $cuti->status_approver_1, 'catatan' => $cuti->catatan_approver_1, 'tgl' => $cuti->tanggal_approve_1 ?? null],
                        2 => ['nama' => 'Tahap 2', 'user' => $cuti->approver2, 'status' => $cuti->status_approver_2, 'catatan' => $cuti->catatan_approver_2, 'tgl' => $cuti->tanggal_approve_2 ?? null],
                        3 => ['nama' => 'Tahap 3', 'user' => $cuti->approver3, 'status' => $cuti->status_approver_3, 'catatan' => $cuti->catatan_approver_3, 'tgl' => $cuti->tanggal_approve_3 ?? null],
                        4 => ['nama' => 'Tahap 4 (Admin)', 'user' => $cuti->approver4, 'status' => $cuti->status_approver_4, 'catatan' => $cuti->catatan_approver_4, 'tgl' => $cuti->tanggal_approve_4 ?? null],
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
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase border {{ $badgeAppr }}">{{ $data['status'] ?? 'menunggu' }}</span>
                            </div>
                            <h4 class="text-sm font-bold text-white truncate">{{ $data['user']->name ?? 'Belum Ditentukan' }}</h4>
                            
                            @if($data['tgl'])
                                <p class="text-[10px] text-zinc-500 mt-1 flex items-center gap-1"><i class="fas fa-clock text-[9px]"></i> {{ \Carbon\Carbon::parse($data['tgl'])->translatedFormat('d/m/Y H:i') }}</p>
                            @endif

                            <div class="mt-3 p-2 bg-zinc-900/60 rounded border border-zinc-700/50 text-xs">
                                <span class="text-zinc-500 block text-[9px] uppercase font-bold">Catatan:</span>
                                <p class="text-zinc-300 italic mt-0.5">{{ $data['catatan'] ? '"' . $data['catatan'] . '"' : 'Tidak ada catatan.' }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- 5. FORM TINDAKAN ADMIN (BERURUTAN KETAT) --}}
            @php
                $admin = Auth::user();
                $app1Done = ($cuti->status_approver_1 != 'menunggu');
                $app2Done = ($cuti->status_approver_2 != 'menunggu');
                $app3Done = ($cuti->status_approver_3 != 'menunggu');

                $isAppr1 = ($admin->id == $cuti->approver_cuti_1_id && $cuti->status_approver_1 == 'menunggu');
                $isAppr2 = ($admin->id == $cuti->approver_cuti_2_id && $cuti->status_approver_2 == 'menunggu' && $app1Done);
                $isAppr3 = ($admin->id == $cuti->approver_cuti_3_id && $cuti->status_approver_3 == 'menunggu' && $app1Done && $app2Done);
                $isAppr4 = ($admin->id == $cuti->approver_cuti_4_id && $cuti->status_approver_4 == 'menunggu' && $app1Done && $app2Done && $app3Done);
                
                $showApprovalForm = $isAppr1 || $isAppr2 || $isAppr3 || $isAppr4;
            @endphp

            @if($showApprovalForm)
                <div class="bg-zinc-900 rounded-xl border border-amber-500/40 p-6 mb-8 shadow-xl mt-8 animate-pulse-subtle">
                    <h3 class="text-lg font-bold text-white mb-2 flex items-center gap-2">
                        <i class="fas fa-gavel text-amber-400"></i> Form Persetujuan Approver (Giliran Anda)
                    </h3>
                    <p class="text-xs text-zinc-400 mb-6">Antrean persetujuan saat ini telah sampai di akun Admin Anda. Silakan berikan keputusan.</p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- FORM SETUJU --}}
                        <form action="{{ route('admin.cuti.updateStatus', $cuti->id) }}" method="POST">
                            @csrf @method('PUT')
                            <div class="bg-emerald-500/10 p-5 rounded-xl border border-emerald-500/20 h-full flex flex-col hover:shadow-md transition-all">
                                <label class="block text-sm font-bold text-emerald-400 mb-2">Setujui Pengajuan Cuti</label>
                                <textarea name="alasan" rows="3" class="w-full p-3 bg-zinc-800 border border-zinc-700 rounded-lg text-sm text-white focus:ring-emerald-500 focus:border-emerald-500 mb-4" placeholder="Berikan catatan persetujuan (opsional)..."></textarea>
                                <input type="hidden" name="status" value="disetujui">
                                <button type="submit" class="mt-auto w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3 rounded-lg transition shadow-md flex items-center justify-center gap-2 active:scale-95">
                                    <i class="fas fa-check-circle"></i> Setujui Pengajuan
                                </button>
                            </div>
                        </form>

                        {{-- FORM TOLAK --}}
                        <form action="{{ route('admin.cuti.updateStatus', $cuti->id) }}" method="POST">
                            @csrf @method('PUT')
                            <div class="bg-red-500/10 p-5 rounded-xl border border-red-500/20 h-full flex flex-col hover:shadow-md transition-all">
                                <label class="block text-sm font-bold text-red-400 mb-2">Tolak Pengajuan Cuti <span class="text-red-500">*</span></label>
                                <textarea name="alasan" rows="3" class="w-full p-3 bg-zinc-800 border border-zinc-700 rounded-lg text-sm text-white focus:ring-red-500 focus:border-red-500 mb-4" placeholder="Tuliskan alasan penolakan..." required></textarea>
                                <input type="hidden" name="status" value="ditolak">
                                <button type="submit" class="mt-auto w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3 rounded-lg transition shadow-md flex items-center justify-center gap-2 active:scale-95" onclick="return confirm('Yakin ingin menolak pengajuan ini?');">
                                    <i class="fas fa-times-circle"></i> Tolak Pengajuan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif

            {{-- [BARU] BUTTON TRIGGER MODAL AMBIL ALIH (Hanya muncul jika belum final & sudah disetujui approver 1) --}}
            @if (!in_array($cuti->status, ['disetujui', 'ditolak', 'dibatalkan']) && $cuti->status_approver_1 !== 'menunggu')
            <div class="mt-6 bg-zinc-900/50 p-4 rounded-xl border border-zinc-700">
                <div class="flex flex-col sm:flex-row justify-between items-center gap-3">
                    <div>
                        <h4 class="text-sm font-bold text-white"><i class="fas fa-user-shield text-amber-500 mr-1.5"></i> Ambil Alih Persetujuan Cuti</h4>
                        <p class="text-xs text-zinc-400 mt-1">Gunakan fitur ini untuk langsung menyetujui pengajuan cuti ini (Admin Override).</p>
                    </div>
                    <button onclick="openModal()" class="bg-amber-600 hover:bg-amber-700 text-white font-bold py-2.5 px-5 rounded-lg shadow-md transition flex items-center gap-2 text-sm whitespace-nowrap active:scale-95">
                        <i class="fas fa-user-shield"></i> Ambil Alih & Setujui
                    </button>
                </div>
            </div>
            @endif

            {{-- 6. FOOTER ACTION --}}
            <div class="mt-8 pt-6 border-t border-zinc-700 flex justify-end gap-3">
                <a href="{{ route('admin.cuti.download', $cuti->id) }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg shadow-lg transition text-sm">
                    <i class="fas fa-file-pdf"></i> Cetak Formulir PDF
                </a>
            </div>

        </div>
    </div>

    {{-- [BARU] MODAL ADMIN OVERRIDE --}}
    @if (!in_array($cuti->status, ['disetujui', 'ditolak', 'dibatalkan']) && $cuti->status_approver_1 !== 'menunggu')
    <div id="adminOverrideModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-zinc-900/40 backdrop-blur-sm transition-opacity" aria-hidden="true" onclick="closeModal()"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="relative inline-block align-bottom bg-zinc-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full border border-zinc-700">
                <div class="bg-zinc-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-amber-500/10 sm:mx-0 sm:h-10 sm:w-10">
                            <i class="fas fa-user-shield text-amber-500"></i>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-white" id="modal-title">
                                Konfirmasi Penyelesaian Manual (Cuti)
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-zinc-400 mb-4">
                                    Anda akan mengambil alih persetujuan cuti ini. Semua sisa persetujuan akan dilewati dan status pengajuan akan langsung berubah menjadi <b>DISETUJUI</b>. Jatah cuti karyawan juga akan langsung dipotong.
                                </p>

                                <form id="formOverride" action="{{ route('admin.cuti.forceApprove', $cuti->id) }}" method="POST" class="space-y-4">
                                    @csrf
                                    
                                    {{-- Input Catatan --}}
                                    <div>
                                        <label class="block text-sm font-medium text-zinc-300 mb-1">Catatan Persetujuan (Opsional)</label>
                                        <textarea name="catatan_admin" rows="2" placeholder="Catatan" 
                                            class="w-full bg-zinc-900 border border-zinc-700 rounded-lg text-sm text-white px-3 py-2 focus:ring-amber-500 focus:border-amber-500"></textarea>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-zinc-900/60 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2">
                    <button type="submit" form="formOverride" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-amber-600 text-base font-medium text-white hover:bg-amber-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                        Ya, Ambil Alih
                    </button>
                    <button type="button" onclick="closeModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-zinc-600 shadow-sm px-4 py-2 bg-zinc-700 text-base font-medium text-zinc-200 hover:bg-zinc-600 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Batal
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function openModal() {
            document.getElementById('adminOverrideModal').classList.remove('hidden');
        }
        function closeModal() {
            document.getElementById('adminOverrideModal').classList.add('hidden');
        }
    </script>
    @endif
</x-layout-admin>
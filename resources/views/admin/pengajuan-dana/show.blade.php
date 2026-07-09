<x-layout-admin>
    <x-slot:title>Detail Pengajuan Dana</x-slot:title>

    <div class="bg-zinc-800 rounded-xl shadow-lg border border-zinc-700 relative">
        {{-- Header --}}
        <div class="p-6 border-b border-zinc-700 flex justify-between items-center">
            <div>
                <h2 class="text-xl font-bold text-white">Detail Pengajuan: {{ $pengajuanDana->judul_pengajuan }}</h2>
                <p class="text-sm text-zinc-400 mt-1">
                    Diajukan oleh: 
                    <span class="font-medium text-zinc-300">{{ $pengajuanDana->user->name }}</span> 
                    - Divisi {{ $pengajuanDana->user->divisi }}
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.pengajuan_dana.index') }}" class="text-sm text-zinc-300 hover:text-white transition-colors duration-200 flex items-center bg-zinc-700 hover:bg-zinc-600 px-4 py-2 rounded-lg">
                    <i class="fas fa-arrow-left mr-2"></i> Kembali
                </a>
            </div>
        </div>

        {{-- Body --}}
        <div class="p-6 grid grid-cols-1 lg:grid-cols-3 gap-x-8 gap-y-6">
            
            {{-- KOLOM KIRI & TENGAH: DETAIL & DOKUMEN --}}
            <div class="lg:col-span-2 space-y-8">
                {{-- DETAIL UTAMA --}}
                <div>
                    <h3 class="text-lg font-semibold text-white mb-3 border-b border-zinc-700 pb-2">Informasi Pengajuan</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                        <div>
                            <label class="block font-medium text-zinc-400">Tanggal Pengajuan</label>
                            <p class="text-zinc-200">{{ $pengajuanDana->created_at->translatedFormat('l, d F Y - H:i') }}</p>
                        </div>
                        <div>
                            <label class="block font-medium text-zinc-400">Informasi Transfer</label>
                            <p class="text-zinc-200">{{ $pengajuanDana->nama_bank }} - {{ $pengajuanDana->no_rekening }} - A/N {{ $pengajuanDana->nama_rek }}</p>
                        </div>
                    </div>
                </div>

                {{-- RINCIAN DANA --}}
                <div>
                    <h3 class="text-lg font-semibold text-white mb-3 border-b border-zinc-700 pb-2">Rincian Dana</h3>
                    <div class="space-y-3 border border-zinc-700 rounded-lg p-4">
                        @forelse($pengajuanDana->rincian_dana as $rincian)
                        <div class="flex justify-between items-center text-zinc-300">
                            <span>- {{ $rincian['deskripsi'] ?? 'Item tidak ada deskripsi' }}</span>
                            <span class="font-mono">Rp {{ number_format($rincian['jumlah'] ?? 0, 0, ',', '.') }}</span>
                        </div>
                        @empty
                            <p class="text-zinc-400 text-sm">Rincian dana tidak tersedia.</p>
                        @endforelse
                        <div class="border-t border-zinc-600 pt-3 mt-3 flex justify-between items-center text-white font-bold">
                            <span>TOTAL</span>
                            <span class="font-mono text-xl text-amber-400">Rp {{ number_format($pengajuanDana->total_dana, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>

                {{-- DOKUMEN TERKAIT --}}
                <div>
                    <h3 class="text-lg font-semibold text-white mb-3 border-b border-zinc-700 pb-2">Dokumen Terkait</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        @if ($pengajuanDana->lampiran && count($pengajuanDana->lampiran) > 0)
                            @foreach ($pengajuanDana->lampiran as $lampiran)
                                <a href="{{ asset('storage/' . $lampiran) }}" target="_blank" class="bg-zinc-700 hover:bg-zinc-600 p-3 rounded-lg text-center text-zinc-300 transition">
                                    <i class="fas fa-paperclip mb-2 text-xl"></i>
                                    <p class="text-sm font-semibold">Lampiran {{ $loop->iteration }}</p>
                                    <p class="text-xs text-zinc-400 truncate">{{ basename($lampiran) }}</p>
                                </a>
                            @endforeach
                        @endif
                        @if($pengajuanDana->bukti_transfer)
                        <a href="{{ asset('storage/' . $pengajuanDana->bukti_transfer) }}" target="_blank" class="bg-zinc-700 hover:bg-zinc-600 p-3 rounded-lg text-center text-zinc-300 transition">
                            <i class="fas fa-receipt mb-2 text-xl"></i><p class="text-sm font-semibold">Bukti Transfer</p>
                        </a>
                        @endif
                        @if($pengajuanDana->invoice)
                        <a href="{{ asset('storage/' . $pengajuanDana->invoice) }}" target="_blank" class="bg-zinc-700 hover:bg-zinc-600 p-3 rounded-lg text-center text-zinc-300 transition">
                            <i class="fas fa-file-invoice-dollar mb-2 text-xl"></i><p class="text-sm font-semibold">Invoice Final</p>
                        </a>
                        @endif
                    </div>
                     @if (empty($pengajuanDana->lampiran) && !$pengajuanDana->bukti_transfer && !$pengajuanDana->invoice)
                        <p class="text-sm text-zinc-500 bg-zinc-700/50 border border-zinc-600 rounded-lg p-4 text-center">Tidak ada dokumen yang dilampirkan.</p>
                     @endif
                </div>
            </div>

            {{-- KOLOM KANAN: ALUR PERSETUJUAN --}}
            <div class="lg:col-span-1 space-y-6 border-t lg:border-t-0 lg:border-l border-zinc-700 pt-6 lg:pt-0 lg:pl-8">
                <h3 class="text-lg font-semibold text-white mb-3">Alur Persetujuan</h3>

                {{-- TAHAP 1: APPROVER 1 --}}
                @if ($pengajuanDana->approver1)
                    @php
                        $status = $pengajuanDana->approver_1_status;
                        $catatan = $pengajuanDana->approver_1_catatan;
                        $tanggal = $pengajuanDana->approver_1_approved_at;
                        $namaApprover = $pengajuanDana->approver1->name ?? 'Approver Dihapus';
                        
                        $statusClass = ''; $statusText = ''; $statusIcon = '';
                        switch ($status) {
                            case 'disetujui': $statusClass = 'text-emerald-400'; $statusIcon = 'fa-check-circle'; $statusText = 'Disetujui'; break;
                            case 'ditolak': $statusClass = 'text-red-400'; $statusIcon = 'fa-times-circle'; $statusText = 'Ditolak'; break;
                            case 'skipped': $statusClass = 'text-zinc-400'; $statusIcon = 'fa-minus-circle'; $statusText = 'Dilewati'; break;
                            default: $statusClass = 'text-yellow-400'; $statusIcon = 'fa-clock'; $statusText = 'Menunggu'; break;
                        }
                        if ($pengajuanDana->status === 'dibatalkan') {
                            $statusClass = 'text-zinc-400'; $statusIcon = 'fa-ban'; $statusText = 'Dibatalkan';
                        }
                    @endphp
                    <div>
                        <label class="block text-sm font-medium text-zinc-400 mb-1">
                            Approver 1: <span class="text-zinc-300">{{ $namaApprover }}</span>
                        </label>
                        <p class="flex items-center font-medium {{ $statusClass }}"><i class="fas {{ $statusIcon }} mr-2 w-4 text-center"></i> {{ $statusText }}</p>
                        
                        @if($tanggal || $catatan)
                        <div class="mt-2 space-y-1">
                            @if($tanggal)
                                <p class="text-xs text-zinc-400 flex items-center">
                                    <i class="fas fa-calendar-alt fa-fw mr-2 text-zinc-500"></i>
                                    <span>{{ $tanggal->translatedFormat('d M Y, H:i') }}</span>
                                </p>
                            @endif
                            @if($catatan)
                            <div class="text-xs text-zinc-500 border-l-2 border-zinc-600 pl-2 italic">"{{ $catatan }}"</div>
                            @endif
                        </div>
                        @endif
                    </div>
                @elseif ($pengajuanDana->approver_1_status === 'skipped')
                    <div>
                        <label class="block text-sm font-medium text-zinc-400 mb-1">Approver 1</label>
                        <p class="flex items-center text-zinc-400"><i class="fas fa-minus-circle mr-2"></i> Dilewati</p>
                    </div>
                @endif

                {{-- TAHAP 2: APPROVER 2 --}}
                 @if ($pengajuanDana->approver2)
                    @php
                        $status = $pengajuanDana->approver_2_status;
                        $catatan = $pengajuanDana->approver_2_catatan;
                        $tanggal = $pengajuanDana->approver_2_approved_at;
                        $namaApprover = $pengajuanDana->approver2->name ?? 'Approver Dihapus';
                        
                        $statusClass = ''; $statusText = ''; $statusIcon = '';
                        switch ($status) {
                            case 'disetujui': $statusClass = 'text-emerald-400'; $statusIcon = 'fa-check-circle'; $statusText = 'Disetujui'; break;
                            case 'ditolak': $statusClass = 'text-red-400'; $statusIcon = 'fa-times-circle'; $statusText = 'Ditolak'; break;
                            case 'skipped': $statusClass = 'text-zinc-400'; $statusIcon = 'fa-minus-circle'; $statusText = 'Dilewati'; break;
                            default: $statusClass = 'text-yellow-400'; $statusIcon = 'fa-clock'; $statusText = 'Menunggu'; break;
                        }
                        if ($pengajuanDana->status === 'dibatalkan') {
                            $statusClass = 'text-zinc-400'; $statusIcon = 'fa-ban'; $statusText = 'Dibatalkan';
                        }
                    @endphp
                    <div>
                        <label class="block text-sm font-medium text-zinc-400 mb-1">
                            Approver 2: <span class="text-zinc-300">{{ $namaApprover }}</span>
                        </label>
                         <p class="flex items-center font-medium {{ $statusClass }}"><i class="fas {{ $statusIcon }} mr-2 w-4 text-center"></i> {{ $statusText }}</p>
                        
                        @if($tanggal || $catatan)
                        <div class="mt-2 space-y-1">
                            @if($tanggal)
                                <p class="text-xs text-zinc-400 flex items-center">
                                    <i class="fas fa-calendar-alt fa-fw mr-2 text-zinc-500"></i>
                                    <span>{{ $tanggal->translatedFormat('d M Y, H:i') }}</span>
                                </p>
                            @endif
                            @if($catatan)
                            <div class="text-xs text-zinc-500 border-l-2 border-zinc-600 pl-2 italic">"{{ $catatan }}"</div>
                            @endif
                        </div>
                        @endif
                    </div>
                @elseif ($pengajuanDana->approver_2_status === 'skipped')
                    <div>
                        <label class="block text-sm font-medium text-zinc-400 mb-1">Approver 2</label>
                        <p class="flex items-center text-zinc-400"><i class="fas fa-minus-circle mr-2"></i> Dilewati</p>
                    </div>
                @endif


                {{-- TAHAP 3: MANAGER KEUANGAN --}}
                @if ($pengajuanDana->user->managerKeuangan)
                    @php
                        $status = $pengajuanDana->payment_status;
                        $catatan = $pengajuanDana->catatan_finance;
                        $tanggal = $pengajuanDana->finance_processed_at; 
                        $namaApprover = $pengajuanDana->user->managerKeuangan->name ?? 'Finance Dihapus';
                        
                        $statusClass = ''; $statusText = ''; $statusIcon = '';
                        switch ($status) {
                            case 'selesai': $statusClass = 'text-emerald-400'; $statusIcon = 'fa-check-circle'; $statusText = 'Selesai (Dibayar)'; break;
                            case 'diproses': $statusClass = 'text-blue-400'; $statusIcon = 'fa-sync-alt'; $statusText = 'Sedang Diproses'; break;
                            case 'ditolak': $statusClass = 'text-red-400'; $statusIcon = 'fa-times-circle'; $statusText = 'Ditolak'; break;
                            case 'skipped': $statusClass = 'text-zinc-400'; $statusIcon = 'fa-minus-circle'; $statusText = 'Dilewati'; break;
                            default: $statusClass = 'text-yellow-400'; $statusIcon = 'fa-clock'; $statusText = 'Menunggu'; break;
                        }
                        
                        if ($pengajuanDana->status === 'dibatalkan') {
                            $statusClass = 'text-zinc-400'; $statusIcon = 'fa-ban'; $statusText = 'Dibatalkan';
                        } 
                        elseif ($pengajuanDana->status === 'ditolak') {
                            $statusClass = 'text-zinc-400'; $statusIcon = 'fa-ban'; 
                            if ($pengajuanDana->approver_1_status === 'ditolak') {
                                $statusText = 'Dihentikan (Ditolak Appr 1)';
                            } elseif ($pengajuanDana->approver_2_status === 'ditolak') {
                                $statusText = 'Dihentikan (Ditolak Appr 2)';
                            } else {
                                $statusText = 'Dihentikan';
                            }
                        }

                        if ($status === 'selesai') {
                            $tanggal = $pengajuanDana->updated_at;
                        }
                    @endphp
                    <div>
                        <label class="block text-sm font-medium text-zinc-400 mb-1">
                            Manager Keuangan: <span class="text-zinc-300">{{ $namaApprover }}</span>
                        </label>
                        <p class="flex items-center font-medium {{ $statusClass }}"><i class="fas {{ $statusIcon }} mr-2 w-4 text-center"></i> {{ $statusText }}</p>
                        
                        @if(in_array($pengajuanDana->payment_status, ['diproses', 'selesai', 'ditolak']) && ($tanggal || $catatan))
                        <div class="mt-2 space-y-1">
                            @if($tanggal)
                                <p class="text-xs text-zinc-400 flex items-center">
                                    <i class="fas fa-calendar-alt fa-fw mr-2 text-zinc-500"></i>
                                    <span>{{ $tanggal->translatedFormat('d M Y, H:i') }}</span>
                                </p>
                            @endif
                            @if($catatan)
                            <div class="text-xs text-zinc-500 border-l-2 border-zinc-600 pl-2 italic">"{{ $catatan }}"</div>
                            @endif
                        </div>
                        @endif
                    </div>
                @elseif ($pengajuanDana->payment_status === 'skipped')
                     <div>
                        <label class="block text-sm font-medium text-zinc-400 mb-1">Manager Keuangan</label>
                        <p class="flex items-center text-zinc-400"><i class="fas fa-minus-circle mr-2"></i> Dilewati</p>
                    </div>
                @endif
                
                
                {{-- [MODIFIKASI] BUTTON TRIGGER MODAL (Hanya muncul jika 'proses_pembayaran') --}}
                @if ($pengajuanDana->status === 'proses_pembayaran')
                <div class="mt-4">
                    <button onclick="openModal()" class="w-full bg-amber-600 hover:bg-amber-700 text-white font-bold py-3 px-4 rounded-lg shadow-lg transform transition hover:-translate-y-0.5 flex justify-center items-center gap-2">
                        <i class="fas fa-user-shield"></i> Ambil Alih & Tandai Selesai
                    </button>
                    <p class="text-xs text-zinc-500 mt-2 text-center">
                        *Klik untuk menyelesaikan pembayaran secara manual (Admin Override).
                    </p>
                </div>
                @endif

                {{-- STATUS FINAL --}}
                <div class="border-t border-zinc-700 pt-4 mt-2">
                    <label class="block text-sm font-medium text-zinc-400 mb-1">Status Final Pengajuan</label>
                     @if ($pengajuanDana->status == 'selesai')
                        <p class="text-xl font-bold flex items-center text-emerald-400"><i class="fas fa-check-circle mr-2"></i> SELESAI</p>
                    @elseif ($pengajuanDana->status == 'ditolak')
                        <p class="text-xl font-bold flex items-center text-red-400"><i class="fas fa-times-circle mr-2"></i> DITOLAK</p>
                    @elseif ($pengajuanDana->status == 'proses_pembayaran' || $pengajuanDana->status == 'diproses_appr_2')
                        <p class="text-xl font-bold flex items-center text-blue-400"><i class="fas fa-sync-alt fa-spin mr-2"></i> DIPROSES</p>
                    @elseif ($pengajuanDana->status == 'dibatalkan')
                        <p class="text-xl font-bold flex items-center text-zinc-400"><i class="fas fa-ban mr-2"></i> DIBATALKAN</p>
                    @else 
                        <p class="text-xl font-bold flex items-center text-yellow-400"><i class="fas fa-hourglass-start mr-2"></i> DIAJUKAN</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- [BARU] MODAL ADMIN OVERRIDE --}}
    @if ($pengajuanDana->status === 'proses_pembayaran')
    <div id="adminOverrideModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        {{-- Backdrop --}}
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-black bg-opacity-75 transition-opacity" aria-hidden="true" onclick="closeModal()"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            {{-- Modal Panel --}}
            <div class="inline-block align-bottom bg-zinc-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full border border-zinc-700">
                <div class="bg-zinc-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-amber-500/10 sm:mx-0 sm:h-10 sm:w-10">
                            <i class="fas fa-user-shield text-amber-500"></i>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-white" id="modal-title">
                                Konfirmasi Penyelesaian Manual
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-zinc-400 mb-4">
                                    Anda akan mengambil alih tugas Manajer Keuangan. Status pengajuan akan langsung berubah menjadi <b>SELESAI</b>.
                                </p>

                                <form id="formOverride" action="{{ route('admin.pengajuan_dana.markAsPaid', $pengajuanDana->id) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                                    @csrf
                                    
                                    {{-- Input Bukti Transfer --}}
                                    <div>
                                        <label class="block text-sm font-medium text-zinc-300 mb-1">Upload Bukti Transfer (Opsional)</label>
                                        <input type="file" name="bukti_transfer" class="block w-full text-sm text-zinc-400
                                            file:mr-4 file:py-2 file:px-4
                                            file:rounded-md file:border-0
                                            file:text-sm file:font-semibold
                                            file:bg-zinc-700 file:text-zinc-200
                                            hover:file:bg-zinc-600 cursor-pointer focus:outline-none"
                                        >
                                    </div>

                                    {{-- Input Catatan --}}
                                    <div>
                                        <label class="block text-sm font-medium text-zinc-300 mb-1">Catatan (Opsional)</label>
                                        <textarea name="catatan_admin" rows="2" placeholder="Catatan" 
                                            class="w-full bg-zinc-900 border-zinc-600 rounded-lg text-sm text-white px-3 py-2 focus:ring-amber-500 focus:border-amber-500"></textarea>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-zinc-700/50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2">
                    <button type="button" onclick="document.getElementById('formOverride').submit()" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-emerald-600 text-base font-medium text-white hover:bg-emerald-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                        <i class="fas fa-check mr-2 mt-1"></i> Simpan & Selesai
                    </button>
                    <button type="button" onclick="closeModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-zinc-600 shadow-sm px-4 py-2 bg-zinc-800 text-base font-medium text-zinc-300 hover:text-white hover:bg-zinc-700 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Batal
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Script Modal Sederhana --}}
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
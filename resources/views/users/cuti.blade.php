<x-layout-users>
    <x-slot:title>{{ $title }}</x-slot:title>

    {{-- Ikon (Font Awesome) --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <div class="bg-gray-100 font-sans bg-gradient-to-br from-sky-50 to-blue-100 p-0 md:p-0 min-h-screen">
        <div class="max-w-7xl mx-auto space-y-6">

            {{-- TOMBOL KEMBALI --}}
            <div class="mb-4">
                <a href="{{ route('dashboard') }}" 
                class="inline-flex items-center justify-center w-auto h-10 px-4 rounded-lg bg-gradient-to-r from-blue-700 to-blue-600 text-white shadow-md hover:shadow-lg hover:brightness-110 transition-all gap-2"
                title="Kembali ke Dashboard">
                    <i class="fas fa-arrow-left"></i>
                    <span class="font-medium text-sm">Kembali</span>
                </a>
            </div>

            {{-- Grid Utama --}}
            <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">

                {{-- 1. Kartu Sisa Cuti --}}
                <div class="order-1 lg:order-2 lg:col-span-2 bg-gradient-to-r from-blue-700 to-blue-600 text-white p-6 rounded-2xl shadow-xl">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-indigo-200">Sisa Cuti Tahun Ini</p>
                            <p class="text-4xl font-extrabold tracking-tight">
                                {{ $sisaCuti ?? 0 }} <span class="text-2xl font-semibold text-indigo-300">/ {{ $totalCuti ?? 0 }} Hari</span>
                            </p>
                        </div>
                        <div class="bg-white/20 p-3 rounded-xl">
                            <i class="fas fa-chart-pie text-2xl"></i>
                        </div>
                    </div>
                </div>

                {{-- 2. Form Pengajuan Cuti --}}
                <div class="order-2 lg:order-1 lg:col-span-3 lg:row-span-2 bg-white p-6 md:p-8 rounded-2xl shadow-xl border border-gray-200 flex flex-col">
                    <h3 class="text-xl font-bold text-gray-900 mb-6">Ajukan Cuti Baru</h3>
                    <form action="{{ route('cuti.store') }}" method="POST" enctype="multipart/form-data" class="flex flex-col flex-grow space-y-6">
                        @csrf
                        <input type="hidden" name="jenis_cuti" value="tahunan">

                        <div class="flex-grow space-y-6">
                            {{-- Input Tanggal --}}
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                <div>
                                    <label for="tanggal_mulai" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Mulai</label>
                                    <input type="date" 
                                           id="tanggal_mulai" 
                                           name="tanggal_mulai" 
                                           min="{{ \Carbon\Carbon::now()->toDateString() }}"
                                           value="{{ old('tanggal_mulai') }}" 
                                           class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5 mt-1 shadow-sm @error('tanggal_mulai') border-red-500 @enderror">
                                </div>
                                <div>
                                    <label for="tanggal_selesai" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Selesai</label>
                                    <input type="date" 
                                           id="tanggal_selesai" 
                                           name="tanggal_selesai" 
                                           min="{{ \Carbon\Carbon::now()->toDateString() }}"
                                           value="{{ old('tanggal_selesai') }}" 
                                           class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5 mt-1 shadow-sm @error('tanggal_selesai') border-red-500 @enderror">
                                </div>
                            </div>
                            
                            {{-- Input Alasan --}}
                            <div>
                                <label for="alasan" class="block text-sm font-medium text-gray-700 mb-1">Alasan</label>
                                <textarea id="alasan" name="alasan" rows="4" class="w-full p-3 mt-1 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500 @error('alasan') border-red-500 @enderror" placeholder="Jelaskan alasan Anda mengajukan cuti...">{{ old('alasan') }}</textarea>
                            </div>

                            {{-- Input Lampiran --}}
                            <div>
                                <label for="lampiran" class="block text-sm font-medium text-gray-700 mb-1">Lampiran (Opsional)</label>
                                <input type="file" id="lampiran" name="lampiran" class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none p-2 mt-1">
                                <p class="mt-1 text-xs text-gray-500">Format: JPG, PNG, PDF (Max. 2MB)</p>
                            </div>
                        </div>

                        {{-- Tombol Kirim --}}
                        <div class="pt-4">
                            <button type="submit" class="w-full bg-gradient-to-r from-blue-700 to-blue-600 hover:from-blue-800 hover:to-blue-700 text-white font-bold py-3 px-4 rounded-lg shadow-lg hover:shadow-xl transition-all duration-300 flex items-center justify-center gap-2 transform hover:-translate-y-1">
                                <i class="fas fa-paper-plane"></i>
                                Kirim Pengajuan
                            </button>
                        </div>

                        {{-- Alert Error --}}
                        @if ($errors->any())
                            <div class="!mt-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-md text-sm" role="alert">
                                <p class="font-bold">Gagal Mengajukan Cuti</p>
                                <ul class="list-disc list-inside ml-4">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </form>
                </div>

                {{-- 3. Kartu Ringkasan --}}
                <div class="order-3 lg:order-3 lg:col-span-2 bg-white p-6 rounded-2xl shadow-xl border border-gray-200 flex flex-col">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">Ringkasan</h3>
                    <div class="space-y-4 flex-grow flex flex-col justify-center">
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div class="flex items-center">
                                <i class="fas fa-calendar-day fa-lg text-gray-400"></i>
                                <p class="ml-3 text-sm font-medium text-gray-600">Mulai Cuti</p>
                            </div>
                            <p id="ringkasan-mulai" class="font-bold text-sm text-gray-800">-</p>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div class="flex items-center">
                                <i class="fas fa-calendar-check fa-lg text-gray-400"></i>
                                <p class="ml-3 text-sm font-medium text-gray-600">Selesai Cuti</p>
                            </div>
                            <p id="ringkasan-selesai" class="font-bold text-sm text-gray-800">-</p>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div class="flex items-center">
                                <i class="fas fa-hourglass-half fa-lg text-gray-400"></i>
                                <p class="ml-3 text-sm font-medium text-gray-600">Total Durasi</p>
                            </div>
                            <p id="total-hari" class="font-bold text-sm text-gray-800">- Hari</p>
                        </div>
                        <div class="text-xs text-gray-500 bg-yellow-50 p-2 rounded border border-yellow-200">
                            <i class="fas fa-info-circle mr-1"></i> Sabtu, Minggu, & Tanggal Merah tidak dihitung.
                        </div>
                    </div>
                </div>

                {{-- 4. Riwayat Pengajuan --}}
                <div class="order-4 lg:col-span-5 bg-white p-6 md:p-8 rounded-2xl shadow-xl border border-gray-200">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">Riwayat Pengajuan</h2>
                    <div class="space-y-4">
                        @forelse ($cutiRequests as $cuti)
                        <a href="{{ route('cuti.show', $cuti) }}" class="block p-4 rounded-xl border border-gray-200 hover:border-indigo-300 hover:bg-indigo-50 transition-all duration-300">
                            <div class="flex items-start justify-between gap-4"> 
                            <div class="flex items-center">
                                <div class="w-10 h-10 flex-shrink-0 flex items-center justify-center rounded-full
                                    @if($cuti->status == 'disetujui') bg-green-100 text-green-600
                                    @elseif($cuti->status == 'ditolak') bg-red-100 text-red-600
                                    @elseif($cuti->status == 'dibatalkan') bg-gray-100 text-gray-500
                                    @else bg-yellow-100 text-yellow-600 @endif">
                                    <i class="fas
                                        @if($cuti->status == 'disetujui') fa-check
                                        @elseif($cuti->status == 'ditolak') fa-times
                                        @elseif($cuti->status == 'dibatalkan') fa-ban
                                        @else fa-clock @endif"></i>
                                </div>
                                <div class="ml-4">
                                    <p class="font-semibold text-gray-800">
                                        {{ \Carbon\Carbon::parse($cuti->tanggal_mulai)->format('d M Y') }} - {{ \Carbon\Carbon::parse($cuti->tanggal_selesai)->format('d M Y') }}
                                    </p>
                                    <p class="text-sm text-gray-500">
                                        Diajukan: {{ \Carbon\Carbon::parse($cuti->created_at)->format('d M Y') }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex-shrink-0">
                                <span class="px-3 py-1 text-xs font-bold rounded-full
                                    @if($cuti->status == 'disetujui') bg-green-100 text-green-800
                                    @elseif($cuti->status == 'ditolak') bg-red-100 text-red-800
                                    @elseif($cuti->status == 'dibatalkan') bg-gray-100 text-gray-800
                                    @else bg-yellow-100 text-yellow-800 @endif">
                                    {{ ucfirst($cuti->status) }}
                                </span>
                            </div>
                        </div>
                        </a>
                        @empty
                        <div class="text-center py-10 border-2 border-dashed rounded-xl">
                            <i class="fas fa-box-open text-4xl text-gray-300 mb-3"></i>
                            <p class="text-gray-500 font-medium">Belum ada riwayat pengajuan cuti.</p>
                        </div>
                        @endforelse
                    </div>
                </div>

            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const tglMulai = document.getElementById('tanggal_mulai');
        const tglSelesai = document.getElementById('tanggal_selesai');
        const totalHariElem = document.getElementById('total-hari');
        const ringkasanMulaiElem = document.getElementById('ringkasan-mulai');
        const ringkasanSelesaiElem = document.getElementById('ringkasan-selesai');

        // [PERBAIKAN] Pastikan data diterima sebagai Array of Strings
        const liburNasional = @json($liburNasional ?? []);
        console.log("Data Libur (Formatted):", liburNasional);

        function formatTanggal(tanggalStr) {
            if (!tanggalStr) return '-';
            const options = { day: 'numeric', month: 'short', year: 'numeric' };
            const date = new Date(tanggalStr + 'T00:00:00');
            return date.toLocaleDateString('id-ID', options);
        }

        function hitungDurasi() {
            const startDateStr = tglMulai.value;
            const endDateStr = tglSelesai.value;

            ringkasanMulaiElem.textContent = formatTanggal(startDateStr);
            ringkasanSelesaiElem.textContent = formatTanggal(endDateStr);

            if (startDateStr && endDateStr) {
                const start = new Date(startDateStr);
                const end = new Date(endDateStr);

                // Reset jam ke 00:00:00
                start.setHours(0,0,0,0);
                end.setHours(0,0,0,0);

                if (end < start) {
                    totalHariElem.textContent = 'Tanggal Invalid';
                    totalHariElem.className = 'font-bold text-sm text-red-600';
                    return;
                }

                let countDays = 0;
                let currentDate = new Date(start);

                while (currentDate <= end) {
                    const dayOfWeek = currentDate.getDay(); // 0=Minggu, 6=Sabtu
                    
                    // [PERBAIKAN UTAMA] Format ke YYYY-MM-DD untuk dicocokkan dengan data backend
                    const year = currentDate.getFullYear();
                    const month = String(currentDate.getMonth() + 1).padStart(2, '0');
                    const day = String(currentDate.getDate()).padStart(2, '0');
                    const dateString = `${year}-${month}-${day}`;

                    // Logika: Cek apakah hari ini Sabtu, Minggu, atau Tanggal Merah
                    const isWeekend = (dayOfWeek === 0 || dayOfWeek === 6);
                    const isHoliday = liburNasional.includes(dateString);

                    if (!isWeekend && !isHoliday) {
                        countDays++;
                    }

                    // Next day
                    currentDate.setDate(currentDate.getDate() + 1);
                }

                if (countDays === 0) {
                     totalHariElem.textContent = '0 Hari (Full Libur)';
                     totalHariElem.className = 'font-bold text-sm text-red-600';
                } else {
                     totalHariElem.textContent = `${countDays} Hari Kerja`;
                     totalHariElem.className = 'font-bold text-sm text-gray-800';
                }
            } else {
                totalHariElem.textContent = '- Hari';
                totalHariElem.className = 'font-bold text-sm text-gray-800';
            }
        }

        tglMulai.addEventListener('change', hitungDurasi);
        tglSelesai.addEventListener('change', hitungDurasi);

        hitungDurasi();
    });
    </script>
    @endpush
</x-layout-users>
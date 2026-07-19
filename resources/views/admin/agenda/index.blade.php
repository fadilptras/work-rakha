<x-layout-admin>
    <x-slot:title>{{ $title }}</x-slot:title>

    @push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        /* Styling untuk FullCalendar (Kompak & Elegan) */
        #admin-calendar-wrapper .fc { border: none !important; background: #27272a; border-radius: 0.75rem; padding: 0.5rem; color: #d1d5db; font-size: 0.8rem; height: 100%; }
        #admin-calendar-wrapper .fc .fc-toolbar-title { font-size: 1rem; font-weight: 700; color: #ffffff; }
        #admin-calendar-wrapper .fc .fc-button { background: transparent !important; border: none !important; box-shadow: none !important; color: #9ca3af; padding: 0.2rem 0.4rem; font-size: 0.8rem; transition: all 0.2s; }
        #admin-calendar-wrapper .fc .fc-button:hover { color: #ffffff; background: #374151 !important; }
        #admin-calendar-wrapper .fc .fc-col-header-cell-cushion { color: #9ca3af; font-weight: 600; font-size: 0.75rem; }
        #admin-calendar-wrapper .fc .fc-daygrid-day-number { color: #d1d5db; font-size: 0.75rem; padding: 4px !important; }
        #admin-calendar-wrapper .fc .fc-day-other .fc-daygrid-day-number { color: #4b5563; }
        
        #admin-calendar-wrapper .fc .fc-day-today .fc-daygrid-day-number {
            font-weight: 700;
            color: #ffffff;
            background: #0284c7;
            border-radius: 9999px;
            padding: 1px 5px !important;
        }
        
        /* Event Calendar Kompak */
        .fc-daygrid-event {
            padding: 1px 4px !important;
            margin: 1px 2px !important;
            border-radius: 4px;
            font-size: 0.7rem;
            line-height: 1.2;
            border: 1px solid rgba(255, 255, 255, 0.1);
            cursor: pointer;
            transition: all 0.2s ease;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .fc-daygrid-event:hover {
            background-color: rgba(255, 255, 255, 0.15) !important;
            transform: scale(1.02);
        }
        
        /* Custom scrollbar untuk list/table scrollable */
        .custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: rgba(39, 39, 42, 0.5); border-radius: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #4b5563; border-radius: 4px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #6b7280; }

        /* Styling untuk Paginasi */
        .pagination { display: flex; list-style: none; padding: 0; }
        .pagination li { margin: 0 4px; }
        .pagination li a, .pagination li span {
            display: block;
            padding: 8px 12px;
            background-color: #3f3f46;
            color: #d4d4d8;
            border-radius: 6px;
            transition: background-color 0.2s;
        }
        .pagination li a:hover { background-color: #52525b; }
        .pagination li.active span { background-color: #0284c7; color: #ffffff; font-weight: bold; }
        .pagination li.disabled span { background-color: #27272a; color: #71717a; cursor: not-allowed; }
    </style>
    @endpush

    {{-- Header Halaman --}}
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-white">Manajemen Agenda Kantor</h1>
        <button id="add-agenda-btn" 
            class="bg-sky-600 hover:bg-sky-700 text-white font-bold py-2.5 px-4 rounded-lg shadow-md flex items-center transition-transform duration-200 hover:scale-105">
            <i class="fas fa-plus mr-2"></i> Buat Agenda Baru
        </button>
    </div>

    
    @if ($errors->any())
        <div class="mb-6 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-md shadow-sm" role="alert">
            <strong class="font-bold">Oops! Terjadi kesalahan.</strong>
            <ul class="list-disc ml-5 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Grid Layout Utama (Equal Height items-stretch) --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-stretch">
        {{-- Kiri: Kalender Agenda --}}
        <div class="lg:col-span-5 flex flex-col">
            <div class="bg-zinc-800 rounded-xl shadow-lg border border-zinc-700 p-5 flex flex-col lg:h-[620px]">
                <div class="border-b border-zinc-700 pb-3 mb-4 flex-shrink-0">
                    <h2 class="text-sm font-bold text-sky-400 flex items-center">
                        <i class="fas fa-calendar-alt mr-2.5 text-sky-400"></i> Kalender Kegiatan
                    </h2>
                </div>
                <div id="admin-calendar-wrapper" class="flex-grow overflow-y-auto custom-scrollbar">
                    <div id="admin-calendar"></div>
                </div>
            </div>
        </div>

        {{-- Kanan: Daftar Semua Agenda & Filter --}}
        <div class="lg:col-span-7 flex flex-col">
            <div class="bg-zinc-800 rounded-xl shadow-lg border border-zinc-700 overflow-hidden flex flex-col lg:h-[620px]">
                <div class="px-6 py-4 bg-zinc-700/50 border-b border-zinc-700 flex justify-between items-center flex-shrink-0">
                    <h2 class="text-sm font-bold text-sky-400 flex items-center">
                        <i class="fas fa-list mr-2.5"></i> Daftar Semua Agenda
                    </h2>
                    <span class="bg-sky-600 text-white text-xs px-2.5 py-1 rounded-full font-semibold">
                        {{ $allAgendas->total() }} Agenda
                    </span>
                </div>

                <div class="p-6 flex flex-col flex-grow overflow-hidden">
                    {{-- Form Filter (Tetap di atas) --}}
                    <form method="GET" action="{{ route('admin.agenda.index') }}" class="mb-4 p-4 bg-zinc-900/40 border border-zinc-700 rounded-lg flex-shrink-0">
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-end">
                            <div>
                                <label for="start_date" class="block text-[10px] font-bold text-zinc-400 uppercase tracking-wider mb-1.5">Dari Tanggal</label>
                                <input type="date" name="start_date" id="start_date" value="{{ request('start_date') }}" class="w-full bg-zinc-700 border border-zinc-600 rounded-lg text-white text-xs px-3 py-2 focus:outline-none focus:border-sky-500 focus:ring-1 focus:ring-sky-500">
                            </div>
                            <div>
                                <label for="end_date" class="block text-[10px] font-bold text-zinc-400 uppercase tracking-wider mb-1.5">Sampai Tanggal</label>
                                <input type="date" name="end_date" id="end_date" value="{{ request('end_date') }}" class="w-full bg-zinc-700 border border-zinc-600 rounded-lg text-white text-xs px-3 py-2 focus:outline-none focus:border-sky-500 focus:ring-1 focus:ring-sky-500">
                            </div>
                            <div class="flex gap-2">
                                <button type="submit" class="w-full bg-sky-600 hover:bg-sky-700 text-white font-semibold py-2 px-3 rounded-lg text-xs transition-colors flex items-center justify-center gap-1.5 shadow">
                                    <i class="fas fa-filter"></i> Filter
                                </button>
                                <a href="{{ route('admin.agenda.index') }}" class="w-full bg-zinc-600 hover:bg-zinc-500 text-white font-semibold py-2 px-3 rounded-lg text-xs text-center transition-colors flex items-center justify-center gap-1.5">
                                    <i class="fas fa-undo"></i> Reset
                                </a>
                            </div>
                        </div>
                    </form>

                    {{-- Tabel Daftar Agenda (Dapat di-scroll jika melebihi batas) --}}
                    <div class="overflow-y-auto flex-grow custom-scrollbar border border-zinc-700 rounded-lg bg-zinc-900/20">
                        <table class="min-w-full divide-y divide-zinc-700">
                            <thead class="bg-zinc-800/50 sticky top-0 z-10">
                                <tr>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider bg-zinc-800">Agenda</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider bg-zinc-800">Waktu</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider font-semibold bg-zinc-800">Tamu</th>
                                    <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-400 uppercase tracking-wider bg-zinc-800">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-zinc-800 divide-y divide-zinc-700">
                                @forelse ($allAgendas as $agenda)
                                    <tr class="hover:bg-zinc-700/50 transition-colors">
                                        <td class="px-4 py-3">
                                            <div class="flex items-center">
                                                <span class="inline-block w-2.5 h-2.5 rounded-full mr-2 shadow-sm border border-zinc-600 flex-shrink-0" style="background-color: {{ $agenda->color ?? '#0284c7' }}"></span>
                                                <div>
                                                    <div class="text-xs font-semibold text-white max-w-[150px] truncate" title="{{ $agenda->title }}">
                                                        {{ $agenda->title }}
                                                    </div>
                                                    <div class="text-[10px] text-gray-400 mt-0.5 truncate max-w-[150px]">
                                                        {{ $agenda->location ?? '-' }}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <div class="text-xs text-gray-300">
                                                {{ \Carbon\Carbon::parse($agenda->start_time)->isoFormat('D MMM YYYY') }}
                                            </div>
                                            <div class="text-[10px] text-gray-400 mt-0.5">
                                                {{ \Carbon\Carbon::parse($agenda->start_time)->format('H:i') }} WIB
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-zinc-750 text-gray-300 border border-zinc-700">
                                                <i class="fas fa-users mr-1 text-gray-400"></i>
                                                {{ $agenda->guests->count() }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-right text-xs font-medium">
                                            <button class="open-detail-btn text-emerald-400 hover:text-emerald-300 mr-2.5" data-agenda-id="{{ $agenda->id }}" title="Lihat Detail">
                                                <i class="fas fa-eye fa-lg"></i>
                                            </button>
                                            
                                            <button class="open-edit-btn text-sky-400 hover:text-sky-300 mr-2.5" data-agenda-id="{{ $agenda->id }}" title="Edit Agenda">
                                                <i class="fas fa-edit fa-lg"></i>
                                            </button>
                                            
                                            <form action="{{ route('admin.agenda.destroy', $agenda->id) }}" method="POST" class="inline-block" onsubmit="confirmSubmit(event, 'Apakah Anda yakin ingin menghapus agenda ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-400 hover:text-red-300" title="Hapus Agenda">
                                                    <i class="fas fa-trash-alt fa-lg"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-4 py-8 text-center text-gray-400">
                                            <i class="fas fa-calendar-times text-3xl text-gray-500 mb-2"></i>
                                            <p class="text-xs">Tidak ada agenda.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Paginasi (Tetap di bawah) --}}
                    <div class="mt-4 flex-shrink-0">
                        {{ $allAgendas->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    {{-- MODAL TAMBAH/EDIT AGENDA --}}
    <div id="agenda-modal" class="fixed inset-0 bg-black/60 backdrop-blur-sm flex justify-center items-center hidden z-50 transition-opacity duration-300 opacity-0">
        <div class="bg-zinc-800 rounded-xl shadow-2xl w-full max-w-3xl transform scale-95 transition-transform duration-300 max-h-[90vh] flex flex-col border border-zinc-700 mx-4">
            <div class="flex justify-between items-center p-6 border-b border-zinc-700">
                <h3 id="modal-title" class="text-xl font-bold text-white flex items-center">
                    Buat Agenda Baru
                </h3>
                <button type="button" class="close-modal text-gray-400 hover:text-white text-2xl leading-none">&times;</button>
            </div>
            
            <div class="p-6 overflow-y-auto custom-scrollbar flex-grow">
                {{-- INFO BOX --}}
                <div class="mb-6 bg-sky-900/30 border border-sky-700 rounded-lg p-4 flex items-start gap-3 shadow-inner">
                    <div class="text-sky-400 mt-0.5">
                        <i class="fas fa-info-circle text-2xl"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-sky-300">
                            Manajemen Agenda Kantor
                        </h4>
                        <p class="text-xs text-gray-300 mt-1">Buat agenda rapat, koordinasi, atau acara kantor. Karyawan yang diundang akan otomatis menerima notifikasi di akun masing-masing.</p>
                    </div>
                </div>

                <form id="agenda-form" method="POST">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-zinc-300"> 
                        <div class="mb-4">
                            <label for="title" class="block text-sm font-medium text-gray-300 mb-1">Judul Agenda <span class="text-red-500">*</span></label>
                            <input type="text" id="title" name="title" required class="w-full bg-zinc-700 border border-zinc-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-sky-500 focus:ring-1 focus:ring-sky-500" placeholder="Contoh: Rapat Evaluasi Bulanan">
                        </div>
                        
                        <div class="mb-4">
                            <label for="location" class="block text-sm font-medium text-gray-300 mb-1">Lokasi</label>
                            <input type="text" id="location" name="location" class="w-full bg-zinc-700 border border-zinc-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-sky-500 focus:ring-1 focus:ring-sky-500" placeholder="Contoh: Ruang Meeting Lt. 2">
                        </div>

                        <div class="mb-4 md:col-span-2">
                            <label for="description" class="block text-sm font-medium text-gray-300 mb-1">Deskripsi</label>
                            <textarea id="description" name="description" rows="3" class="w-full bg-zinc-700 border border-zinc-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-sky-500 focus:ring-1 focus:ring-sky-500" placeholder="Jelaskan detail agenda di sini..."></textarea>
                        </div>
                        
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-300 mb-1">Waktu Acara <span class="text-red-500">*</span></label>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label for="agenda_date" class="block text-[10px] text-gray-400 uppercase tracking-wider mb-1">Tanggal</label>
                                    <input type="text" id="agenda_date" required class="w-full bg-zinc-700 border border-zinc-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-sky-500 focus:ring-1 focus:ring-sky-500" placeholder="Pilih Tanggal">
                                </div>
                                <div>
                                    <label for="start_hour" class="block text-[10px] text-gray-400 uppercase tracking-wider mb-1">Jam Mulai</label>
                                    <input type="text" id="start_hour" required class="w-full bg-zinc-700 border border-zinc-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:border-sky-500 focus:ring-1 focus:ring-sky-500" placeholder="Jam">
                                </div>
                            </div>
                            <input type="hidden" id="start_time" name="start_time">
                        </div>

                        <div class="mb-4">
                            <label for="color" class="block text-sm font-medium text-gray-300 mb-1">Warna Label</label>
                            <div class="flex items-center gap-3 h-[42px] mt-4">
                                <input type="color" id="color" name="color" value="#0284c7" class="w-12 h-10 p-1 bg-zinc-700 border border-zinc-600 rounded-lg cursor-pointer">
                                <span class="text-xs text-gray-400">Pilih warna penanda agenda di kalender</span>
                            </div>
                        </div>

                        <div class="md:col-span-2 mt-2">
                            <label class="block text-sm font-medium text-gray-300 mb-2"><i class="fas fa-users text-sky-400 mr-1.5"></i>Undang Karyawan</label>
                            <div id="guest-list-container" class="max-h-48 overflow-y-auto rounded-lg border border-zinc-600 bg-zinc-900/50 p-4 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-x-4 gap-y-3 custom-scrollbar">
                                <p class="text-zinc-500 sm:col-span-2 md:col-span-3">Memuat karyawan...</p>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            
            <div class="p-6 border-t border-zinc-700 bg-zinc-800 rounded-b-xl flex justify-end space-x-3">
                <button type="button" class="close-modal px-4 py-2 bg-zinc-600 text-white rounded-lg hover:bg-zinc-500 transition-colors">Batal</button>
                <button type="submit" form="agenda-form" id="save-agenda-btn" class="px-4 py-2 bg-sky-600 text-white rounded-lg hover:bg-sky-700 shadow-md transition-colors font-semibold flex items-center">
                    <i class="fas fa-save mr-2"></i> Simpan Agenda
                </button>
            </div>
        </div>
    </div>

    {{-- MODAL DETAIL AGENDA --}}
    <div id="agenda-detail-modal" class="fixed inset-0 bg-black/60 backdrop-blur-sm flex justify-center items-center hidden z-50 transition-opacity duration-300 opacity-0">
        <div class="bg-zinc-800 rounded-xl shadow-2xl w-full max-w-2xl transform scale-95 transition-transform duration-300 border border-zinc-700 mx-4" id="agenda-detail-content">
            <!-- Diisi secara dinamis oleh JavaScript -->
        </div>
    </div>

    @push('scripts')
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js'></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/id.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const calendarEl = document.getElementById('admin-calendar');
        const detailModal = document.getElementById('agenda-detail-modal');
        const detailContent = document.getElementById('agenda-detail-content');
        const agendaModal = document.getElementById('agenda-modal');
        const addAgendaBtn = document.getElementById('add-agenda-btn');
        const agendaForm = document.getElementById('agenda-form');
        const agendaDate = flatpickr("#agenda_date", { dateFormat: "Y-m-d", altInput: true, altFormat: "d F Y", locale: "id" });
        const startHour = flatpickr("#start_hour", { enableTime: true, noCalendar: true, dateFormat: "H:i", time_24hr: true });
        
        function toggleModal(modal, show) {
            if (show) {
                modal.classList.remove('hidden');
                setTimeout(() => {
                    modal.classList.remove('opacity-0');
                    modal.querySelector('div').classList.remove('scale-95');
                }, 10);
            } else {
                modal.classList.add('opacity-0');
                modal.querySelector('div').classList.add('scale-95');
                setTimeout(() => {
                    modal.classList.add('hidden');
                }, 300);
            }
        }

        const calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: { left: 'prev', center: 'title', right: 'next' },
            locale: 'id',
            height: 'auto',
            aspectRatio: 1.25,
            dayMaxEvents: 2,
            events: "{{ route('admin.agenda.getEvents') }}",
            eventClick: (info) => {
                info.jsEvent.preventDefault();
                showAgendaDetails(info.event);
            }
        });
        calendar.render();

        function formatFullDate(date) { return date.toLocaleString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }); }
        function formatTime(date) { return date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', hour12: false }); }
        
        function showAgendaDetails(event) {
            const props = event.extendedProps;
            const startTime = formatTime(event.start);
            const endTime = event.end ? formatTime(event.end) : '';
            const csrfToken = document.querySelector('form#agenda-form input[name="_token"]').value;
            
            let guestsHTML = '<p class="text-zinc-500 italic text-xs">Tidak ada tamu undangan.</p>';
            if (props.guests && props.guests.length > 0) {
                guestsHTML = `<div class="flex flex-wrap gap-1.5">${props.guests.map(g => `<span class="bg-zinc-700 text-zinc-300 text-xs px-2.5 py-1 rounded border border-zinc-600">${g}</span>`).join('')}</div>`;
            }
            
            const editButton = `<button type="button" id="edit-agenda-btn" class="px-4 py-2 bg-sky-600 text-white rounded-lg hover:bg-sky-700 transition-colors font-semibold flex items-center"><i class="fas fa-edit mr-2"></i> Edit</button>`;
            const deleteUrl = "{{ route('admin.agenda.destroy', ['agenda' => ':id']) }}".replace(':id', event.id);
            const deleteForm = `<form action="${deleteUrl}" method="POST" onsubmit="confirmSubmit(event, 'Anda yakin ingin menghapus agenda ini?')" class="inline-block"><input type="hidden" name="_token" value="${csrfToken}"><input type="hidden" name="_method" value="DELETE"><button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors font-semibold flex items-center"><i class="fas fa-trash-alt mr-2"></i> Hapus</button></form>`;
            
            const contentHTML = `
                <div class="flex justify-between items-center p-6 border-b border-zinc-700">
                    <h3 class="text-xl font-bold text-white flex items-center">
                        <i class="fas fa-calendar-check text-sky-400 mr-3 text-2xl"></i> Detail Lengkap Agenda
                    </h3>
                    <button type="button" class="close-modal text-gray-400 hover:text-white text-2xl leading-none">&times;</button>
                </div>
                
                <div class="p-6 overflow-y-auto custom-scrollbar max-h-[70vh] space-y-6">
                    <div class="flex items-center">
                        <span class="inline-block w-4 h-4 rounded-full mr-3 border border-zinc-600 flex-shrink-0" style="background-color: ${event.backgroundColor || '#0284c7'}"></span>
                        <h4 class="text-xl font-bold text-white">${props.fullTitle}</h4>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-zinc-900/40 p-4 rounded-lg border border-zinc-700/50">
                        <div class="flex items-start gap-3">
                            <i class="fas fa-calendar-alt text-sky-400 mt-1 text-lg"></i>
                            <div>
                                <p class="text-[10px] text-gray-400 uppercase tracking-wider">Tanggal & Waktu</p>
                                <p class="font-semibold text-white text-sm">${formatFullDate(event.start)}</p>
                                <p class="text-gray-300 text-xs mt-0.5">${startTime}${endTime ? ' - ' + endTime : ''} WIB</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <i class="fas fa-map-marker-alt text-emerald-400 mt-1 text-lg"></i>
                            <div>
                                <p class="text-[10px] text-gray-400 uppercase tracking-wider">Lokasi Acara</p>
                                <p class="font-semibold text-white text-sm">${props.location || '-'}</p>
                            </div>
                        </div>
                    </div>

                    ${props.description ? `
                        <div>
                            <h5 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2"><i class="fas fa-align-left text-sky-400 mr-1.5"></i>Deskripsi Agenda</h5>
                            <div class="text-zinc-300 bg-zinc-900/60 p-4 rounded-lg border border-zinc-700 whitespace-pre-wrap text-sm leading-relaxed">${props.description}</div>
                        </div>
                    ` : ''}

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h5 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2"><i class="fas fa-user-tie text-purple-400 mr-1.5"></i>Penyelenggara</h5>
                            <p class="text-white text-sm font-semibold">${props.organizer}</p>
                        </div>
                        <div>
                            <h5 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2"><i class="fas fa-users text-amber-400 mr-1.5"></i>Tamu Undangan (${props.guests ? props.guests.length : 0})</h5>
                            ${guestsHTML}
                        </div>
                    </div>
                </div>

                <div class="p-6 border-t border-zinc-700 bg-zinc-800 rounded-b-xl flex justify-end gap-3">
                    <button type="button" class="close-modal px-4 py-2 bg-zinc-600 text-white rounded-lg hover:bg-zinc-500 transition-colors">Tutup</button>
                    ${editButton}
                    ${deleteForm}
                </div>
            `;
            
            detailContent.innerHTML = contentHTML;
            toggleModal(detailModal, true);
            
            detailContent.querySelectorAll('.close-modal').forEach(btn => {
                btn.addEventListener('click', closeDetailModal);
            });
            document.getElementById('edit-agenda-btn').addEventListener('click', () => openModalForEdit(event));
        }

        function closeDetailModal() { toggleModal(detailModal, false); }
        
        function openModalForCreate() {
            agendaForm.querySelector('input[name="_method"]')?.remove();
            agendaForm.reset();
            agendaForm.setAttribute('action', "{{ route('admin.agenda.store') }}");
            agendaModal.querySelector('#modal-title').textContent = 'Buat Agenda Baru';
            agendaModal.querySelector('#save-agenda-btn').innerHTML = '<i class="fas fa-save mr-2"></i> Simpan Agenda';
            agendaDate.setDate(new Date());
            startHour.clear();
            document.querySelectorAll('input[name="guests[]"]').forEach(cb => cb.checked = false);
            toggleModal(agendaModal, true);
        }

        function openModalForEdit(event) {
            closeDetailModal();
            agendaForm.querySelector('input[name="_method"]')?.remove();
            agendaForm.reset();
            const updateUrl = "{{ route('admin.agenda.update', ['agenda' => ':id']) }}".replace(':id', event.id);
            agendaForm.setAttribute('action', updateUrl);
            const methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'PUT';
            agendaForm.appendChild(methodInput);
            agendaModal.querySelector('#modal-title').textContent = 'Edit Agenda';
            agendaModal.querySelector('#save-agenda-btn').innerHTML = '<i class="fas fa-save mr-2"></i> Update Agenda';
            agendaForm.querySelector('#title').value = event.extendedProps.fullTitle;
            agendaForm.querySelector('#description').value = event.extendedProps.description || '';
            agendaForm.querySelector('#location').value = event.extendedProps.location || '';
            agendaForm.querySelector('#color').value = event.backgroundColor || '#0284c7';
            agendaDate.setDate(event.start, true, "Y-m-d");
            startHour.setDate(event.start, true, "H:i");
            document.querySelectorAll('input[name="guests[]"]').forEach(cb => {
                cb.checked = event.extendedProps.guest_ids.includes(parseInt(cb.value));
            });
            toggleModal(agendaModal, true);
        }

        addAgendaBtn.addEventListener('click', openModalForCreate);
        
        document.querySelectorAll('.close-modal').forEach(btn => {
            btn.addEventListener('click', () => {
                const modal = btn.closest('.fixed.inset-0');
                if (modal) toggleModal(modal, false);
            });
        });
        
        window.addEventListener('click', (e) => {
            if (e.target.classList.contains('fixed') && e.target.classList.contains('inset-0')) {
                toggleModal(e.target, false);
            }
        });

        // Event listener untuk tombol aksi di tabel
        document.querySelectorAll('.open-detail-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const event = calendar.getEventById(this.dataset.agendaId);
                if (event) {
                    showAgendaDetails(event);
                } else {
                    alert('Mohon tunggu sebentar, kalender sedang memuat data...');
                }
            });
        });
        
        document.querySelectorAll('.open-edit-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const event = calendar.getEventById(this.dataset.agendaId);
                if (event) {
                    openModalForEdit(event);
                } else {
                    alert('Mohon tunggu sebentar, kalender sedang memuat data...');
                }
            });
        });

        agendaForm.addEventListener('submit', function() {
            const dateValue = agendaDate.input.value;
            const timeValue = startHour.input.value;
            if (dateValue && timeValue) {
                this.querySelector('#start_time').value = `${dateValue} ${timeValue}`;
            }
        });

        const guestContainer = document.getElementById('guest-list-container');
        fetch("{{ route('admin.agenda.getAllUsers') }}")
            .then(res => res.json())
            .then(users => {
                guestContainer.innerHTML = users.length > 0
                    ? users.map(user => `<div class="flex items-center"><input id="guest-${user.id}" name="guests[]" value="${user.id}" type="checkbox" class="h-4 w-4 rounded border-zinc-500 bg-zinc-700 text-sky-600 focus:ring-sky-500"><label for="guest-${user.id}" class="ml-3 block text-sm font-medium text-zinc-300">${user.name}</label></div>`).join('')
                    : '<p class="text-zinc-500 text-sm">Tidak ada karyawan.</p>';
            });
    });
    </script>
    @endpush
</x-layout-admin>
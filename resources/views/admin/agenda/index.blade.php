<x-layout-admin>
    <x-slot:title>{{ $title }}</x-slot:title>

    @push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        /* Styling untuk FullCalendar */
        #admin-calendar-wrapper .fc { border: none !important; background: #27272a; border-radius: 0.75rem; padding: 1rem; color: #d1d5db; }
        #admin-calendar-wrapper .fc .fc-toolbar-title { font-size: 1.3rem; font-weight: 700; color: #ffffff; }
        #admin-calendar-wrapper .fc .fc-button { background: transparent !important; border: none !important; box-shadow: none !important; color: #9ca3af; transition: all 0.2s; }
        #admin-calendar-wrapper .fc .fc-button:hover { color: #ffffff; background: #374151 !important; }
        #admin-calendar-wrapper .fc .fc-col-header-cell-cushion { color: #9ca3af; font-weight: 600; }
        #admin-calendar-wrapper .fc .fc-daygrid-day-number { color: #d1d5db; }
        #admin-calendar-wrapper .fc .fc-day-other .fc-daygrid-day-number { color: #4b5563; }
        
        #admin-calendar-wrapper .fc .fc-day-today .fc-daygrid-day-number {
            font-weight: 700;
            color: #ffffff;
            background: #f59e0b;
            border-radius: 9999px;
            padding: 2px 6px;
        }
        
        /* [PERUBAHAN FINAL] Tampilan Jalan Tengah (Kotak Rapi + Teks Terpotong) */
        .fc-daygrid-event {
            padding: 2px 6px !important;      /* Mengembalikan padding untuk teks */
            margin: 2px 4px !important;       /* Mengatur jarak antar agenda */
            border-radius: 5px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            cursor: pointer;
            transition: all 0.2s ease;
            
            /* Trik untuk memotong teks dengan "..." */
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .fc-daygrid-event:hover {
            background-color: rgba(255, 255, 255, 0.15) !important;
            transform: scale(1.05);
        }
        
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
        .pagination li.active span { background-color: #f59e0b; color: #ffffff; font-weight: bold; }
        .pagination li.disabled span { background-color: #27272a; color: #71717a; cursor: not-allowed; }
    </style>
    @endpush

    @if (session('success'))
        <div class="mb-6 bg-emerald-500/10 text-emerald-300 text-sm p-4 rounded-lg">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="mb-6 bg-red-500/10 text-red-300 p-4 text-sm rounded-lg">
            <p class="font-bold">Terjadi Kesalahan</p>
            <ul class="mt-1 list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="space-y-6">
        {{-- BAGIAN ATAS: KALENDER & TOMBOL --}}
        <div class="bg-zinc-800 rounded-xl shadow-lg border border-zinc-700 p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold text-white">Kalender Agenda</h2>
                <button id="add-agenda-btn" class="bg-amber-500 hover:bg-amber-600 text-white font-bold py-2 px-4 rounded-lg flex items-center gap-2">
                    <i class="fas fa-plus"></i> Buat Agenda Baru
                </button>
            </div>
            <div id="admin-calendar-wrapper">
                <div id="admin-calendar"></div>
            </div>
        </div>

        {{-- BAGIAN BAWAH: DAFTAR SEMUA AGENDA --}}
        <div class="bg-zinc-800 rounded-xl shadow-lg border border-zinc-700 p-6">
            <h2 class="text-xl font-bold text-white mb-4">Semua Agenda</h2>

            <form method="GET" action="{{ route('admin.agenda.index') }}" class="mb-4">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-end">
                    <div>
                        <label for="start_date" class="block text-sm font-medium text-zinc-400 mb-1">Dari Tanggal</label>
                        <input type="date" name="start_date" id="start_date" value="{{ request('start_date') }}" class="w-full bg-zinc-700 border-zinc-600 rounded-lg text-white text-sm px-3 py-2">
                    </div>
                    <div>
                        <label for="end_date" class="block text-sm font-medium text-zinc-400 mb-1">Sampai Tanggal</label>
                        <input type="date" name="end_date" id="end_date" value="{{ request('end_date') }}" class="w-full bg-zinc-700 border-zinc-600 rounded-lg text-white text-sm px-3 py-2">
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="w-full bg-amber-500 hover:bg-amber-600 text-white font-bold py-2 px-4 rounded-lg">Filter</button>
                        <a href="{{ route('admin.agenda.index') }}" class="w-full bg-zinc-600 hover:bg-zinc-500 text-white font-bold py-2 px-4 rounded-lg text-center">Reset</a>
                    </div>
                </div>
            </form>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 border-t border-zinc-700 pt-4">
                @forelse ($allAgendas as $agenda)
                    <div class="agenda-item-clickable bg-zinc-700/50 p-4 rounded-lg border-l-4 cursor-pointer hover:bg-zinc-700 transition-colors flex flex-col justify-between"
                         style="border-color: {{ $agenda->color ?? '#F59E0B' }};"
                         data-agenda-id="{{ $agenda->id }}">
                        <div>
                            <p class="font-bold text-white">{{ $agenda->title }}</p>
                            <p class="text-sm text-zinc-400 mt-1">{{ \Carbon\Carbon::parse($agenda->start_time)->isoFormat('dddd, D MMM YYYY [pukul] HH:mm') }}</p>
                        </div>
                        <p class="text-xs text-zinc-500 mt-3 pt-2 border-t border-zinc-600/50">Dibuat oleh: {{ $agenda->creator->name ?? 'N/A' }}</p>
                    </div>
                @empty
                    <div class="md:col-span-2 lg:col-span-3 h-40 flex items-center justify-center text-center text-zinc-500">
                        <p>Tidak ada agenda yang cocok dengan filter Anda.</p>
                    </div>
                @endempty
            </div>
            
            <div class="mt-6">
                {{ $allAgendas->links() }}
            </div>
        </div>
    </div>
    
    <div id="agenda-modal" class="fixed inset-0 bg-black bg-opacity-70 z-50 hidden flex items-center justify-center p-4">
        <div class="bg-zinc-800 border border-zinc-700 rounded-2xl shadow-2xl w-full max-w-3xl mx-4 p-6 flex flex-col max-h-[90vh]">
            <div class="flex-shrink-0 flex justify-between items-center border-b border-zinc-700 pb-4 mb-6">
                <h4 id="modal-title" class="text-xl font-bold text-white">Buat Agenda Baru</h4>
                <button class="close-modal-btn text-zinc-500 hover:text-white"><i class="fas fa-times text-2xl"></i></button>
            </div>
            <div class="flex-grow overflow-y-auto -mr-3 pr-3">
                <form id="agenda-form" method="POST">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-5 text-zinc-300"> 
                        <div class="space-y-5">
                            <div><label for="title" class="block text-sm font-medium mb-2">Judul Agenda <span class="text-red-500">*</span></label><input type="text" id="title" name="title" required class="w-full bg-zinc-700 border-zinc-600 rounded-lg px-3 py-2" placeholder="Contoh: Rapat Evaluasi Bulanan"></div>
                            <div><label for="description" class="block text-sm font-medium mb-2">Deskripsi</label><textarea id="description" name="description" rows="5" class="w-full bg-zinc-700 border-zinc-600 rounded-lg px-3 py-2" placeholder="Jelaskan detail agenda di sini..."></textarea></div>
                        </div>
                        <div class="space-y-5">
                            <div>
                                <label class="block text-sm font-medium mb-2">Waktu Acara <span class="text-red-500">*</span></label>
                                <div class="grid grid-cols-2 gap-4">
                                   <div><label for="agenda_date" class="block text-xs font-medium text-zinc-400 mb-1.5">Tanggal</label><input type="text" id="agenda_date" required class="w-full bg-zinc-700 border-zinc-600 rounded-lg px-3 py-2" placeholder="Pilih Tanggal"></div>
                                   <div><label for="start_hour" class="block text-xs font-medium text-zinc-400 mb-1.5">Jam Mulai</label><input type="text" id="start_hour" required class="w-full bg-zinc-700 border-zinc-600 rounded-lg px-3 py-2" placeholder="Jam"></div>
                                </div>
                                <input type="hidden" id="start_time" name="start_time">
                            </div>
                            <div><label for="location" class="block text-sm font-medium mb-2">Lokasi</label><input type="text" id="location" name="location" class="w-full bg-zinc-700 border-zinc-600 rounded-lg px-3 py-2" placeholder="Contoh: Ruang Meeting Lt. 2"></div>
                             <div><label for="color" class="block text-sm font-medium mb-2">Warna Label</label><input type="color" id="color" name="color" value="#F59E0B" class="w-full h-10 px-1 py-1 bg-zinc-700 border-zinc-600 rounded-lg cursor-pointer"></div>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium mb-2">Undang Karyawan</label>
                            <div id="guest-list-container" class="max-h-48 overflow-y-auto rounded-lg border border-zinc-600 bg-zinc-900 p-4 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-x-4 gap-y-3">
                                <p class="text-zinc-500 sm:col-span-2 md:col-span-3">Memuat karyawan...</p>
                            </div>
                        </div>
                    </div>
                    <div class="flex-shrink-0 flex justify-end mt-8 pt-5 border-t border-zinc-700">
                        <button type="button" class="close-modal-btn bg-zinc-600 hover:bg-zinc-500 text-white font-bold py-2 px-4 rounded-lg mr-3">Batal</button>
                        <button type="submit" id="save-agenda-btn" class="bg-amber-500 hover:bg-amber-600 text-white font-bold py-2 px-4 rounded-lg">Simpan Agenda</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div id="agenda-detail-modal" class="fixed inset-0 bg-black bg-opacity-70 z-50 hidden flex items-center justify-center p-4">
        <div class="bg-zinc-800 border border-zinc-700 rounded-2xl shadow-2xl w-full max-w-2xl mx-4 p-6" id="agenda-detail-content"></div>
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
        const calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: { left: 'prev', center: 'title', right: 'next' },
            locale: 'id',
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
            let guestsHTML = '<p class="text-zinc-400 text-sm">Tidak ada tamu.</p>';
            if (props.guests && props.guests.length > 0) {
                guestsHTML = `<div class="flex flex-wrap gap-2">${props.guests.map(g => `<span class="bg-zinc-600 text-zinc-200 text-xs px-2 py-1 rounded-full">${g}</span>`).join('')}</div>`;
            }
            const editButton = `<button type="button" id="edit-agenda-btn" class="bg-amber-500 hover:bg-amber-600 text-white font-bold py-2 px-4 rounded-lg">Edit</button>`;
            const deleteUrl = "{{ route('admin.agenda.destroy', ['agenda' => ':id']) }}".replace(':id', event.id);
            const deleteForm = `<form action="${deleteUrl}" method="POST" onsubmit="return confirm('Anda yakin ingin menghapus agenda ini?')" class="ml-2"><input type="hidden" name="_token" value="${csrfToken}"><input type="hidden" name="_method" value="DELETE"><button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-lg">Hapus</button></form>`;
            const contentHTML = `<div class="flex justify-between items-start pb-3 mb-4 border-b border-zinc-700"><div><p class="text-xs font-semibold uppercase" style="color: ${event.backgroundColor || '#F59E0B'}">Detail Agenda</p><h4 class="text-2xl font-bold text-white mt-1">${props.fullTitle}</h4></div><button class="close-detail-modal-btn text-zinc-500 hover:text-white"><i class="fas fa-times text-2xl"></i></button></div><div class="max-h-[60vh] overflow-y-auto pr-3 -mr-3 space-y-5 text-sm"><div class="grid grid-cols-1 sm:grid-cols-2 gap-5"><div class="flex items-start gap-3"><i class="fas fa-calendar-alt fa-fw text-zinc-500 text-lg mt-1"></i><div><p class="text-xs text-zinc-400">Waktu & Tanggal</p><p class="font-semibold text-zinc-200">${formatFullDate(event.start)}</p><p class="text-zinc-300">${startTime}${endTime ? ' - ' + endTime : ''} WIB</p></div></div>${props.location ? `<div class="flex items-start gap-3"><i class="fas fa-map-marker-alt fa-fw text-zinc-500 text-lg mt-1"></i><div><p class="text-xs text-zinc-400">Lokasi</p><p class="font-semibold text-zinc-200">${props.location}</p></div></div>` : ''}</div>${props.description ? `<div><h5 class="font-bold text-zinc-200 mb-2 flex items-center gap-2"><i class="fas fa-align-left fa-fw text-zinc-500"></i>Deskripsi</h5><div class="text-zinc-300 bg-zinc-900 p-4 rounded-lg border border-zinc-700 whitespace-pre-wrap">${props.description}</div></div>` : ''}<div><h5 class="font-bold text-zinc-200 mb-2 flex items-center gap-2"><i class="fas fa-user-tie fa-fw text-zinc-500"></i>Penyelenggara</h5><p class="text-zinc-300">${props.organizer}</p></div><div><h5 class="font-bold text-zinc-200 mb-3 flex items-center gap-2"><i class="fas fa-users fa-fw text-zinc-500"></i>Tamu Undangan</h5>${guestsHTML}</div></div><div class="mt-6 pt-4 border-t border-zinc-700 flex items-center"><button class="close-detail-modal-btn bg-zinc-600 hover:bg-zinc-500 text-white font-bold py-2 px-4 rounded-lg mr-auto">Tutup</button>${editButton}${deleteForm}</div>`;
            detailContent.innerHTML = contentHTML;
            detailModal.classList.remove('hidden');
            document.querySelectorAll('.close-detail-modal-btn').forEach(btn => btn.addEventListener('click', closeDetailModal));
            document.getElementById('edit-agenda-btn').addEventListener('click', () => openModalForEdit(event));
        }
        function closeDetailModal() { detailModal.classList.add('hidden'); }
        function openModalForCreate() {
            agendaForm.querySelector('input[name="_method"]')?.remove();
            agendaForm.reset();
            agendaForm.setAttribute('action', "{{ route('admin.agenda.store') }}");
            agendaModal.querySelector('#modal-title').textContent = 'Buat Agenda Baru';
            agendaModal.querySelector('#save-agenda-btn').textContent = 'Simpan Agenda';
            agendaDate.setDate(new Date());
            startHour.clear();
            document.querySelectorAll('input[name="guests[]"]').forEach(cb => cb.checked = false);
            agendaModal.classList.remove('hidden');
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
            agendaModal.querySelector('#save-agenda-btn').textContent = 'Update Agenda';
            agendaForm.querySelector('#title').value = event.extendedProps.fullTitle;
            agendaForm.querySelector('#description').value = event.extendedProps.description || '';
            agendaForm.querySelector('#location').value = event.extendedProps.location || '';
            agendaForm.querySelector('#color').value = event.backgroundColor || '#F59E0B';
            agendaDate.setDate(event.start, true, "Y-m-d");
            startHour.setDate(event.start, true, "H:i");
            document.querySelectorAll('input[name="guests[]"]').forEach(cb => {
                cb.checked = event.extendedProps.guest_ids.includes(parseInt(cb.value));
            });
            agendaModal.classList.remove('hidden');
        }
        addAgendaBtn.addEventListener('click', openModalForCreate);
        document.querySelectorAll('.close-modal-btn').forEach(btn => btn.addEventListener('click', () => btn.closest('.fixed').classList.add('hidden')));
        document.querySelectorAll('.agenda-item-clickable').forEach(item => {
            item.addEventListener('click', function() {
                const event = calendar.getEventById(this.dataset.agendaId);
                if (event) showAgendaDetails(event);
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
                    ? users.map(user => `<div class="flex items-center"><input id="guest-${user.id}" name="guests[]" value="${user.id}" type="checkbox" class="h-4 w-4 rounded border-zinc-500 bg-zinc-700 text-amber-500 focus:ring-amber-500"><label for="guest-${user.id}" class="ml-3 block text-sm font-medium text-zinc-300">${user.name}</label></div>`).join('')
                    : '<p class="text-zinc-500 text-sm">Tidak ada karyawan.</p>';
            });
    });
    </script>
    @endpush
</x-layout-admin>
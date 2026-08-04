<x-layout-admin>
    <x-slot:title>Kelola Indikator KPI</x-slot:title>

    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-white">Manajemen Indikator KPI</h1>
        <button id="open-add-modal-btn" 
            class="bg-sky-600 hover:bg-sky-700 text-white font-bold py-2 px-4 rounded-lg shadow-md flex items-center transition-transform duration-200 hover:scale-105">
            <i class="fas fa-plus mr-2"></i> Tambah Indikator
        </button>
    </div>

    @if ($errors->any())
        <div class="mb-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-md">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="space-y-6">
        @forelse ($groupedIndicators as $type => $categories)
            @foreach($categories as $category => $indicatorList)
            <div class="bg-zinc-800 rounded-lg shadow-lg overflow-hidden border border-zinc-700">
                <div class="bg-zinc-700 px-6 py-4 flex justify-between items-center cursor-pointer toggle-group" data-target="group-{{ Str::slug($type . '-' . $category) }}">
                    <h2 class="text-lg font-bold text-sky-400 flex items-center uppercase tracking-wider">
                        <i class="fas fa-layer-group mr-3"></i> Tipe: {{ $type }} | Kategori: {{ Str::title(str_replace('_', ' ', $category)) }}
                    </h2>
                    <div class="flex items-center space-x-4">
                        <span class="bg-sky-600 text-white text-xs px-3 py-1 rounded-full font-semibold">
                            {{ $indicatorList->count() }} Indikator
                        </span>
                        <i class="fas fa-chevron-down text-gray-400 transition-transform duration-300 icon-chevron"></i>
                    </div>
                </div>

                <div id="group-{{ Str::slug($type . '-' . $category) }}" class="overflow-x-auto transition-all duration-300">
                    <table class="min-w-full divide-y divide-zinc-700">
                        <thead class="bg-zinc-800/50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Nama & Definisi</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Target</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Bobot (%)</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-400 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-zinc-800 divide-y divide-zinc-700">
                            @foreach ($indicatorList as $indicator)
                                <tr class="hover:bg-zinc-700/50 transition-colors">
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-bold text-white">{{ $indicator->name }}</div>
                                        <div class="text-xs text-gray-400 mt-1">{{ $indicator->definition }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                                        {{ $indicator->target ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-emerald-400">
                                        {{ (float)$indicator->weight_percentage }}%
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <button class="text-sky-400 hover:text-sky-300 mr-3 edit-btn" 
                                            data-id="{{ $indicator->id }}"
                                            data-category="{{ $indicator->category }}"
                                            data-name="{{ $indicator->name }}"
                                            data-definition="{{ $indicator->definition }}"
                                            data-target="{{ $indicator->target }}"
                                            data-weight="{{ (float)$indicator->weight_percentage }}"
                                            data-type="{{ $indicator->type }}">
                                            <i class="fas fa-edit"></i>
                                        </button>

                                        <form action="{{ route('admin.kpi.indicators.destroy', $indicator->id) }}" method="POST" class="inline-block" onsubmit="confirmSubmit(event, 'Hapus indikator ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-400 hover:text-red-300">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endforeach
        @empty
            <div class="bg-zinc-800 p-8 rounded-lg text-center border border-zinc-700">
                <i class="fas fa-folder-open text-4xl text-gray-500 mb-3"></i>
                <p class="text-gray-400 text-lg">Belum ada indikator KPI yang ditambahkan.</p>
            </div>
        @endforelse
    </div>

    {{-- MODAL TAMBAH/EDIT --}}
    <div id="form-modal" class="fixed inset-0 bg-black/60 backdrop-blur-sm flex justify-center items-center hidden z-50 transition-opacity duration-300 opacity-0">
        <div class="bg-zinc-800 rounded-xl shadow-2xl w-full max-w-3xl transform scale-95 transition-transform duration-300 flex flex-col border border-zinc-700">
            <div class="flex justify-between items-center p-6 border-b border-zinc-700">
                <h3 id="modal-title" class="text-xl font-bold text-white flex items-center">
                    Tambah Indikator
                </h3>
                <button type="button" class="close-modal text-gray-400 hover:text-white text-2xl leading-none">&times;</button>
            </div>
            
            <div class="p-6 max-h-[70vh] overflow-y-auto">
                <form id="indicator-form" action="{{ route('admin.kpi.indicators.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="_method" id="form-method" value="POST">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1">Tipe (Divisi) <span class="text-red-500">*</span></label>
                            <select name="type" id="type" required class="w-full bg-zinc-700 border border-zinc-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-1 focus:ring-sky-500">
                                <option value="marketing">Marketing (Frontliner)</option>
                                <option value="umum">Umum / Back Office (Finance, HRD, Gudang, dll)</option>
                            </select>
                            <p class="text-xs text-gray-400 mt-1">Menentukan form mana yang muncul untuk karyawan di divisi ini.</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1">Kategori <span class="text-red-500">*</span></label>
                            <select name="category" id="category" required class="w-full bg-zinc-700 border border-zinc-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-1 focus:ring-sky-500">
                                <option value="kinerja">Kinerja</option>
                                <option value="perilaku">Perilaku</option>
                                <option value="kehadiran">Kehadiran</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-300 mb-1">Nama Indikator <span class="text-red-500">*</span></label>
                        <input type="text" name="name" id="name" required class="w-full bg-zinc-700 border border-zinc-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-1 focus:ring-sky-500">
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-300 mb-1">Definisi</label>
                        <textarea name="definition" id="definition" rows="3" class="w-full bg-zinc-700 border border-zinc-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-1 focus:ring-sky-500"></textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1">Target</label>
                            <input type="text" name="target" id="target" class="w-full bg-zinc-700 border border-zinc-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-1 focus:ring-sky-500" placeholder="Misal: 100 JT, 85%">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1">Bobot (%) <span class="text-red-500">*</span></label>
                            <input type="number" step="0.01" name="weight_percentage" id="weight_percentage" required class="w-full bg-zinc-700 border border-zinc-600 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-1 focus:ring-sky-500">
                        </div>
                    </div>
                </form>
            </div>
            <div class="p-6 border-t border-zinc-700 bg-zinc-800 rounded-b-xl flex justify-end space-x-3">
                <button type="button" class="close-modal px-4 py-2 bg-zinc-600 text-white rounded-lg hover:bg-zinc-500">Batal</button>
                <button type="submit" form="indicator-form" class="px-4 py-2 bg-sky-600 text-white rounded-lg hover:bg-sky-700 shadow-md font-semibold flex items-center">
                    <i class="fas fa-save mr-2"></i> Simpan
                </button>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            
            document.querySelectorAll('.toggle-group').forEach(header => {
                header.addEventListener('click', () => {
                    const targetId = header.getAttribute('data-target');
                    const targetDiv = document.getElementById(targetId);
                    const icon = header.querySelector('.icon-chevron');
                    
                    if (targetDiv.classList.contains('hidden')) {
                        targetDiv.classList.remove('hidden');
                        icon.style.transform = 'rotate(0deg)';
                    } else {
                        targetDiv.classList.add('hidden');
                        icon.style.transform = 'rotate(-90deg)';
                    }
                });
            });

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

            const formModal = document.getElementById('form-modal');
            const indicatorForm = document.getElementById('indicator-form');
            
            document.getElementById('open-add-modal-btn')?.addEventListener('click', () => {
                document.getElementById('modal-title').innerText = 'Tambah Indikator';
                indicatorForm.reset();
                indicatorForm.action = "{{ route('admin.kpi.indicators.store') }}";
                document.getElementById('form-method').value = 'POST';
                toggleModal(formModal, true);
            });

            document.querySelectorAll('.edit-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    document.getElementById('modal-title').innerText = 'Edit Indikator';
                    indicatorForm.action = `/admin/kpi/indicators/${btn.dataset.id}`;
                    document.getElementById('form-method').value = 'PUT';
                    
                    document.getElementById('type').value = btn.dataset.type;
                    document.getElementById('category').value = btn.dataset.category;
                    document.getElementById('name').value = btn.dataset.name;
                    document.getElementById('definition').value = btn.dataset.definition;
                    document.getElementById('target').value = btn.dataset.target;
                    document.getElementById('weight_percentage').value = btn.dataset.weight;

                    toggleModal(formModal, true);
                });
            });

            document.querySelectorAll('.close-modal').forEach(btn => {
                btn.addEventListener('click', () => {
                    toggleModal(formModal, false);
                });
            });
        });
    </script>
    @endpush
</x-layout-admin>

{{-- Modal edit baris Kartu Stock + toast + JS. Di-include di card-index (konteks popup) & card-show (halaman penuh). --}}
<div id="kartuEditModal" class="fixed inset-0 z-[10000] hidden items-center justify-center bg-slate-900/50 backdrop-blur-sm p-3 sm:p-6">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden flex flex-col">
        <div class="px-4 py-3 border-b border-slate-100 flex justify-between items-start gap-3 bg-slate-50 shrink-0">
            <div class="min-w-0">
                <h3 class="text-sm font-black text-slate-800 leading-tight">Edit Baris <span id="kartuEditId" class="text-slate-400"></span></h3>
                <p class="text-[11px] text-slate-500 font-medium">Sisa dihitung otomatis ulang. Qty/Tanggal berubah = reversal + mutasi koreksi.</p>
            </div>
            <button type="button" onclick="closeKartuEdit()" class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-red-50 text-slate-400 hover:text-red-500 transition-colors flex items-center justify-center shrink-0"><i class="fas fa-times"></i></button>
        </div>
        <form id="kartuEditForm" method="POST" class="p-4 grid grid-cols-2 gap-2">
            @csrf
            <div><label class="ui-label">Arah</label><input type="text" id="kartuEditArah" disabled class="ui-input !bg-slate-50 text-slate-500"></div>
            <div><label class="ui-label">Tanggal <span class="text-red-500">*</span></label><input type="date" lang="id-ID" name="tanggal" id="kartuEditTanggal" required max="{{ today()->toDateString() }}" class="ui-input !bg-white"></div>
            <div><label class="ui-label">Qty <span class="text-red-500">*</span></label><input type="number" name="qty" id="kartuEditQty" min="0.01" step="any" required class="ui-input !bg-white"></div>
            <div><label class="ui-label">Batch No./SN</label><input type="text" name="batch_no" id="kartuEditBatch" maxlength="100" class="ui-input !bg-white"></div>
            <div class="col-span-2"><label class="ui-label">Keterangan <span class="text-red-500">*</span></label><input type="text" name="keterangan" id="kartuEditKeterangan" required maxlength="1000" class="ui-input !bg-white"></div>
            <div class="col-span-2 flex justify-end gap-2 mt-1">
                <button type="button" onclick="closeKartuEdit()" class="h-[34px] px-4 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold">Batal</button>
                <button type="submit" class="h-[34px] px-4 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-sm">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<div id="kartuPageToast" class="hidden fixed top-5 left-1/2 -translate-x-1/2 z-[10001] px-4 py-3 rounded-xl text-xs font-bold shadow-xl"></div>

@push('scripts')
<script>
    function kartuPageToast(msg, ok = true) {
        const el = document.getElementById('kartuPageToast');
        if (!el) return;
        el.classList.remove('hidden');
        el.className = 'fixed top-5 left-1/2 -translate-x-1/2 z-[10001] px-4 py-3 rounded-xl text-xs font-bold shadow-xl ' + (ok
            ? 'bg-blue-600 text-white'
            : 'bg-red-600 text-white');
        el.textContent = msg;
        clearTimeout(el._t);
        el._t = setTimeout(() => el.classList.add('hidden'), 4000);
    }

    function openKartuEdit(btn) {
        const d = btn.dataset;
        document.getElementById('kartuEditId').textContent = d.id ? '#' + d.id : '';
        const form = document.getElementById('kartuEditForm');
        form.action = d.url;
        document.getElementById('kartuEditArah').value = d.arah || '-';
        document.getElementById('kartuEditTanggal').value = d.tanggal || '';
        document.getElementById('kartuEditQty').value = d.qty || '';
        document.getElementById('kartuEditBatch').value = d.batch || '';
        document.getElementById('kartuEditKeterangan').value = d.keterangan || '';
        const m = document.getElementById('kartuEditModal');
        m.classList.remove('hidden');
        m.classList.add('flex');
        document.body.style.overflow = 'hidden';
    }

    function closeKartuEdit() {
        const m = document.getElementById('kartuEditModal');
        m.classList.add('hidden');
        m.classList.remove('flex');
        // Jangan buka scroll body bila modal kartu masih terbuka (konteks popup).
        const kartu = document.getElementById('kartuModal');
        if (!kartu || kartu.classList.contains('hidden')) document.body.style.overflow = '';
    }

    function kartuAfterMutation(message) {
        // Konteks popup: refresh isi + toast di modal.
        if (typeof kartuCurrentUrl !== 'undefined' && kartuCurrentUrl && typeof loadKartuEmbed === 'function') {
            if (typeof kartuToast === 'function') kartuToast(message, true);
            else kartuPageToast(message, true);
            loadKartuEmbed(kartuCurrentUrl);
        } else {
            // Halaman penuh: toast lalu reload.
            kartuPageToast(message, true);
            setTimeout(() => location.reload(), 1200);
        }
    }

    function kartuCsrf() {
        return document.querySelector('meta[name="csrf-token"]')?.content || '';
    }

    // Klik tombol Edit / Hapus di baris tabel (delegasi: jalan di halaman penuh & popup).
    document.addEventListener('click', function (e) {
        const editBtn = e.target.closest('[data-kartu-edit]');
        if (editBtn) { e.preventDefault(); openKartuEdit(editBtn); return; }

        const delBtn = e.target.closest('[data-kartu-delete]');
        if (delBtn) {
            e.preventDefault();
            if (!confirm('Hapus baris ini? Stok dikembalikan via reversal, jejak tetap tercatat.')) return;
            fetch(delBtn.dataset.url, {
                method: 'DELETE',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'X-CSRF-TOKEN': kartuCsrf() },
            })
                .then(async (res) => {
                    const data = await res.json().catch(() => ({}));
                    if (!res.ok || data.success === false) throw new Error(data.message || 'Gagal menghapus baris.');
                    kartuAfterMutation(data.message || 'Baris dibatalkan.');
                })
                .catch((err) => {
                    if (typeof kartuToast === 'function' && typeof kartuCurrentUrl !== 'undefined' && kartuCurrentUrl) kartuToast(err.message, false);
                    else kartuPageToast(err.message, false);
                });
        }
    });

    // Tutup modal edit via backdrop & Esc.
    document.getElementById('kartuEditModal').addEventListener('click', function (e) {
        if (e.target === this) closeKartuEdit();
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && !document.getElementById('kartuEditModal').classList.contains('hidden')) closeKartuEdit();
    });

    // Submit form edit via fetch (POST + _method=PUT).
    document.getElementById('kartuEditForm').addEventListener('submit', async function (e) {
        e.preventDefault();
        const form = e.target;
        const fd = new FormData(form);
        fd.append('_method', 'PUT');
        const btn = form.querySelector('button[type="submit"]');
        if (btn) { btn.disabled = true; btn.textContent = 'Menyimpan...'; }
        try {
            const res = await fetch(form.action, {
                method: 'POST',
                body: fd,
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            });
            const data = await res.json().catch(() => ({}));
            if (!res.ok || data.success === false) {
                const msg = data.message || (data.errors ? Object.values(data.errors).flat().join(' ') : 'Gagal menyimpan.');
                throw new Error(msg);
            }
            closeKartuEdit();
            kartuAfterMutation(data.message || 'Perubahan disimpan.');
        } catch (err) {
            if (typeof kartuToast === 'function' && typeof kartuCurrentUrl !== 'undefined' && kartuCurrentUrl) kartuToast(err.message, false);
            else kartuPageToast(err.message, false);
        } finally {
            if (btn) { btn.disabled = false; btn.textContent = 'Simpan Perubahan'; }
        }
    });
</script>
@endpush

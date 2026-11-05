{{-- MOBILE ONLY: Riwayat Pengajuan Dana — gaya bahasa halaman sales. Web tidak tersentuh. --}}
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
@push('styles')
<style>
    .m-wrap { font-family: 'Outfit', sans-serif; }
    .m-mesh {
        position: fixed; inset: 0; z-index: 0; pointer-events: none;
        background-color: #ede9fe;
        background-image:
            radial-gradient(at 0% 0%, rgba(255,255,255,.45) 0px, transparent 50%),
            radial-gradient(at 100% 0%, rgba(99,102,241,.12) 0px, transparent 50%),
            radial-gradient(at 100% 100%, rgba(59,130,246,.12) 0px, transparent 50%);
    }
    .m-hero {
        border-radius: 1rem; padding: 1.1rem 1.25rem; color: #fff;
        box-shadow: 0 10px 25px -8px rgba(30,64,175,.5);
        display: flex; align-items: center; justify-content: space-between; gap: 1rem;
        position: relative; overflow: hidden;
        background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
    }
    .m-hero h2 { font-size: .82rem; font-weight: 900; letter-spacing: .06em; text-transform: uppercase; line-height: 1.35; }
    .m-hero p { font-size: .72rem; color: #dbeafe; font-weight: 500; margin-top: 2px; }
    .m-hero-ic {
        width: 2.75rem; height: 2.75rem; border-radius: .8rem; flex-shrink: 0;
        background: rgba(255,255,255,.2); display: flex; align-items: center; justify-content: center;
        font-size: 1rem; box-shadow: inset 0 1px 3px rgba(255,255,255,.25);
    }
    .m-tabs { display: flex; gap: .45rem; overflow-x: auto; padding-bottom: 2px; scrollbar-width: none; }
    .m-tabs::-webkit-scrollbar { display: none; }
    .m-tabs a {
        flex-shrink: 0; font-size: 11px; font-weight: 800;
        border-radius: 9999px; padding: 7px 14px; text-decoration: none;
        border: 1px solid #fff; background: rgba(255,255,255,.92); color: #475569;
        box-shadow: 0 2px 8px rgba(15,23,42,.05);
    }
    .m-tabs a.on { background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%); border-color: transparent; color: #fff; box-shadow: 0 4px 12px rgba(30,64,175,.35); }
    .m-filter { background: #fff; border: 1px solid #e2e8f0; border-radius: 1rem; padding: .7rem; box-shadow: 0 4px 16px rgba(15,23,42,.05); }
    .m-item {
        display: block; background: #fff; border: 1px solid #e2e8f0; border-top-width: 4px;
        border-radius: 1rem; box-shadow: 0 4px 16px rgba(15,23,42,.05);
        padding: .85rem 1rem; text-decoration: none;
    }
    .m-item:active { transform: scale(.99); background: #f8fafc; }
    .m-item-h { display: flex; justify-content: space-between; align-items: flex-start; gap: .6rem; }
    .m-item-t { font-size: 13px; font-weight: 800; color: #1e293b; line-height: 1.35; letter-spacing: -.01em; }
    .m-pill { font-size: 8px; font-weight: 900; text-transform: uppercase; letter-spacing: .06em; border-radius: 9999px; padding: 4px 9px; border: 1px solid; flex-shrink: 0; }
    .m-chips { display: flex; align-items: center; gap: 6px; margin-top: 7px; flex-wrap: wrap; }
    .m-tipe { display: inline-block; font-size: 8px; font-weight: 900; text-transform: uppercase; letter-spacing: .06em; border-radius: 9999px; padding: 3px 8px; background: #f0fdfa; color: #0f766e; border: 1px solid #ccfbf1; }
    .m-date { font-size: 10px; font-weight: 700; color: #94a3b8; }
    .m-stats { display: grid; grid-template-columns: 1fr 1fr; gap: .5rem; margin-top: .7rem; padding-top: .7rem; border-top: 1px solid #f1f5f9; text-align: left; }
    .m-stat-k { font-size: 8px; font-weight: 800; text-transform: uppercase; letter-spacing: .1em; color: #94a3b8; }
    .m-stat-v { font-size: 13px; font-weight: 900; color: #1e293b; margin-top: 1px; }
    .m-stat-v.mono { font-family: monospace; }
    .m-go { display: flex; align-items: center; justify-content: space-between; margin-top: .7rem; font-size: 11px; font-weight: 800; color: #1d4ed8; }
    .m-go i { color: #cbd5e1; font-size: .75rem; }
    .m-empty { text-align: center; color: #94a3b8; background: #fff; border: 1px solid #e2e8f0; border-radius: 1rem; padding: 2rem 1rem; }
</style>
@endpush

@php
    $mTipe = request('tipe', 'semua');
    $mTabParams = array_filter(['status' => request('status'), 'from' => request('from')]);
    $mShowUrl = fn($id) => request('from') === 'sales'
        ? route('pengajuan_dana.show', ['pengajuanDana' => $id, 'from' => 'sales'])
        : route('pengajuan_dana.show', $id);
    $mDot = fn($st) => match($st) {
        'selesai' => '#16a34a', 'ditolak' => '#dc2626', 'dibatalkan' => '#64748b',
        'diajukan' => '#ca8a04', 'diproses' => '#4f46e5', 'disetujui', 'proses_pembayaran' => '#0d9488',
        default => '#64748b',
    };
    $mPill = fn($st) => match($st) {
        'selesai' => 'background:#dcfce7;color:#15803d;border-color:#bbf7d0;',
        'ditolak' => 'background:#fee2e2;color:#b91c1c;border-color:#fecaca;',
        'dibatalkan' => 'background:#f1f5f9;color:#475569;border-color:#e2e8f0;',
        'diajukan' => 'background:#fef9c3;color:#a16207;border-color:#fde68a;',
        'diproses' => 'background:#e0e7ff;color:#4338ca;border-color:#c7d2fe;',
        'disetujui', 'proses_pembayaran' => 'background:#ccfbf1;color:#0f766e;border-color:#99f6e4;',
        default => 'background:#f1f5f9;color:#475569;border-color:#e2e8f0;',
    };
    $mLabel = function($p) {
        return match($p->status) {
            'diajukan' => 'Approver 1',
            'diproses' => ($p->approver_2_status === 'menunggu')
                ? 'Approver 2'
                : (($p->approver_3_status === 'menunggu')
                    ? 'Approver 3'
                    : 'Diproses'),
            'disetujui', 'proses_pembayaran' => 'Menunggu Pembayaran',
            'selesai' => 'Selesai',
            'ditolak' => 'Ditolak',
            'dibatalkan' => 'Dibatalkan',
            default => ucfirst($p->status),
        };
    };
@endphp

<div class="m-wrap flex flex-col flex-1 min-h-screen relative overflow-x-clip text-slate-800 pb-16">
    <div class="m-mesh"></div>

    <div class="relative z-10 w-full max-w-none mx-0 px-4 pt-4 flex-1 flex flex-col gap-3">
        <div class="w-full flex justify-start">
            <x-ui.back-button :href="request('from') === 'sales' ? route('pengajuan_dana.index', ['from' => 'sales']) : route('pengajuan_dana.index')" label="Form Pengajuan" />
        </div>

        <div class="m-hero">
            <div class="relative z-10 flex-1 min-w-0">
                <h2>Riwayat Pengajuan Dana</h2>
                <p class="truncate mt-0.5">{{ $pengajuanDanas->total() }} pengajuan • pantau status persetujuanmu</p>
            </div>
            <div class="m-hero-ic relative z-10"><i class="fas fa-history"></i></div>
        </div>

        <div class="m-tabs">
            <a href="{{ route('pengajuan_dana.history', $mTabParams + ['tipe' => 'semua']) }}" class="{{ $mTipe === 'semua' ? 'on' : '' }}">Semua</a>
            @foreach(\App\Models\PengajuanDana::TIPE_LIST as $tKey => $tLabel)
            <a href="{{ route('pengajuan_dana.history', $mTabParams + ['tipe' => $tKey]) }}" class="{{ $mTipe === $tKey ? 'on' : '' }}">{{ $tLabel }}</a>
            @endforeach
        </div>

        <form action="{{ route('pengajuan_dana.history') }}" method="GET" class="m-filter">
            @if(request('from') === 'sales')
            <input type="hidden" name="from" value="sales">
            @endif
            @if(request()->filled('tipe'))
            <input type="hidden" name="tipe" value="{{ request('tipe') }}">
            @endif
            <select name="status" class="modern-select" style="width:100%;" onchange="this.form.submit()">
                <option value="semua" {{ request('status') == 'semua' || !request('status') ? 'selected' : '' }}>Semua Status</option>
                <option value="diajukan" {{ request('status') == 'diajukan' ? 'selected' : '' }}>Menunggu Approver 1</option>
                <option value="diproses" {{ request('status') == 'diproses' ? 'selected' : '' }}>Menunggu Approver 2 / 3</option>
                <option value="disetujui" {{ request('status') == 'disetujui' ? 'selected' : '' }}>Menunggu Pembayaran</option>
                <option value="selesai" {{ request('status') == 'selesai' ? 'selected' : '' }}>Selesai</option>
                <option value="ditolak" {{ request('status') == 'ditolak' ? 'selected' : '' }}>Ditolak</option>
                <option value="dibatalkan" {{ request('status') == 'dibatalkan' ? 'selected' : '' }}>Dibatalkan</option>
            </select>
        </form>

        <div class="flex flex-col gap-2.5">
            @forelse ($pengajuanDanas as $pengajuan)
            <a href="{{ $mShowUrl($pengajuan->id) }}" class="m-item" style="border-top-color:{{ $mDot($pengajuan->status) }};">
                <div class="m-item-h">
                    <div class="m-item-t">{{ $pengajuan->judul_pengajuan }}</div>
                    <span class="m-pill" style="{{ $mPill($pengajuan->status) }}">{{ $mLabel($pengajuan) }}</span>
                </div>
                <div class="m-chips">
                    <span class="m-tipe">{{ \App\Models\PengajuanDana::TIPE_LIST[$pengajuan->tipe_expend] ?? $pengajuan->tipe_expend }}</span>
                    <span class="m-date">{{ $pengajuan->created_at->locale('id')->isoFormat('D MMM YYYY') }}</span>
                </div>
                <div class="m-stats">
                    <div><div class="m-stat-k">Total Dana</div><div class="m-stat-v mono">Rp {{ number_format($pengajuan->total_dana, 0, ',', '.') }}</div></div>
                    <div><div class="m-stat-k">Nomor</div><div class="m-stat-v mono" style="font-size:11px;">#{{ str_pad($pengajuan->id, 4, '0', STR_PAD_LEFT) }}</div></div>
                </div>
                <div class="m-go"><span>Lihat Detail</span><i class="fas fa-chevron-right"></i></div>
            </a>
            @empty
            <div class="m-empty">
                <i class="fas fa-folder-open" style="font-size:28px;color:#e2e8f0;"></i>
                <p style="font-size:11px;font-weight:800;margin-top:8px;letter-spacing:.06em;">BELUM ADA DATA SESUAI</p>
            </div>
            @endforelse
        </div>

        @if ($pengajuanDanas->hasPages())
            <div>{{ $pengajuanDanas->links() }}</div>
        @endif
    </div>
</div>

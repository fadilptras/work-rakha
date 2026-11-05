{{-- MOBILE ONLY: Detail Pengajuan Dana — gaya bahasa halaman sales. Web tidak tersentuh. --}}
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
        position: relative; overflow: hidden;
        background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
    }
    .m-hero h2 { font-size: .85rem; font-weight: 900; letter-spacing: .04em; text-transform: uppercase; line-height: 1.4; }
    .m-hero p { font-size: .7rem; color: #dbeafe; font-weight: 500; margin-top: 3px; }
    .m-hero-badges { display: flex; gap: 6px; margin-top: 10px; flex-wrap: wrap; position: relative; z-index: 10; }
    .m-hero-badges span {
        font-size: 9px; font-weight: 900; text-transform: uppercase; letter-spacing: .06em;
        border-radius: 9999px; padding: 4px 10px; border: 1px solid rgba(255,255,255,.35);
        background: rgba(255,255,255,.18);
    }
    .m-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 1rem; box-shadow: 0 4px 16px rgba(15,23,42,.05); overflow: hidden; }
    .m-card-h { display: flex; align-items: center; gap: .7rem; padding: .85rem 1rem; border-bottom: 1px solid #f1f5f9; }
    .m-ic { width: 2.5rem; height: 2.5rem; border-radius: .8rem; flex-shrink: 0; display: flex; align-items: center; justify-content: center; font-size: 1.05rem; border: 1px solid; }
    .m-kicker { font-size: 9px; font-weight: 800; letter-spacing: .14em; text-transform: uppercase; color: #94a3b8; }
    .m-title { font-size: 14px; font-weight: 800; letter-spacing: -.01em; color: #1e293b; }
    .m-card-b { padding: .9rem 1rem; }
    .m-ic-blue { background: #eff6ff; color: #2563eb; border-color: #dbeafe; }
    .m-ic-emerald { background: #ecfdf5; color: #059669; border-color: #d1fae5; }
    .m-ic-amber { background: #fffbeb; color: #d97706; border-color: #fde68a; }
    .m-ic-violet { background: #f5f3ff; color: #7c3aed; border-color: #ede9fe; }
    .m-kv { display: flex; justify-content: space-between; align-items: center; gap: .8rem; padding: .55rem 0; border-bottom: 1px solid #f8fafc; font-size: 12px; }
    .m-kv:last-child { border-bottom: 0; }
    .m-kv .k { color: #94a3b8; font-weight: 700; flex-shrink: 0; }
    .m-kv .v { color: #1e293b; font-weight: 800; text-align: right; min-width: 0; overflow-wrap: anywhere; }
    .m-step { border: 1px solid #e2e8f0; border-left-width: 4px; border-radius: .9rem; padding: .7rem .8rem; margin-bottom: .55rem; background: #fff; }
    .m-step:last-child { margin-bottom: 0; }
    .m-step-top { display: flex; justify-content: space-between; align-items: flex-start; gap: .6rem; }
    .m-who { font-size: 9px; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: .1em; }
    .m-nm { font-size: 12.5px; font-weight: 800; color: #1e293b; margin-top: 2px; }
    .m-tm { font-size: 10px; color: #94a3b8; font-weight: 600; margin-top: 1px; }
    .m-pill { font-size: 8px; font-weight: 900; text-transform: uppercase; letter-spacing: .06em; border-radius: 9999px; padding: 4px 9px; border: 1px solid; flex-shrink: 0; }
    .m-note { margin-top: .55rem; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: .7rem; padding: .55rem .65rem; font-size: 11px; color: #475569; font-style: italic; }
    .m-item { display: flex; justify-content: space-between; gap: .8rem; padding: .6rem 0; border-bottom: 1px solid #f8fafc; font-size: 12px; }
    .m-item:last-child { border-bottom: 0; }
    .m-item .d { color: #1e293b; font-weight: 700; min-width: 0; }
    .m-item .n { font-family: monospace; font-weight: 900; color: #1e293b; white-space: nowrap; }
    .m-total { display: flex; justify-content: space-between; align-items: center; margin-top: .7rem; background: linear-gradient(135deg, #eff6ff, #eef2ff); border: 1px solid #dbeafe; border-radius: .8rem; padding: .7rem .85rem; }
    .m-total span { font-size: 10px; font-weight: 900; color: #1d4ed8; text-transform: uppercase; letter-spacing: .08em; }
    .m-total strong { font-size: 15px; font-weight: 900; color: #1e40af; font-family: monospace; }
    .m-docs { display: grid; grid-template-columns: 1fr 1fr; gap: .55rem; }
    .m-doc { display: flex; flex-direction: column; align-items: center; gap: 5px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: .9rem; padding: .9rem .5rem; text-decoration: none; font-size: 22px; color: #2563eb; }
    .m-doc small { font-size: 9px; font-weight: 800; color: #475569; text-align: center; overflow-wrap: anywhere; }
    .m-act { width: 100%; border-radius: .9rem; padding: 13px; font-size: 13px; font-weight: 900; text-transform: uppercase; letter-spacing: .04em; display: flex; align-items: center; justify-content: center; gap: 8px; text-decoration: none; }
    .m-ok { background: linear-gradient(135deg, #059669, #10b981); color: #fff; box-shadow: 0 6px 16px rgba(5,150,105,.35); }
    .m-no { background: linear-gradient(135deg, #dc2626, #ef4444); color: #fff; box-shadow: 0 6px 16px rgba(220,38,38,.3); }
    .m-pdf { background: linear-gradient(135deg, #dc2626, #f87171); color: #fff; box-shadow: 0 6px 16px rgba(220,38,38,.3); }
    .m-ta { width: 100%; border: 1.5px solid #e2e8f0; border-radius: .8rem; padding: 10px; font-size: 12px; font-weight: 600; font-family: 'Outfit', sans-serif; }
    .m-empty { text-align: center; font-size: 11px; font-weight: 700; color: #94a3b8; background: #f8fafc; border: 1.5px dashed #cbd5e1; border-radius: .8rem; padding: 1.4rem .8rem; }
</style>
@endpush

@php
    $mSteps = [
        ['id' => $pengajuanDana->approver_dana_1_id, 'name' => $pengajuanDana->approverDana1->name ?? null, 'st' => $pengajuanDana->approver_1_status, 'ct' => $pengajuanDana->approver_1_catatan, 'at' => $pengajuanDana->approver_1_approved_at],
        ['id' => $pengajuanDana->approver_dana_2_id, 'name' => $pengajuanDana->approverDana2->name ?? null, 'st' => $pengajuanDana->approver_2_status, 'ct' => $pengajuanDana->approver_2_catatan, 'at' => $pengajuanDana->approver_2_approved_at],
        ['id' => $pengajuanDana->approver_dana_3_id, 'name' => $pengajuanDana->approverDana3->name ?? null, 'st' => $pengajuanDana->approver_3_status, 'ct' => $pengajuanDana->approver_3_catatan, 'at' => ($pengajuanDana->approver_3_status === 'disetujui' && $pengajuanDana->status === 'selesai') ? $pengajuanDana->updated_at : $pengajuanDana->approver_3_approved_at],
        ['id' => $pengajuanDana->approver_dana_4_id, 'name' => $pengajuanDana->approverDana4->name ?? null, 'st' => $pengajuanDana->approver_4_status, 'ct' => $pengajuanDana->approver_4_catatan, 'at' => ($pengajuanDana->approver_4_status === 'disetujui' && $pengajuanDana->status === 'selesai') ? $pengajuanDana->updated_at : $pengajuanDana->approver_4_approved_at],
    ];
    $mTheme = fn($s) => match($s) {
        'disetujui' => ['#22c55e', 'background:#dcfce7;color:#15803d;border-color:#bbf7d0;'],
        'ditolak' => ['#ef4444', 'background:#fee2e2;color:#b91c1c;border-color:#fecaca;'],
        'skipped' => ['#94a3b8', 'background:#f1f5f9;color:#475569;border-color:#e2e8f0;'],
        default => ['#eab308', 'background:#fef9c3;color:#a16207;border-color:#fde68a;'],
    };
    $mStatusText = match($pengajuanDana->status) {
        'selesai' => 'Selesai',
        'ditolak' => 'Ditolak',
        'dibatalkan' => 'Dibatalkan',
        'disetujui', 'proses_pembayaran' => 'Menunggu Pembayaran',
        'diproses' => ($pengajuanDana->approver_2_status === 'menunggu')
            ? 'Menunggu Approver 2'
            : (($pengajuanDana->approver_3_status === 'menunggu')
                ? 'Menunggu Approver 3'
                : 'Diproses'),
        'diajukan' => 'Menunggu Approver 1',
        default => 'Menunggu',
    };
    $mBack = request('from') === 'monitoring'
        ? [request('src') === 'sales' ? route('pengajuan_dana.monitoring_all', ['from' => 'sales']) : route('pengajuan_dana.monitoring_all'), 'Monitoring']
        : (request('from') === 'sales'
            ? [route('pengajuan_dana.history', ['from' => 'sales']), 'Riwayat']
            : (Auth::id() != $pengajuanDana->user_id
                ? [route('notifikasi.index'), 'Notifikasi']
                : [route('pengajuan_dana.history'), 'Riwayat']));
@endphp

<div class="m-wrap flex flex-col flex-1 min-h-screen relative overflow-x-clip text-slate-800 pb-16">
    <div class="m-mesh"></div>

    <div class="relative z-10 w-full max-w-none mx-0 px-4 pt-4 flex-1 flex flex-col gap-3">
        <div class="w-full flex justify-start">
            <x-ui.back-button :href="$mBack[0]" :label="$mBack[1]" />
        </div>

        <div class="m-hero">
            <div class="relative z-10">
                <h2>{{ $pengajuanDana->judul_pengajuan }}</h2>
                <p>Diajukan {{ $pengajuanDana->created_at->locale('id')->isoFormat('dddd, D MMMM YYYY') }}</p>
                <div class="m-hero-badges">
                    <span>{{ \App\Models\PengajuanDana::TIPE_LIST[$pengajuanDana->tipe_expend] ?? $pengajuanDana->tipe_expend }}</span>
                    <span>{{ $mStatusText }}</span>
                    <span>#{{ str_pad($pengajuanDana->id, 4, '0', STR_PAD_LEFT) }}</span>
                </div>
            </div>
        </div>

        @if(Auth::id() == $pengajuanDana->user_id || Auth::user()->role == 'admin' || Auth::user()->can('approve', $pengajuanDana))
        <a href="{{ route('pengajuan_dana.download', $pengajuanDana) }}" class="m-act m-pdf">
            <i class="fas fa-file-pdf"></i> Cetak PDF
        </a>
        @endif

        <div class="m-card">
            <div class="m-card-h">
                <div class="m-ic m-ic-blue"><i class="fas fa-user-circle"></i></div>
                <div class="min-w-0"><div class="m-kicker">Pemohon & Pencairan</div><div class="m-title">Informasi Dana</div></div>
            </div>
            <div class="m-card-b" style="padding-top:.4rem;">
                <div class="m-kv"><span class="k">Nama Pemohon</span><span class="v">{{ $pengajuanDana->user->name ?? 'Unknown' }}</span></div>
                <div class="m-kv"><span class="k">Divisi</span><span class="v">{{ $pengajuanDana->user->divisi }}</span></div>
                <div class="m-kv"><span class="k">Bank</span><span class="v">{{ $pengajuanDana->nama_bank }}</span></div>
                <div class="m-kv"><span class="k">No. Rekening</span><span class="v" style="font-family:monospace;">{{ $pengajuanDana->no_rekening }}</span></div>
                <div class="m-kv"><span class="k">Atas Nama</span><span class="v">{{ $pengajuanDana->nama_rek }}</span></div>
            </div>
        </div>

        <div class="m-card">
            <div class="m-card-h">
                <div class="m-ic m-ic-violet"><i class="fas fa-history"></i></div>
                <div class="min-w-0"><div class="m-kicker">Alur Verifikasi</div><div class="m-title">Persetujuan</div></div>
            </div>
            <div class="m-card-b">
                @foreach($mSteps as $i => $s)
                    @if($s['id'])
                        @php [$mBorder, $mBadge] = $mTheme($s['st']); @endphp
                        <div class="m-step" style="border-left-color:{{ $mBorder }};">
                            <div class="m-step-top">
                                <div class="min-w-0">
                                    <div class="m-who">Approver {{ $i + 1 }}</div>
                                    <div class="m-nm">{{ $s['name'] ?? ('Approver ' . ($i + 1)) }}</div>
                                    @if($s['at'])
                                    <div class="m-tm">{{ $s['at']->translatedFormat('d M Y, H:i') }}</div>
                                    @endif
                                </div>
                                <span class="m-pill" style="{{ $mBadge }}">{{ ucfirst($s['st']) }}</span>
                            </div>
                            @if($s['ct'])
                            <div class="m-note">“{{ $s['ct'] }}”</div>
                            @endif
                        </div>
                    @endif
                @endforeach
            </div>
        </div>

        <div class="m-card">
            <div class="m-card-h">
                <div class="m-ic m-ic-emerald"><i class="fas fa-list-ul"></i></div>
                <div class="min-w-0"><div class="m-kicker">Kebutuhan</div><div class="m-title">Rincian Dana</div></div>
            </div>
            <div class="m-card-b" style="padding-top:.4rem;">
                @forelse($pengajuanDana->rincian_dana as $rincian)
                <div class="m-item">
                    <span class="d">{{ $rincian['deskripsi'] ?? '-' }}</span>
                    <span class="n">Rp {{ number_format($rincian['jumlah'] ?? 0, 0, ',', '.') }}</span>
                </div>
                @empty
                <div class="m-empty">Data rincian tidak tersedia.</div>
                @endforelse
                <div class="m-total">
                    <span>Total Pengajuan</span>
                    <strong>Rp {{ number_format($pengajuanDana->total_dana, 0, ',', '.') }}</strong>
                </div>
            </div>
        </div>

        <div class="m-card">
            <div class="m-card-h">
                <div class="m-ic m-ic-amber"><i class="fas fa-paperclip"></i></div>
                <div class="min-w-0"><div class="m-kicker">Bukti & Berkas</div><div class="m-title">Dokumen</div></div>
            </div>
            <div class="m-card-b">
                @if(empty($pengajuanDana->lampiran) && !$pengajuanDana->bukti_transfer && !$pengajuanDana->invoice)
                    <div class="m-empty">Tidak ada dokumen yang dilampirkan.</div>
                @else
                    <div class="m-docs">
                        @if ($pengajuanDana->lampiran)
                            @foreach ($pengajuanDana->lampiran as $lampiran)
                            <a class="m-doc" href="{{ asset('storage/' . $lampiran) }}">
                                <i class="fas fa-file-alt"></i>
                                <small>{{ basename($lampiran) }}</small>
                            </a>
                            @endforeach
                        @endif
                        @if ($pengajuanDana->bukti_transfer)
                        <a class="m-doc" href="{{ asset('storage/' . $pengajuanDana->bukti_transfer) }}">
                            <i class="fas fa-receipt"></i>
                            <small>Bukti Transfer</small>
                        </a>
                        @endif
                        @if ($pengajuanDana->invoice)
                        <a class="m-doc" href="{{ asset('storage/' . $pengajuanDana->invoice) }}">
                            <i class="fas fa-file-invoice"></i>
                            <small>Invoice</small>
                        </a>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        @can('approve', $pengajuanDana)
        @php
            $mUser = Auth::user();
            $mStage = null;
            if ($mUser->id == $pengajuanDana->approver_dana_1_id && $pengajuanDana->approver_1_status == 'menunggu') $mStage = 1;
            elseif ($mUser->id == $pengajuanDana->approver_dana_2_id && $pengajuanDana->approver_2_status == 'menunggu') $mStage = 2;
            elseif ($mUser->id == $pengajuanDana->approver_dana_3_id && $pengajuanDana->approver_3_status == 'menunggu') $mStage = 3;
            elseif ($mUser->id == $pengajuanDana->approver_dana_4_id && $pengajuanDana->approver_4_status == 'menunggu') $mStage = 4;
            $mIsPaymentStage = ($mStage == 4) || ($mStage == 3 && empty($pengajuanDana->approver_dana_4_id));
        @endphp
        <div class="m-card">
            <div class="m-card-h">
                <div class="m-ic m-ic-emerald"><i class="fas fa-gavel"></i></div>
                <div class="min-w-0"><div class="m-kicker">Keputusanmu</div><div class="m-title">Tindakan Persetujuan</div></div>
            </div>
            <div class="m-card-b flex flex-col gap-2">
                <form action="{{ route('pengajuan_dana.approve', $pengajuanDana) }}" method="POST" enctype="multipart/form-data" class="flex flex-col gap-2 m-0">
                    @csrf
                    @if($mIsPaymentStage)
                    <div>
                        <label style="font-size:11px;font-weight:900;color:#15803d;">Bukti Transfer (Opsional)</label>
                        <input type="file" name="bukti_transfer" style="font-size:11px;margin-top:4px;" accept=".pdf,.jpg,.jpeg,.png">
                    </div>
                    @endif
                    <textarea name="catatan_persetujuan" rows="2" class="m-ta" placeholder="Catatan persetujuan (opsional)..."></textarea>
                    <button type="submit" class="m-act m-ok"><i class="fas fa-check-circle"></i> {{ $mIsPaymentStage ? 'Selesaikan & Konfirmasi Pembayaran' : 'Setujui' }}</button>
                </form>
                <form action="{{ route('pengajuan_dana.reject', $pengajuanDana) }}" method="POST" class="flex flex-col gap-2 m-0">
                    @csrf
                    <textarea name="catatan_penolakan" rows="2" class="m-ta" placeholder="Alasan penolakan (wajib)..." required></textarea>
                    <button type="submit" class="m-act m-no"><i class="fas fa-times-circle"></i> Tolak</button>
                </form>
            </div>
        </div>
        @endcan

        @can('cancel', $pengajuanDana)
        <div class="m-card" style="text-align:center;background:#fef2f2;border-color:#fecaca;">
            <p style="font-size:12px;font-weight:900;color:#991b1b;text-transform:uppercase;letter-spacing:.04em;">Batalkan Pengajuan?</p>
            <p style="font-size:11px;color:#dc2626;font-weight:600;margin:4px 0 10px;">Tindakan ini tidak dapat diurungkan.</p>
            <form action="{{ route('pengajuan_dana.cancel', $pengajuanDana) }}" method="POST" class="m-0" onsubmit="confirmSubmit(event, 'Apakah Anda benar-benar yakin ingin membatalkan pengajuan dana ini?');">
                @csrf
                @if(request('from') === 'sales')
                <input type="hidden" name="from" value="sales">
                @endif
                <button type="submit" class="m-act" style="background:#fff;border:1.5px solid #fecaca;color:#dc2626;">Batalkan Pengajuan</button>
            </form>
        </div>
        @endcan
    </div>
</div>

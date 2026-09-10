@php
    use Illuminate\Support\Str;
@endphp

<x-layout-users title="{{ $title ?? 'Detail SPH' }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    @push('styles')
    <style>
        body { font-family: 'Outfit', sans-serif; background-color: #ede9fe; }

        /* == Background == */
        .mesh-bg { 
            background-color: #ede9fe;
            background-image: 
                radial-gradient(at 0% 0%, rgba(255, 255, 255, 0.4) 0px, transparent 50%),
                radial-gradient(at 100% 0%, rgba(99, 102, 241, 0.1) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(59, 130, 246, 0.1) 0px, transparent 50%);
            background-attachment: fixed;
            position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: 0;
            pointer-events: none;
        }

        /* == Header Style == */
        .page-header {
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
            border-radius: 1.25rem; padding: 1rem 1.5rem; color: white;
            box-shadow: 0 10px 25px -5px rgba(59, 130, 246, 0.3);
            position: relative; overflow: hidden;
        }
        .page-header::before {
            content: ''; position: absolute; top: -50%; left: -50%; width: 200%; height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 60%);
            transform: rotate(30deg); pointer-events: none;
        }
        .header-content { position: relative; z-index: 1; }

        /* == Back Button == */
        .btn-back-modern {
            display: inline-flex; align-items: center; gap: 10px;
            padding: 6px 16px 6px 6px;
            background: rgba(255, 255, 255, 0.7); backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.9); border-radius: 9999px;
            color: #1e293b; font-size: 0.85rem; font-weight: 700;
            text-decoration: none; transition: all 0.25s ease;
            box-shadow: 0 2px 6px rgba(0,0,0,0.05); width: fit-content; margin-bottom: 0;
        }
        .btn-back-modern .icon-circle {
            width: 28px; height: 28px; background: #fff; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            color: #3b82f6; font-size: 0.8rem; box-shadow: 0 2px 6px rgba(0,0,0,0.06);
            transition: transform 0.3s ease;
        }
        .btn-back-modern:hover { background: #fff; color: #1d4ed8; transform: translateY(-2px); }
        .btn-back-modern:hover .icon-circle { transform: translateX(-3px); background: #EFF6FF; }

        /* == Card & Info == */
        .glass-card {
            background: rgba(255, 255, 255, 0.95); border: 1px solid rgba(226, 232, 240, 0.9);
            border-radius: 1rem; padding: 1rem 1.25rem; box-shadow: 0 6px 20px rgba(15, 23, 42, 0.05);
        }

        .info-item .info-label {
            font-size: 0.65rem; font-weight: 800; color: #94a3b8;
            text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 0.25rem;
        }
        .info-item .info-value { font-size: 0.85rem; font-weight: 700; color: #1e293b; }
        .sph-number { font-size: 1rem; font-weight: 800; color: #2563eb; letter-spacing: 0.01em; }
    </style>
    @endpush

    <div class="flex flex-col flex-1 min-h-screen relative overflow-hidden text-slate-800 pb-16">
        <div class="mesh-bg"></div>

        <div class="relative z-10 w-full max-w-6xl mx-auto p-4 sm:p-6 lg:p-10 space-y-4 flex-1 flex flex-col">
            <div class="w-full flex justify-start mb-3 md:mb-4">
                <a href="{{ route('sales.pricing') }}" class="btn-back-modern shrink-0">
                    <div class="icon-circle"><i class="fas fa-arrow-left"></i></div>
                    Back to Pricing / SPH
                </a>
            </div>

            {{-- Header --}}
            <div class="page-header flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div class="header-content">
                    <p class="text-blue-200 text-[10px] font-bold uppercase tracking-widest mb-1"><i class="fas fa-file-contract mr-1"></i> Surat Penawaran Harga</p>
                    <h1 class="text-xl md:text-2xl font-extrabold tracking-tight text-white mb-1">Detail Dokumen SPH</h1>
                    <p class="text-blue-100 text-[11px] md:text-xs opacity-90 font-medium">{{ $sph->date ? $sph->date->translatedFormat('d F Y') : '-' }}</p>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <a href="{{ route('sales.sph.export.pdf', $sph) }}" title="Export PDF (kolom menyesuaikan isi)" class="shrink-0 inline-flex items-center justify-center w-9 h-9 bg-red-50 hover:bg-red-100 text-red-600 hover:text-red-700 rounded-lg text-base transition-all border border-red-200 hover:border-red-300 hover:shadow-md hover:shadow-red-100 hover:-translate-y-0.5 cursor-pointer">
                        <i class="fas fa-file-pdf"></i>
                    </a>
                    <a href="{{ route('sales.sph.export.excel', $sph) }}" title="Export Excel" class="shrink-0 inline-flex items-center justify-center w-9 h-9 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 rounded-lg text-base transition-all border border-emerald-200 hover:border-emerald-300 hover:shadow-md hover:shadow-emerald-100 hover:-translate-y-0.5 cursor-pointer">
                        <i class="fas fa-file-excel"></i>
                    </a>
                </div>
            </div>

            {{-- Info dokumen & klien (Grid Formasi Compact) --}}
            <div class="glass-card">
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-y-4 gap-x-6">
                    <div class="info-item">
                        <div class="info-label"><i class="fas fa-hashtag mr-1"></i> Nomor SPH</div>
                        <div class="sph-number">{{ $sph->sph_number }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label"><i class="fas fa-building mr-1"></i> Perusahaan / Institusi</div>
                        <div class="info-value">{{ $sph->customer_company ?? '-' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label"><i class="fas fa-user mr-1"></i> Customer Name (UP)</div>
                        <div class="info-value">{{ $sph->customer_name ?? '-' }}</div>
                    </div>
                    <div class="info-item border-t border-slate-100 pt-3 md:border-none md:pt-0">
                        <div class="info-label"><i class="fas fa-user-tie mr-1"></i> Contact Person (PS)</div>
                        <div class="info-value">{{ $sph->ps ?? '-' }}</div>
                    </div>
                    <div class="info-item border-t border-slate-100 pt-3 md:border-none md:pt-0">
                        <div class="info-label"><i class="fas fa-phone mr-1"></i> Phone Number</div>
                        <div class="info-value">{{ $sph->ps_phone ?? '-' }}</div>
                    </div>
                    <div class="info-item border-t border-slate-100 pt-3 md:border-none md:pt-0">
                        <div class="info-label"><i class="fas fa-percent mr-1"></i> PPN Setting</div>
                        <div class="info-value">
                            @if($sph->vat_percent > 0)
                                PPN {{ $sph->vat_percent }}%
                            @else
                                Non-PPN (0%)
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tabel item --}}
            <div class="glass-card !p-0 overflow-hidden flex-1 flex flex-col">
                <div class="px-5 py-4 border-b border-slate-200 bg-slate-50 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3 shrink-0">
                        <div class="w-10 h-10 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center text-lg"><i class="fas fa-boxes"></i></div>
                        <div>
                            <h3 class="text-lg font-black text-slate-800">Item Penawaran</h3>
                            <p class="text-xs text-slate-500 font-semibold mt-0.5">Kolom PDF menyesuaikan isi otomatis.</p>
                        </div>
                    </div>
                    <div class="shrink-0 bg-white px-3 py-1.5 rounded-lg border border-slate-200 shadow-sm flex items-center gap-1.5" title="Total Item">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total:</span>
                        <span class="text-sm font-black text-blue-600">{{ count($sph->items ?? []) }}</span>
                    </div>
                </div>
                <div class="overflow-x-auto flex-1">
                    <table class="w-full text-left border-collapse whitespace-nowrap">
                        <thead>
                            <tr class="bg-slate-50 text-[10px] font-bold uppercase tracking-wider border-b border-slate-200">
                                <th class="py-2.5 px-3 text-center text-slate-500 w-10">No</th>
                                <th class="py-2.5 px-3 text-slate-500">Product Name</th>
                                <th class="py-2.5 px-3 text-slate-500">Presentation</th>
                                <th class="py-2.5 px-3 text-right text-blue-600">HNA Price</th>
                                <th class="py-2.5 px-3 text-right text-slate-500">HNA / Pcs</th>
                                <th class="py-2.5 px-3 text-center text-amber-600 w-20">Discount</th>
                                <th class="py-2.5 px-3 text-right text-slate-600">Net+PPN</th>
                                <th class="py-2.5 px-3 text-right bg-blue-50/40 text-blue-600">Net+PPN / Pcs</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-xs">
                            @forelse($sph->items ?? [] as $i => $item)
                                @php
                                    $hna = (float) ($item['base_price'] ?? $item['unit_price']);
                                    $packQty = (int) ($item['pack_qty'] ?? 1);
                                    $discount = (float) ($item['discount'] ?? 0);
                                    $hnaPcs = $hna / max(1, $packQty);
                                    $netPpn = $hna * (1 - $discount / 100) * (1 + ($sph->vat_percent / 100));
                                    $netPpnPcs = $netPpn / max(1, $packQty);
                                    $qty = (int) ($item['qty'] ?? 1);
                                    $rowTotal = $netPpn * $qty;
                                @endphp
                                <tr class="hover:bg-slate-50/70 transition-colors text-slate-700">
                                    <td class="py-2.5 px-3 text-center font-bold text-slate-400">{{ $i + 1 }}</td>
                                    <td class="py-2.5 px-3 border-l border-slate-100 font-bold text-slate-800 whitespace-normal leading-relaxed min-w-[150px]">{{ $item['product_name'] }}</td>
                                    <td class="py-2.5 px-3 border-l border-slate-100"><span class="bg-slate-100 text-slate-600 px-2 py-1 rounded-md font-semibold text-[10px]">{{ $item['presentation'] ?? '-' }}</span></td>
                                    <td class="py-2.5 px-3 text-right border-l border-slate-100 font-bold text-slate-600">Rp {{ number_format($hna, 0, ',', '.') }}</td>
                                    <td class="py-2.5 px-3 text-right border-l border-slate-100 font-semibold text-slate-500">Rp {{ number_format($hnaPcs, 0, ',', '.') }}</td>
                                    <td class="py-2.5 px-3 text-center border-l border-slate-100">
                                        @if($discount > 0)
                                            <span class="inline-flex items-center gap-1 bg-amber-50 text-amber-600 px-2 py-1 rounded-md font-bold text-[10px] border border-amber-200">
                                                <i class="fas fa-tag text-[8px]"></i> {{ rtrim(rtrim(number_format($discount, 2, ',', '.'), '0'), ',') }}%
                                            </span>
                                        @else
                                            <span class="text-slate-300 text-[10px] font-bold">-</span>
                                        @endif
                                    </td>
                                    <td class="py-2.5 px-3 text-right border-l border-slate-100 font-bold text-slate-700">Rp {{ number_format($netPpn, 0, ',', '.') }}</td>
                                    <td class="py-2.5 px-3 text-right border-l border-slate-100 bg-blue-50/40 font-black text-blue-600">Rp {{ number_format($netPpnPcs, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="py-8 text-center text-slate-400 bg-slate-50 text-[11px]">Data item tidak tersedia.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="px-5 py-3 border-t border-slate-100 bg-slate-50 shrink-0">
                    <div class="text-[10px] text-slate-500 font-semibold">
                        @if($sph->vat_percent > 0) Harga di atas sudah termasuk PPN {{ $sph->vat_percent }}%.
                        @else Harga di atas belum termasuk PPN. @endif
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-layout-users>
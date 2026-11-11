<table>
    {{-- Kop Perusahaan --}}
    <tr>
        <td colspan="19" style="font-weight: bold; font-size: 14pt; text-align: center; vertical-align: middle;">PT RAKHA NUSANTARA MEDIKA</td>
    </tr>
    <tr>
        <td colspan="19" style="font-weight: bold; font-size: 12pt; text-align: center; vertical-align: middle;">REKAPITULASI SELURUH PERMINTAAN BARANG (SPB)</td>
    </tr>
    <tr>
        <td colspan="19" style="font-size: 10pt; text-align: center; vertical-align: middle;">Periode: {{ $periodText }}</td>
    </tr>
    <tr><td colspan="19"></td></tr>

    {{-- Header Tabel --}}
    <thead>
        <tr>
            <th style="border: 1px solid #000; font-weight: bold; background-color: #1e40af; color: #ffffff; text-align: center;">No</th>
            <th style="border: 1px solid #000; font-weight: bold; background-color: #1e40af; color: #ffffff; text-align: center;">Tanggal SPB</th>
            <th style="border: 1px solid #000; font-weight: bold; background-color: #1e40af; color: #ffffff; text-align: center;">Nomor SPB</th>
            <th style="border: 1px solid #000; font-weight: bold; background-color: #1e40af; color: #ffffff; text-align: left;">Pemohon</th>
            <th style="border: 1px solid #000; font-weight: bold; background-color: #1e40af; color: #ffffff; text-align: left;">Divisi</th>
            <th style="border: 1px solid #000; font-weight: bold; background-color: #1e40af; color: #ffffff; text-align: left;">Perihal / Judul Pengajuan</th>
            <th style="border: 1px solid #000; font-weight: bold; background-color: #1e40af; color: #ffffff; text-align: center;">Item #</th>
            <th style="border: 1px solid #000; font-weight: bold; background-color: #1e40af; color: #ffffff; text-align: left;">Nama Barang</th>
            <th style="border: 1px solid #000; font-weight: bold; background-color: #1e40af; color: #ffffff; text-align: center;">Kode Produk</th>
            <th style="border: 1px solid #000; font-weight: bold; background-color: #1e40af; color: #ffffff; text-align: center;">Qty Diajukan</th>
            <th style="border: 1px solid #000; font-weight: bold; background-color: #1e40af; color: #ffffff; text-align: center;">Satuan</th>
            <th style="border: 1px solid #000; font-weight: bold; background-color: #1e40af; color: #ffffff; text-align: center;">Qty Diproses</th>
            <th style="border: 1px solid #000; font-weight: bold; background-color: #1e40af; color: #ffffff; text-align: center;">Sisa Qty</th>
            <th style="border: 1px solid #000; font-weight: bold; background-color: #1e40af; color: #ffffff; text-align: left;">Supplier</th>
            <th style="border: 1px solid #000; font-weight: bold; background-color: #1e40af; color: #ffffff; text-align: left;">Keterangan Item</th>
            <th style="border: 1px solid #000; font-weight: bold; background-color: #1e40af; color: #ffffff; text-align: center;">Status SPB</th>
            <th style="border: 1px solid #000; font-weight: bold; background-color: #1e40af; color: #ffffff; text-align: left;">Status Monitoring</th>
            <th style="border: 1px solid #000; font-weight: bold; background-color: #1e40af; color: #ffffff; text-align: left;">Catatan Pemohon</th>
            <th style="border: 1px solid #000; font-weight: bold; background-color: #1e40af; color: #ffffff; text-align: center;">Tanggal Selesai / Disetujui</th>
        </tr>
    </thead>
    <tbody>
        @php
            $rowNo = 1;
            $grandTotalDiajukan = 0;
            $grandTotalDiproses = 0;
        @endphp

        @forelse($pengajuanBarangs as $pengajuan)
            @php
                $rincian = is_string($pengajuan->rincian_barang) ? json_decode($pengajuan->rincian_barang, true) : ($pengajuan->rincian_barang ?? []);
                $tglSpb = $pengajuan->created_at ? $pengajuan->created_at->format('d/m/Y') : '-';
                $nomorSpb = $pengajuan->nomor_surat ?? '-';
                $pemohon = $pengajuan->user->name ?? '-';
                $divisi = $pengajuan->divisi ?? $pengajuan->user->divisi ?? '-';
                $judul = $pengajuan->judul_pengajuan ?? '-';
                $status = strtoupper($pengajuan->status ?? 'DIAJUKAN');
                $monitoring = $pengajuan->status_monitoring ?? '-';
                $catatan = $pengajuan->catatan_pemohon ?? '-';
                $tglSelesai = $pengajuan->tanggal_approved_4 ? \Carbon\Carbon::parse($pengajuan->tanggal_approved_4)->format('d/m/Y H:i') : ($pengajuan->tanggal_approved_3 ? \Carbon\Carbon::parse($pengajuan->tanggal_approved_3)->format('d/m/Y H:i') : '-');
            @endphp

            @if(empty($rincian))
                <tr>
                    <td style="border: 1px solid #cbd5e1; text-align: center;">{{ $rowNo++ }}</td>
                    <td style="border: 1px solid #cbd5e1; text-align: center;">{{ $tglSpb }}</td>
                    <td style="border: 1px solid #cbd5e1; text-align: center; font-weight: bold;">{{ $nomorSpb }}</td>
                    <td style="border: 1px solid #cbd5e1;">{{ $pemohon }}</td>
                    <td style="border: 1px solid #cbd5e1;">{{ $divisi }}</td>
                    <td style="border: 1px solid #cbd5e1;">{{ $judul }}</td>
                    <td style="border: 1px solid #cbd5e1; text-align: center;">-</td>
                    <td style="border: 1px solid #cbd5e1; color: #94a3b8; font-style: italic;">Tidak ada rincian barang</td>
                    <td style="border: 1px solid #cbd5e1; text-align: center;">-</td>
                    <td style="border: 1px solid #cbd5e1; text-align: center;">0</td>
                    <td style="border: 1px solid #cbd5e1; text-align: center;">-</td>
                    <td style="border: 1px solid #cbd5e1; text-align: center;">0</td>
                    <td style="border: 1px solid #cbd5e1; text-align: center;">0</td>
                    <td style="border: 1px solid #cbd5e1;">-</td>
                    <td style="border: 1px solid #cbd5e1;">-</td>
                    <td style="border: 1px solid #cbd5e1; text-align: center; font-weight: bold;">{{ $status }}</td>
                    <td style="border: 1px solid #cbd5e1;">{{ $monitoring }}</td>
                    <td style="border: 1px solid #cbd5e1;">{{ $catatan }}</td>
                    <td style="border: 1px solid #cbd5e1; text-align: center;">{{ $tglSelesai }}</td>
                </tr>
            @else
                @foreach($rincian as $itemIdx => $item)
                    @php
                        $qty = (float) ($item['jumlah'] ?? 0);
                        $diproses = isset($item['jumlah_diproses']) ? (float) $item['jumlah_diproses'] : 0;
                        $sisa = max(0, $qty - $diproses);
                        $grandTotalDiajukan += $qty;
                        $grandTotalDiproses += $diproses;
                    @endphp
                    <tr>
                        <td style="border: 1px solid #cbd5e1; text-align: center;">{{ $rowNo++ }}</td>
                        <td style="border: 1px solid #cbd5e1; text-align: center;">{{ $tglSpb }}</td>
                        <td style="border: 1px solid #cbd5e1; text-align: center; font-weight: bold;">{{ $nomorSpb }}</td>
                        <td style="border: 1px solid #cbd5e1;">{{ $pemohon }}</td>
                        <td style="border: 1px solid #cbd5e1;">{{ $divisi }}</td>
                        <td style="border: 1px solid #cbd5e1;">{{ $judul }}</td>
                        <td style="border: 1px solid #cbd5e1; text-align: center;">{{ $itemIdx + 1 }}</td>
                        <td style="border: 1px solid #cbd5e1; font-weight: bold;">{{ $item['nama_barang'] ?? $item['deskripsi'] ?? '-' }}</td>
                        <td style="border: 1px solid #cbd5e1; text-align: center;">{{ $item['kode_produk'] ?? '-' }}</td>
                        <td style="border: 1px solid #cbd5e1; text-align: center;">{{ $qty }}</td>
                        <td style="border: 1px solid #cbd5e1; text-align: center;">{{ $item['satuan'] ?? '-' }}</td>
                        <td style="border: 1px solid #cbd5e1; text-align: center;">{{ $diproses }}</td>
                        <td style="border: 1px solid #cbd5e1; text-align: center;">{{ $sisa }}</td>
                        <td style="border: 1px solid #cbd5e1;">{{ $item['supplier'] ?? '-' }}</td>
                        <td style="border: 1px solid #cbd5e1;">{{ $item['keterangan'] ?? '-' }}</td>
                        <td style="border: 1px solid #cbd5e1; text-align: center; font-weight: bold;">{{ $status }}</td>
                        <td style="border: 1px solid #cbd5e1;">{{ $monitoring }}</td>
                        <td style="border: 1px solid #cbd5e1;">{{ $catatan }}</td>
                        <td style="border: 1px solid #cbd5e1; text-align: center;">{{ $tglSelesai }}</td>
                    </tr>
                @endforeach
            @endif
        @empty
            <tr>
                <td colspan="19" style="border: 1px solid #cbd5e1; text-align: center; color: #94a3b8; padding: 16px;">
                    Belum ada data pengajuan barang.
                </td>
            </tr>
        @endforelse

        @if($grandTotalDiajukan > 0)
        <tr>
            <td colspan="9" style="border: 1px solid #000; font-weight: bold; text-align: right; background-color: #f1f5f9;">TOTAL KESELURUHAN</td>
            <td style="border: 1px solid #000; font-weight: bold; text-align: center; background-color: #f1f5f9;">{{ $grandTotalDiajukan }}</td>
            <td style="border: 1px solid #000; background-color: #f1f5f9;"></td>
            <td style="border: 1px solid #000; font-weight: bold; text-align: center; background-color: #f1f5f9;">{{ $grandTotalDiproses }}</td>
            <td style="border: 1px solid #000; font-weight: bold; text-align: center; background-color: #f1f5f9;">{{ max(0, $grandTotalDiajukan - $grandTotalDiproses) }}</td>
            <td colspan="6" style="border: 1px solid #000; background-color: #f1f5f9;"></td>
        </tr>
        @endif
    </tbody>
</table>

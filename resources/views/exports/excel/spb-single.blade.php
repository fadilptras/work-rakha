<table>
    {{-- Kop Perusahaan --}}
    <tr>
        <td colspan="9" style="font-weight: bold; font-size: 14pt; text-align: center; vertical-align: middle;">PT RAKHA NUSANTARA MEDIKA</td>
    </tr>
    <tr>
        <td colspan="9" style="font-weight: bold; font-size: 12pt; text-align: center; vertical-align: middle;">SURAT PERMINTAAN BARANG (SPB)</td>
    </tr>
    <tr>
        <td colspan="9" style="font-size: 10pt; text-align: center; vertical-align: middle;">Nomor: {{ $pengajuan->nomor_surat ?? '-' }}</td>
    </tr>
    <tr><td colspan="9"></td></tr>

    {{-- Metadata Pengajuan --}}
    <tr>
        <td style="font-weight: bold; width: 180px;">Nomor SPB</td>
        <td colspan="3">: {{ $pengajuan->nomor_surat ?? '-' }}</td>
        <td style="font-weight: bold; width: 160px;">Tanggal Pengajuan</td>
        <td colspan="4">: {{ $pengajuan->created_at ? $pengajuan->created_at->locale('id')->isoFormat('D MMMM YYYY, HH:mm') : '-' }}</td>
    </tr>
    <tr>
        <td style="font-weight: bold;">Pemohon</td>
        <td colspan="3">: {{ $pengajuan->user->name ?? '-' }}</td>
        <td style="font-weight: bold;">Status Pengajuan</td>
        <td colspan="4">: {{ strtoupper($pengajuan->status ?? 'DIAJUKAN') }}</td>
    </tr>
    <tr>
        <td style="font-weight: bold;">Divisi / Jabatan</td>
        <td colspan="3">: {{ $pengajuan->user->divisi ?? '-' }} / {{ $pengajuan->user->jabatan ?? '-' }}</td>
        <td style="font-weight: bold;">Status Pelacakan</td>
        <td colspan="4">: {{ $pengajuan->status_monitoring ?? 'Diajukan' }}</td>
    </tr>
    <tr>
        <td style="font-weight: bold;">Perihal / Judul</td>
        <td colspan="8">: {{ $pengajuan->judul_pengajuan ?? '-' }}</td>
    </tr>
    @if(!empty($pengajuan->catatan_pemohon))
    <tr>
        <td style="font-weight: bold;">Catatan Pemohon</td>
        <td colspan="8">: {{ $pengajuan->catatan_pemohon }}</td>
    </tr>
    @endif
    <tr><td colspan="9"></td></tr>

    {{-- Tabel Rincian Barang --}}
    <tr>
        <td colspan="9" style="font-weight: bold; font-size: 11pt; background-color: #dbeafe;">RINCIAN BARANG YANG DIAJUKAN</td>
    </tr>
    <tr>
        <th style="border: 1px solid #000; font-weight: bold; background-color: #f1f5f9; text-align: center;">No</th>
        <th style="border: 1px solid #000; font-weight: bold; background-color: #f1f5f9; text-align: left;">Nama Barang</th>
        <th style="border: 1px solid #000; font-weight: bold; background-color: #f1f5f9; text-align: center;">Kode Produk</th>
        <th style="border: 1px solid #000; font-weight: bold; background-color: #f1f5f9; text-align: center;">Satuan</th>
        <th style="border: 1px solid #000; font-weight: bold; background-color: #f1f5f9; text-align: center;">Qty Diajukan</th>
        <th style="border: 1px solid #000; font-weight: bold; background-color: #f1f5f9; text-align: center;">Qty Diproses</th>
        <th style="border: 1px solid #000; font-weight: bold; background-color: #f1f5f9; text-align: center;">Sisa</th>
        <th style="border: 1px solid #000; font-weight: bold; background-color: #f1f5f9; text-align: left;">Supplier</th>
        <th style="border: 1px solid #000; font-weight: bold; background-color: #f1f5f9; text-align: left;">Keterangan</th>
    </tr>

    @php
        $rincian = is_string($pengajuan->rincian_barang) ? json_decode($pengajuan->rincian_barang, true) : ($pengajuan->rincian_barang ?? []);
        $totalDiajukan = 0;
        $totalDiproses = 0;
    @endphp

    @forelse($rincian as $i => $item)
        @php
            $qty = (float) ($item['jumlah'] ?? 0);
            $diproses = isset($item['jumlah_diproses']) ? (float) $item['jumlah_diproses'] : 0;
            $sisa = max(0, $qty - $diproses);
            $totalDiajukan += $qty;
            $totalDiproses += $diproses;
        @endphp
        <tr>
            <td style="border: 1px solid #000; text-align: center;">{{ $i + 1 }}</td>
            <td style="border: 1px solid #000; font-weight: bold;">{{ $item['nama_barang'] ?? $item['deskripsi'] ?? '-' }}</td>
            <td style="border: 1px solid #000; text-align: center;">{{ $item['kode_produk'] ?? '-' }}</td>
            <td style="border: 1px solid #000; text-align: center;">{{ $item['satuan'] ?? '-' }}</td>
            <td style="border: 1px solid #000; text-align: center;">{{ $qty }}</td>
            <td style="border: 1px solid #000; text-align: center;">{{ $diproses }}</td>
            <td style="border: 1px solid #000; text-align: center;">{{ $sisa }}</td>
            <td style="border: 1px solid #000;">{{ $item['supplier'] ?? '-' }}</td>
            <td style="border: 1px solid #000;">{{ $item['keterangan'] ?? '-' }}</td>
        </tr>
    @empty
        <tr>
            <td colspan="9" style="border: 1px solid #000; text-align: center; color: #94a3b8;">Tidak ada rincian barang.</td>
        </tr>
    @endforelse

    {{-- Baris Total --}}
    @if(count($rincian) > 0)
    <tr>
        <td colspan="4" style="border: 1px solid #000; font-weight: bold; text-align: right; background-color: #f8fafc;">Total Qty</td>
        <td style="border: 1px solid #000; font-weight: bold; text-align: center; background-color: #f8fafc;">{{ $totalDiajukan }}</td>
        <td style="border: 1px solid #000; font-weight: bold; text-align: center; background-color: #f8fafc;">{{ $totalDiproses }}</td>
        <td style="border: 1px solid #000; font-weight: bold; text-align: center; background-color: #f8fafc;">{{ max(0, $totalDiajukan - $totalDiproses) }}</td>
        <td colspan="2" style="border: 1px solid #000; background-color: #f8fafc;"></td>
    </tr>
    @endif

    {{-- Data Termin Penerimaan (Jika Ada) --}}
    @php
        $termin = is_string($pengajuan->data_termin) ? json_decode($pengajuan->data_termin, true) : ($pengajuan->data_termin ?? []);
    @endphp
    @if(!empty($termin))
        <tr><td colspan="9"></td></tr>
        <tr>
            <td colspan="9" style="font-weight: bold; font-size: 11pt; background-color: #fef3c7;">DATA TERMIN PENERIMAAN BARANG</td>
        </tr>
        <tr>
            <th style="border: 1px solid #000; font-weight: bold; background-color: #fef9c3; text-align: center;">Termin</th>
            <th style="border: 1px solid #000; font-weight: bold; background-color: #fef9c3; text-align: left;">Tanggal Dibuat</th>
            <th style="border: 1px solid #000; font-weight: bold; background-color: #fef9c3; text-align: left;">Status Monitoring</th>
            <th colspan="6" style="border: 1px solid #000; font-weight: bold; background-color: #fef9c3; text-align: left;">Rincian Barang Diterima</th>
        </tr>
        @foreach($termin as $t)
            @php
                $rincianT = $t['rincian'] ?? [];
                $summaryT = collect($rincianT)->map(function ($rt) {
                    return ($rt['nama_barang'] ?? '-') . ' (' . ($rt['jumlah'] ?? 0) . ' ' . ($rt['satuan'] ?? '') . ')';
                })->implode('; ');
            @endphp
            <tr>
                <td style="border: 1px solid #000; text-align: center; font-weight: bold;">Termin {{ $t['id_termin'] ?? '-' }}</td>
                <td style="border: 1px solid #000;">{{ $t['tanggal_dibuat'] ?? '-' }}</td>
                <td style="border: 1px solid #000;">{{ $t['status_monitoring'] ?? '-' }}</td>
                <td colspan="6" style="border: 1px solid #000;">{{ $summaryT ?: '-' }}</td>
            </tr>
        @endforeach
    @endif

    {{-- Alur Persetujuan (Approval) --}}
    <tr><td colspan="9"></td></tr>
    <tr>
        <td colspan="9" style="font-weight: bold; font-size: 11pt; background-color: #e0e7ff;">ALUR PERSETUJUAN (APPROVAL)</td>
    </tr>
    <tr>
        <th style="border: 1px solid #000; font-weight: bold; background-color: #eef2ff; text-align: center;">Tahap</th>
        <th colspan="2" style="border: 1px solid #000; font-weight: bold; background-color: #eef2ff; text-align: left;">Approver</th>
        <th colspan="2" style="border: 1px solid #000; font-weight: bold; background-color: #eef2ff; text-align: left;">Jabatan</th>
        <th style="border: 1px solid #000; font-weight: bold; background-color: #eef2ff; text-align: center;">Status</th>
        <th colspan="2" style="border: 1px solid #000; font-weight: bold; background-color: #eef2ff; text-align: left;">Waktu Persetujuan</th>
        <th style="border: 1px solid #000; font-weight: bold; background-color: #eef2ff; text-align: left;">Catatan</th>
    </tr>

    @php
        $approvers = [
            ['label' => 'Tahap 1 (Atasan)', 'user' => $pengajuan->approver1, 'status' => $pengajuan->status_appr_1, 'date' => $pengajuan->tanggal_approved_1, 'catatan' => $pengajuan->catatan_approver_1],
            ['label' => 'Tahap 2 (Direktur)', 'user' => $pengajuan->approver2, 'status' => $pengajuan->status_appr_2, 'date' => $pengajuan->tanggal_approved_2, 'catatan' => $pengajuan->catatan_approver_2],
            ['label' => 'Tahap 3 (Finance)', 'user' => $pengajuan->approver3, 'status' => $pengajuan->status_appr_3, 'date' => $pengajuan->tanggal_approved_3, 'catatan' => $pengajuan->catatan_approver_3],
        ];
        if (!empty($pengajuan->approver_barang_4_id) || $pengajuan->status_appr_4) {
            $approvers[] = ['label' => 'Tahap 4 (Admin / GA)', 'user' => $pengajuan->approver4, 'status' => $pengajuan->status_appr_4, 'date' => $pengajuan->tanggal_approved_4, 'catatan' => $pengajuan->catatan_approver_4];
        }
    @endphp

    @foreach($approvers as $appr)
        <tr>
            <td style="border: 1px solid #000; font-weight: bold; text-align: center;">{{ $appr['label'] }}</td>
            <td colspan="2" style="border: 1px solid #000; font-weight: bold;">{{ $appr['user']->name ?? '-' }}</td>
            <td colspan="2" style="border: 1px solid #000;">{{ $appr['user']->jabatan ?? '-' }}</td>
            <td style="border: 1px solid #000; text-align: center; font-weight: bold;">{{ strtoupper($appr['status'] ?? 'MENUNGGU') }}</td>
            <td colspan="2" style="border: 1px solid #000;">{{ $appr['date'] ? \Carbon\Carbon::parse($appr['date'])->locale('id')->isoFormat('D MMMM YYYY, HH:mm') : '-' }}</td>
            <td style="border: 1px solid #000;">{{ $appr['catatan'] ?? '-' }}</td>
        </tr>
    @endforeach
</table>

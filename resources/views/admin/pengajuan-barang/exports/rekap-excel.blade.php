<table>
    <!-- Judul Header Atas -->
    <tr>
        <td colspan="20" style="font-weight: bold; font-size: 16px; text-align: center; vertical-align: middle;">REKAPITULASI PURCHASE ORDER</td>
    </tr>
    <tr>
        <td colspan="20" style="font-weight: bold; font-size: 12px; text-align: center; vertical-align: middle;">Bulan : {{ $bulanTahun }}</td>
    </tr>
    <tr>
        <td colspan="20"></td>
    </tr>

    <!-- Header Tabel Kompleks (Rowspan & Colspan) -->
    <tr>
        <th rowspan="2" style="border: 1px solid #000; font-weight: bold; text-align: center; vertical-align: middle;">Tanggal SPB</th>
        <th rowspan="2" style="border: 1px solid #000; font-weight: bold; text-align: center; vertical-align: middle;">Nomor SPB</th>
        <th rowspan="2" style="border: 1px solid #000; font-weight: bold; text-align: center; vertical-align: middle;">Nama Barang</th>
        <th rowspan="2" style="border: 1px solid #000; font-weight: bold; text-align: center; vertical-align: middle;">Jumlah Barang</th>
        <th rowspan="2" style="border: 1px solid #000; font-weight: bold; text-align: center; vertical-align: middle;">Satuan</th>
        <th rowspan="2" style="border: 1px solid #000; font-weight: bold; text-align: center; vertical-align: middle;">Nomor PO</th>
        <th rowspan="2" style="border: 1px solid #000; font-weight: bold; text-align: center; vertical-align: middle;">Tanggal PO</th>
        <th rowspan="2" style="border: 1px solid #000; font-weight: bold; text-align: center; vertical-align: middle;">Pemasok</th>
        <th colspan="10" style="border: 1px solid #000; font-weight: bold; text-align: center;">Penerimaan barang</th>
        <th rowspan="2" style="border: 1px solid #000; font-weight: bold; text-align: center; vertical-align: middle;">Sisa</th>
        <th rowspan="2" style="border: 1px solid #000; font-weight: bold; text-align: center; vertical-align: middle;">Keterangan</th>
    </tr>
    <tr>
        <!-- 5 Kolom Termin Penerimaan Barang -->
        @for($i = 0; $i < 5; $i++)
            <th style="border: 1px solid #000; font-weight: bold; text-align: center;">Tanggal</th>
            <th style="border: 1px solid #000; font-weight: bold; text-align: center;">Jumlah</th>
        @endfor
    </tr>

    <!-- Isi Data -->
    @foreach($pengajuanBarangs as $pengajuan)
        @php
            // Mendekode JSON rincian barang dan data termin
            $rincian = is_string($pengajuan->rincian_barang) ? json_decode($pengajuan->rincian_barang, true) : ($pengajuan->rincian_barang ?? []);
            $termin = is_string($pengajuan->data_termin) ? json_decode($pengajuan->data_termin, true) : ($pengajuan->data_termin ?? []);
            
            // Ekstrak Nomor PO & Tanggal PO dari Riwayat Monitoring
            $nomor_po = $pengajuan->nomor_po ?? '-';
            $tanggal_po = $pengajuan->tanggal_po ? \Carbon\Carbon::parse($pengajuan->tanggal_po)->locale('id')->isoFormat('D MMMM YYYY') : '-';
            
            $riwayat = is_array($pengajuan->riwayat_monitoring) ? $pengajuan->riwayat_monitoring : (json_decode($pengajuan->riwayat_monitoring, true) ?? []);
            foreach ($riwayat as $r) {
                if (stripos($r['status'] ?? '', 'terbit po') !== false) {
                    $nomor_po = !empty($r['catatan']) && $r['catatan'] !== '-' ? $r['catatan'] : $nomor_po;
                    if (!empty($r['waktu'])) {
                        $tanggal_po = trim(explode(',', $r['waktu'])[0]);
                    }
                    break;
                }
            }
        @endphp

        <!-- Looping setiap item barang di dalam satu pengajuan SPB -->
        @foreach($rincian as $index => $item)
            @php
                $jumlahBarang = floatval($item['jumlah'] ?? 0);
                $jumlahDiproses = floatval($item['jumlah_diproses'] ?? 0);
                $sisa = $jumlahBarang - $jumlahDiproses;

                // Memetakan termin penerimaan (maksimal 5 termin ke samping)
                $terimTanggal = array_fill(0, 5, null);
                $terimJumlah = array_fill(0, 5, null);
                $tIdx = 0;
                
                foreach($termin as $t) {
                    if($tIdx >= 5) break;
                    $rincianTermin = $t['rincian'] ?? [];
                    foreach($rincianTermin as $rt) {
                        // Mencocokkan berdasarkan index atau nama barang
                        if(($rt['index_barang'] ?? -1) == $index || ($rt['nama_barang'] ?? '') == ($item['nama_barang'] ?? $item['deskripsi'] ?? '')) {
                            $terimTanggal[$tIdx] = $t['tanggal_dibuat'] ?? null;
                            $terimJumlah[$tIdx] = floatval($rt['jumlah'] ?? 0);
                            $tIdx++;
                            break;
                        }
                    }
                }
            @endphp
            <tr>
                @if ($index === 0)
                    <td rowspan="{{ count($rincian) }}" style="border: 1px solid #000; vertical-align: middle;">{{ \Carbon\Carbon::parse($pengajuan->created_at)->locale('id')->isoFormat('D MMMM YYYY') }}</td>
                    <td rowspan="{{ count($rincian) }}" style="border: 1px solid #000; vertical-align: middle;">{{ $pengajuan->nomor_surat ?? '-' }}</td>
                @endif

                <td style="border: 1px solid #000;">{{ $item['nama_barang'] ?? $item['deskripsi'] ?? '-' }}</td>
                <td style="border: 1px solid #000;">{{ $jumlahBarang }}</td>
                <td style="border: 1px solid #000;">{{ $item['satuan'] ?? '-' }}</td>

                @if ($index === 0)
                    <td rowspan="{{ count($rincian) }}" style="border: 1px solid #000; vertical-align: middle;">{{ $nomor_po }}</td>
                    <td rowspan="{{ count($rincian) }}" style="border: 1px solid #000; vertical-align: middle;">{{ $tanggal_po }}</td>
                    <td rowspan="{{ count($rincian) }}" style="border: 1px solid #000; vertical-align: middle;">{{ $item['supplier'] ?? '-' }}</td>
                @endif
                
                <!-- Mencetak 5 pasang Tanggal & Jumlah Termin -->
                @for($i = 0; $i < 5; $i++)
                    @php
                        $tanggalTermin = '';
                        if ($terimTanggal[$i]) {
                            // Extract date part from "19 Agustus 2026, 09:19"
                            $tanggalTermin = trim(explode(',', $terimTanggal[$i])[0]);
                        }
                    @endphp
                    <td style="border: 1px solid #000;">{{ $tanggalTermin }}</td>
                    <td style="border: 1px solid #000;">{{ $terimJumlah[$i] ?: '' }}</td>
                @endfor

                <td style="border: 1px solid #000;">{{ $sisa }}</td>

                @if ($index === 0)
                    <td rowspan="{{ count($rincian) }}" style="border: 1px solid #000; vertical-align: middle;">{{ $pengajuan->catatan_monitoring ?? $pengajuan->catatan_pemohon ?? '' }}</td>
                @endif
            </tr>
        @endforeach
    @endforeach
</table>

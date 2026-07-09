<table>
    <thead>
    <tr>
        <th colspan="6" align="center">REKAPITULASI SALES TAHUN {{ $year }}</th>
    </tr>
    <tr>
        <th colspan="6" align="center">{{ strtoupper($client->nama_user) }} - {{ strtoupper($client->nama_perusahaan) }}</th>
    </tr>
    <tr><td colspan="6"></td></tr>

    {{-- BAGIAN INFO KLIEN & BANK --}}
    <tr>
        <td colspan="2"><b>DATA KLIEN</b></td>
        <td></td>
        <td colspan="3"><b>DATA BANK</b></td>
    </tr>
    
    {{-- Baris 1: Nama & Bank --}}
    <tr>
        <td><b>Nama Klien</b></td>
        <td>{{ $client->nama_user }}</td>
        <td></td>
        <td><b>Nama Bank</b></td>
        <td colspan="2" align="left">{{ $client->bank ?? '-' }}</td>
    </tr>

    {{-- [BARU] Baris 2: Jabatan & No. Rekening --}}
    <tr>
        <td><b>Jabatan</b></td>
        <td>{{ $client->jabatan ?? '-' }}</td>
        <td></td>
        <td><b>No. Rekening</b></td>
        <td colspan="2" align="left" style="mso-number-format:'@';">{{ $client->no_rekening ?? '-' }}</td>
    </tr>

    {{-- Baris 3: Instansi & Atas Nama --}}
    <tr>
        <td><b>Instansi</b></td>
        <td>{{ $client->nama_perusahaan }}</td>
        <td></td>
        <td><b>Atas Nama (A/N)</b></td>
        <td colspan="2" align="left">{{ $client->nama_di_rekening ?? '-' }}</td>
    </tr>

    {{-- [BARU] Baris 4: Hobby & Saldo Awal --}}
    <tr>
        <td><b>Hobby / Minat</b></td>
        <td>{{ $client->hobby_client ?? '-' }}</td>
        <td></td>
        <td><b>Saldo Awal</b></td>
        <td colspan="2">{{ $client->saldo_awal ?? 0 }}</td>
    </tr>

    {{-- Baris 5: Area (Kanan Kosong) --}}
    <tr>
        <td><b>Area</b></td>
        <td>{{ $client->area ?? '-' }}</td>
        <td></td>
        <td></td>
        <td colspan="2"></td>
    </tr>

    {{-- Baris 6: PIC Sales (Kanan Kosong) --}}
    <tr>
        <td><b>PIC Sales</b></td>
        <td>{{ $client->pic }}</td>
        <td></td>
        <td></td>
        <td colspan="2"></td>
    </tr>

    <tr>
        <td><b>Kontak</b></td>
        <td>{{ $client->email }} / {{ $client->no_telpon }}</td>
        <td></td>
        <td></td>
        <td colspan="2"></td>
    </tr>

    <tr>
        <td valign="top"><b>Alamat</b></td>
        <td colspan="5">{{ $client->alamat_user ?? $client->alamat_perusahaan ?? '-' }}</td>
    </tr>

    <tr>
        <td colspan="6" style="height: 25px;"></td> 
    </tr>

    {{-- HEADER TABEL TRANSAKSI --}}
    <tr>
        <th align="center" style="border: 1px solid #000000; background-color: #f3f4f6;">BULAN</th>
        <th align="center" style="border: 1px solid #000000; background-color: #f3f4f6;">SALES (IN)</th>
        <th align="center" style="border: 1px solid #000000; background-color: #f3f4f6;">KOMISI</th>
        <th align="center" style="border: 1px solid #000000; background-color: #f3f4f6;">VALUE (NET)</th>
        <th align="center" style="border: 1px solid #000000; background-color: #f3f4f6;">USAGE (OUT)</th>
        <th align="center" style="border: 1px solid #000000; background-color: #f3f4f6;">SALDO</th>
    </tr>
    </thead>

    {{-- BODY TABEL --}}
    <tbody>
    @foreach($recap as $r)
        <tr>
            <td style="border: 1px solid #000000;">{{ $r['month_name'] }}</td>
            <td style="border: 1px solid #000000;">{{ $r['gross_in'] }}</td>
            <td style="border: 1px solid #000000;" align="center">{{ $r['komisi_text'] }}</td>
            <td style="border: 1px solid #000000;">{{ $r['net_value'] }}</td>
            <td style="border: 1px solid #000000; color: #FF0000;">{{ $r['out'] }}</td>
            <td style="border: 1px solid #000000; font-weight: bold;">{{ $r['saldo'] }}</td>
        </tr>
    @endforeach
    </tbody>

    {{-- FOOTER TOTAL --}}
    <tfoot>
        <tr>
            <td style="border: 1px solid #000000; background-color: #e5e7eb;"><b>TOTAL TAHUNAN</b></td>
            <td style="border: 1px solid #000000; background-color: #e5e7eb;"><b>{{ $yearlyTotals['gross_in'] }}</b></td>
            <td style="border: 1px solid #000000; background-color: #e5e7eb;"></td>
            <td style="border: 1px solid #000000; background-color: #e5e7eb;"><b>{{ $yearlyTotals['net_value'] }}</b></td>
            <td style="border: 1px solid #000000; background-color: #e5e7eb; color: #FF0000;"><b>{{ $yearlyTotals['out'] }}</b></td>
            <td style="border: 1px solid #000000; background-color: #e5e7eb;"><b>{{ $yearlyTotals['saldo'] }}</b></td>
        </tr>
    </tfoot>
</table>
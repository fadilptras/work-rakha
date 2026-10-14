@extends('pdf.layouts.export-document')

@section('title', 'Berita Acara Stock Opname ' . ($namaBulan ?? ''))
@section('form-title', 'BERITA ACARA STOCK OPNAME')

@section('extra-style')
    .ba-periode { text-align: center; font-size: 11pt; margin: 0 0 12px; }
    .ba-narasi { font-size: 10pt; text-align: justify; margin: 0 0 8px; }
    .ba-identitas { width: 100%; border-collapse: collapse; margin: 0 0 8px; }
    .ba-identitas td { font-size: 10pt; padding: 1px 4px; vertical-align: top; }
    .ba-lampiran { text-align: center; font-size: 10pt; margin: 6px 0; }
    table.export-table th, table.export-table td { border: 1px solid #000; }
    table.export-table th { font-size: 8pt; padding: 4px 3px; background-color: #f1f5f9; color: #1e293b; font-weight: bold; }
    table.export-table td { font-size: 8pt; padding: 3px 4px; }
    table.export-table td.num { text-align: right; }
    table.export-table td.ctr { text-align: center; }
    table.export-table td.lft { text-align: left; }
    .ba-ttd { width: 100%; border-collapse: collapse; margin-top: 14px; }
    .ba-ttd td { width: 50%; text-align: center; font-size: 10pt; vertical-align: top; }
    .ba-ttd .nama { margin-top: 56px; font-weight: bold; }
    .ba-form-no { text-align: right; font-size: 8pt; color: #000; margin-top: 18px; }
@endsection

@section('content')
<p class="ba-periode">(PERIODE {{ \Illuminate\Support\Str::upper($namaBulan ?? '') }})</p>

<p class="ba-narasi">Pada hari ini tanggal {{ $hari ?? '-' }} bulan {{ $bulanTahun ?? '-' }}, Yang bertanda tangan di bawah ini :</p>

<table class="ba-identitas">
    <tr>
        <td style="width:12%">Nama</td>
        <td style="width:2%">:</td>
        <td>{{ $pembuat ?? '............' }}</td>
    </tr>
    <tr>
        <td>Jabatan</td>
        <td>:</td>
        <td>Gudang</td>
    </tr>
</table>

<p class="ba-narasi">Sesuai dengan jabatannya sebagai Gudang telah melakukan pemeriksaan fisik sediaan barang.</p>

<p class="ba-narasi">Berdasarkan pemeriksaan secara uji petik terhadap barang serta bukti-bukti yang ada dalam pengurusannya, kami menemui kenyataan seperti keterangan di bawah ini :</p>

<p class="ba-lampiran">(Terlampir Form Stock Opname)</p>

<table class="export-table" width="100%">
    <thead>
        <tr>
            <th style="width:5%">No</th>
            <th style="width:31%">Nama Barang</th>
            <th style="width:12%">Tercatat</th>
            <th style="width:12%">Fisik</th>
            <th style="width:12%">Selisih</th>
            <th style="width:28%">Keterangan</th>
        </tr>
    </thead>
    <tbody>
        @foreach($items as $i => $it)
        <tr>
            <td class="ctr">{{ $i + 1 }}</td>
            <td class="lft">{{ $it['produk'] }}</td>
            <td class="num">{{ $it['tercatat'] !== null ? $it['tercatat'] : '-' }}</td>
            <td class="num">{{ $it['terhitung'] !== null ? $it['terhitung'] : '-' }}</td>
            <td class="num">{{ $it['selisih'] !== null ? ($it['selisih'] > 0 ? '+' : '') . $it['selisih'] : '-' }}</td>
            <td class="lft">{{ $it['catatan'] ?: '-' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<p class="ba-narasi">Demikian Berita Acara Stock Opname ini dibuat untuk dapat dipergunakan semestinya.</p>

<table class="ba-ttd">
    <tr>
        <td>Dibuat oleh,</td>
        <td>Diperiksa oleh,</td>
    </tr>
    <tr>
        <td><div class="nama">( {{ $pembuat ?? '........................' }} )</div></td>
        <td><div class="nama">( {{ $pemeriksa ?? '........................' }} )</div></td>
    </tr>
    <tr>
        <td>Gudang</td>
        <td>Penanggung Jawab Teknis</td>
    </tr>
</table>

<p class="ba-form-no">FORM-GD-07-002</p>
@endsection

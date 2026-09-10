<table>
    {{-- Kop Surat --}}
    <tr>
        <td colspan="5" style="text-align: center; font-weight: bold; font-size: 14pt;">
            PT RAKHA NUSANTARA MEDIKA
        </td>
    </tr>
    <tr>
        <td colspan="5" style="text-align: center; font-size: 10pt;">
            Perumahan Taman Kenari Blok C2 NO. 12B Kel. Cimahpar, Kec. Bogor Utara, Kota Bogor 16155
        </td>
    </tr>
    <tr>
        <td colspan="5" style="text-align: center; font-size: 10pt;">
            Telp: +0251-2005021 | Email: rakha_medika@yahoo.com
        </td>
    </tr>
    <tr><td colspan="5"></td></tr>

    {{-- Judul --}}
    <tr>
        <td colspan="5" style="text-align: center; font-weight: bold; font-size: 13pt;">
            DAFTAR HARGA PT. RAKHA NUSANTARA MEDIKA
        </td>
    </tr>
    <tr>
        <td colspan="5" style="text-align: center;">
            Update Bulan {{ now()->translatedFormat('F Y') }}
        </td>
    </tr>
    <tr><td colspan="5"></td></tr>

    {{-- Header Tabel --}}
    <tr>
        <th style="font-weight: bold; border: 1px solid #000; background-color: #f3f4f6;">No</th>
        <th style="font-weight: bold; border: 1px solid #000; background-color: #f3f4f6;">Nama Produk</th>
        <th style="font-weight: bold; border: 1px solid #000; background-color: #f3f4f6;">Sediaan</th>
        <th style="font-weight: bold; border: 1px solid #000; background-color: #f3f4f6;">HNA</th>
        <th style="font-weight: bold; border: 1px solid #000; background-color: #f3f4f6;">HNA / PCS</th>
    </tr>

    {{-- Data Produk --}}
    @foreach($products as $i => $item)
        <tr>
            <td style="border: 1px solid #000; text-align: center;">{{ $i + 1 }}</td>
            <td style="border: 1px solid #000;">{{ $item->product_name }}</td>
            <td style="border: 1px solid #000;">{{ $item->presentation ?: 'General' }}</td>
            <td style="border: 1px solid #000; text-align: right;">
                {{ number_format($item->base_price ?? 0, 0, ',', '.') }}
            </td>
            <td style="border: 1px solid #000; text-align: right;">
                {{ number_format($item->unit_price ?? 0, 0, ',', '.') }}
            </td>
        </tr>
    @endforeach
</table>

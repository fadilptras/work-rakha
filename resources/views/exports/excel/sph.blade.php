<table>
    {{-- Kop Surat --}}
    <tr>
        <td colspan="7" style="text-align: center; font-weight: bold; font-size: 14pt;">
            PT RAKHA NUSANTARA MEDIKA
        </td>
    </tr>
    <tr>
        <td colspan="7" style="text-align: center; font-size: 10pt;">
            Perumahan Taman Kenari Blok C2 NO. 12B Kel. Cimahpar, Kec. Bogor Utara, Kota Bogor 16155
        </td>
    </tr>
    <tr>
        <td colspan="7" style="text-align: center; font-size: 10pt;">
            Telp: +0251-2005021 | Email: rakha_medika@yahoo.com
        </td>
    </tr>
    <tr><td colspan="7"></td></tr>

    {{-- Nomor & Perihal --}}
    <tr>
        <td style="font-weight: bold;">Nomor</td>
        <td>:</td>
        <td colspan="5">{{ $sph->sph_number }}</td>
    </tr>
    <tr>
        <td style="font-weight: bold;">Perihal</td>
        <td>:</td>
        <td colspan="5">Penawaran Harga</td>
    </tr>
    <tr><td colspan="7"></td></tr>

    {{-- Tujuan --}}
    <tr><td colspan="7">Kepada Yth :</td></tr>
    <tr><td colspan="7">Bapak / Ibu {{ $sph->customer_name }}</td></tr>
    <tr><td colspan="7">{{ $sph->customer_company }}</td></tr>
    <tr><td colspan="7">Di Tempat</td></tr>
    @if($sph->ps)
        <tr><td colspan="7">UP : {{ $sph->ps }}</td></tr>
    @endif
    <tr><td colspan="7"></td></tr>

    {{-- Salam --}}
    <tr><td colspan="7">Dengan Hormat,</td></tr>
    <tr>
        <td colspan="7">
            Kami PT. Rakha Nusantara Medika, mengajukan Penawaran Harga sebagai berikut :
        </td>
    </tr>
    <tr><td colspan="7"></td></tr>

    {{-- Header Tabel --}}
    <tr>
        <th style="font-weight: bold; border: 1px solid #000; background-color: #f3f4f6;">No</th>
        <th style="font-weight: bold; border: 1px solid #000; background-color: #f3f4f6;">Nama Produk</th>
        <th style="font-weight: bold; border: 1px solid #000; background-color: #f3f4f6;">Sediaan</th>
        <th style="font-weight: bold; border: 1px solid #000; background-color: #f3f4f6;">HNA Price</th>
        <th style="font-weight: bold; border: 1px solid #000; background-color: #f3f4f6;">HNA / Pcs</th>
        <th style="font-weight: bold; border: 1px solid #000; background-color: #f3f4f6;">Diskon</th>
        <th style="font-weight: bold; border: 1px solid #000; background-color: #f3f4f6;">Net+PPN</th>
    </tr>

    {{-- Data Item --}}
    @foreach($sph->items as $i => $item)
        @php
            $hna = (float) ($item['base_price'] ?? $item['unit_price']);
            $packQty = (int) ($item['pack_qty'] ?? 1);
            $discount = (float) ($item['discount'] ?? 0);
            $qty = (int) ($item['qty'] ?? 1);
            $hnaPcs = $hna / max(1, $packQty);
            $netPpn = $hna * (1 - $discount / 100) * (1 + ($sph->vat_percent / 100));
            $discountPct = rtrim(rtrim(number_format($discount, 2, ',', '.'), '0'), ',');
        @endphp
        <tr>
            <td style="border: 1px solid #000; text-align: center;">{{ $i + 1 }}</td>
            <td style="border: 1px solid #000;">{{ $item['product_name'] }}</td>
            <td style="border: 1px solid #000;">{{ $item['presentation'] ?? '-' }}</td>
            <td style="border: 1px solid #000; text-align: right;">
                {{ number_format($hna, 0, ',', '.') }}
            </td>
            <td style="border: 1px solid #000; text-align: right;">
                {{ number_format($hnaPcs, 0, ',', '.') }}
            </td>
            <td style="border: 1px solid #000; text-align: center;">
                {{ $discountPct }}%
            </td>
            <td style="border: 1px solid #000; text-align: right;">
                {{ number_format($netPpn, 0, ',', '.') }}
            </td>
        </tr>
    @endforeach
</table>

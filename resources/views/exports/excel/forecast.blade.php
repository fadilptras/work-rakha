<table>
    {{-- Kop --}}
    <tr>
        <td colspan="10" style="text-align: center; font-weight: bold; font-size: 14pt;">
            PT RAKHA NUSANTARA MEDIKA
        </td>
    </tr>
    <tr>
        <td colspan="10" style="text-align: center; font-size: 10pt;">
            Perumahan Taman Kenari Blok C2 NO. 12B Kel. Cimahpar, Kec. Bogor Utara, Kota Bogor 16155
        </td>
    </tr>
    <tr>
        <td colspan="10" style="text-align: center; font-size: 10pt;">
            Telp: +0251-2005021 | Email: rakha_medika@yahoo.com
        </td>
    </tr>
    <tr><td colspan="10"></td></tr>

    {{-- Judul --}}
    <tr>
        <td colspan="10" style="text-align: center; font-weight: bold; font-size: 13pt;">
            SALES FORECAST &amp; STOCK ESTIMATION
        </td>
    </tr>
    <tr>
        <td colspan="10" style="text-align: center;">
            Periode Acuan: {{ $bulanAktif }} {{ $tahun }} | 3 Bulan Terakhir: {{ implode(', ', array_map(fn($b) => $monthTranslations[$b] ?? $b, $tigaBulanTerakhir)) }}
        </td>
    </tr>
    <tr>
        <td colspan="10" style="text-align: center;">
            Buffer Persentase: +{{ $activePercentage }}% | Available Stock per {{ $teksStokAkhir }}
        </td>
    </tr>
    <tr><td colspan="10"></td></tr>

    {{-- Header Tabel --}}
    <tr>
        <th style="font-weight: bold; border: 1px solid #000; background-color: #f3f4f6;">No</th>
        <th style="font-weight: bold; border: 1px solid #000; background-color: #f3f4f6;">Nama Produk</th>
        @foreach($tigaBulanTerakhir as $bulan)
            <th style="font-weight: bold; border: 1px solid #000; background-color: #f3f4f6; text-align: center;">
                {{ strtoupper(substr($monthTranslations[$bulan] ?? $bulan, 0, 3)) }}
            </th>
        @endforeach
        <th style="font-weight: bold; border: 1px solid #000; background-color: #f3f4f6; text-align: center;">Total</th>
        <th style="font-weight: bold; border: 1px solid #000; background-color: #f3f4f6; text-align: center;">Average</th>
        <th style="font-weight: bold; border: 1px solid #000; background-color: #f3f4f6; text-align: center;">Forecast (+{{ $activePercentage }}%)</th>
        <th style="font-weight: bold; border: 1px solid #000; background-color: #f3f4f6; text-align: center;">Available Stock</th>
        <th style="font-weight: bold; border: 1px solid #000; background-color: #f3f4f6; text-align: center;">Suggested Order</th>
    </tr>

    {{-- Data --}}
    @foreach($stockForecast as $i => $item)
        <tr>
            <td style="border: 1px solid #000; text-align: center;">{{ $i + 1 }}</td>
            <td style="border: 1px solid #000;">
                {{ $item['nama_produk'] }}
                @if(!empty($item['ps_breakdown']))
                    ({{ implode(', ', array_map(fn($q, $ps) => $ps . ': ' . number_format($q, 0, ',', '.'), $item['ps_breakdown'], array_keys($item['ps_breakdown']))) }})
                @endif
            </td>
            @foreach($tigaBulanTerakhir as $bulan)
                <td style="border: 1px solid #000; text-align: center;">
                    {{ number_format($item['detail_bulan'][$bulan] ?? 0, 0, ',', '.') }}
                </td>
            @endforeach
            <td style="border: 1px solid #000; text-align: center;">
                {{ number_format($item['total_qty'], 0, ',', '.') }} {{ $item['satuan_sales'] }}
            </td>
            <td style="border: 1px solid #000; text-align: center;">
                {{ number_format($item['avg_qty'], 2, ',', '.') }}
            </td>
            <td style="border: 1px solid #000; text-align: center; font-weight: bold;">
                {{ number_format($item['forecast_qty'], 0, ',', '.') }} {{ $item['satuan_sales'] }}
            </td>
            <td style="border: 1px solid #000; text-align: center;">
                {{ number_format($item['stok_tersedia'], 0, ',', '.') }} {{ $item['satuan_stok'] }}
            </td>
            <td style="border: 1px solid #000; text-align: center;">
                {{ $item['suggested_order'] !== '' ? number_format($item['suggested_order'], 0, ',', '.') : '-' }}
            </td>
        </tr>
    @endforeach
</table>

<table>
    <tr>
        <td colspan="{{ count($tigaBulanTerakhir) + 10 }}" style="text-align: center; font-weight: bold; font-size: 13pt; color: #1e293b;">PT RAKHA NUSANTARA MEDIKA</td>
    </tr>
    <tr>
        <td colspan="{{ count($tigaBulanTerakhir) + 10 }}" style="text-align: center; font-size: 7pt; color: #64748b;">Perumahan Taman Kenari Blok C2 NO. 12B Kel. Cimahpar, Kec. Bogor Utara, Kota Bogor 16155 | Telp: +0251-2005021</td>
    </tr>
    <tr><td colspan="{{ count($tigaBulanTerakhir) + 10 }}"></td></tr>

    <tr>
        <td colspan="{{ count($tigaBulanTerakhir) + 10 }}" style="text-align: center; font-weight: bold; font-size: 11pt; color: #1e40af; background-color: #eff6ff; border: 1px solid #dbeafe; padding: 6px;">SALES FORECAST &amp; STOCK ESTIMATION</td>
    </tr>
    <tr>
        <td colspan="{{ count($tigaBulanTerakhir) + 10 }}" style="text-align: center; font-size: 8pt; color: #334155; border: 1px solid #e2e8f0; padding: 4px;">
            Periode: {{ $monthTranslations[$bulanAktif] ?? $bulanAktif }} {{ $tahun }} ({{ implode(', ', array_map(fn($b) => $monthTranslations[$b] ?? $b, $tigaBulanTerakhir)) }}) &nbsp;|&nbsp; Forecast +{{ rtrim(rtrim(number_format($activePercentage, 2, '.', ''), '0'), '.') }}% &nbsp;|&nbsp; Target DOI {{ $activeDoi ?? 30 }} days &nbsp;|&nbsp; Stock as of {{ $teksStokAkhir }}
        </td>
    </tr>
    <tr><td colspan="{{ count($tigaBulanTerakhir) + 10 }}"></td></tr>

    <tr>
        <th style="font-weight: bold; border: 1px solid #94a3b8; background-color: #1e293b; color: #ffffff; text-align: center; vertical-align: middle; font-size: 8pt;">No</th>
        <th style="font-weight: bold; border: 1px solid #94a3b8; background-color: #1e293b; color: #ffffff; text-align: center; vertical-align: middle; font-size: 8pt;">Product</th>
        @foreach($tigaBulanTerakhir as $bulan)
            <th style="font-weight: bold; border: 1px solid #94a3b8; background-color: #334155; color: #ffffff; text-align: center; vertical-align: middle; font-size: 8pt;">{{ strtoupper(substr($monthTranslations[$bulan] ?? $bulan, 0, 3)) }}</th>
        @endforeach
        <th style="font-weight: bold; border: 1px solid #94a3b8; background-color: #475569; color: #ffffff; text-align: center; vertical-align: middle; font-size: 8pt;">Total</th>
        <th style="font-weight: bold; border: 1px solid #94a3b8; background-color: #475569; color: #ffffff; text-align: center; vertical-align: middle; font-size: 8pt;">Average</th>
        <th style="font-weight: bold; border: 1px solid #94a3b8; background-color: #1e40af; color: #ffffff; text-align: center; vertical-align: middle; font-size: 8pt;">Forecast</th>
        <th style="font-weight: bold; border: 1px solid #94a3b8; background-color: #475569; color: #ffffff; text-align: center; vertical-align: middle; font-size: 8pt;">Buffer</th>
        <th style="font-weight: bold; border: 1px solid #94a3b8; background-color: #475569; color: #ffffff; text-align: center; vertical-align: middle; font-size: 8pt;">End Stock</th>
        <th style="font-weight: bold; border: 1px solid #94a3b8; background-color: #475569; color: #ffffff; text-align: center; vertical-align: middle; font-size: 8pt;">DOI</th>
        <th style="font-weight: bold; border: 1px solid #94a3b8; background-color: #475569; color: #ffffff; text-align: center; vertical-align: middle; font-size: 8pt;">MOQ</th>
        <th style="font-weight: bold; border: 1px solid #94a3b8; background-color: #3730a3; color: #ffffff; text-align: center; vertical-align: middle; font-size: 8pt;">Order</th>
    </tr>

    @foreach($stockForecast as $i => $item)
        <tr>
            <td style="border: 1px solid #cbd5e1; text-align: center; vertical-align: middle; font-size: 8pt;">{{ $i + 1 }}</td>
            <td style="border: 1px solid #cbd5e1; vertical-align: middle; text-align: left; font-size: 8pt;">
                {{ $item['nama_produk'] }}
                @if(!empty($item['ps_breakdown']))
                    <br><span style="font-size: 6pt; color: #64748b;">{{ implode(' | ', array_map(fn($q, $ps) => explode(' ', trim($ps))[0] . ': ' . number_format($q, 0, ',', '.'), $item['ps_breakdown'], array_keys($item['ps_breakdown']))) }}</span>
                @endif
            </td>
            @foreach($tigaBulanTerakhir as $bulan)
                <td style="border: 1px solid #e2e8f0; text-align: center; vertical-align: middle; font-size: 8pt;">{{ number_format($item['detail_bulan'][$bulan] ?? 0, 0, ',', '.') }}</td>
            @endforeach
            <td style="border: 1px solid #cbd5e1; text-align: center; vertical-align: middle; font-size: 8pt;">{{ number_format($item['total_qty'], 0, ',', '.') }}</td>
            <td style="border: 1px solid #cbd5e1; text-align: center; vertical-align: middle; font-size: 8pt;">{{ number_format($item['avg_qty'], 2, ',', '.') }}</td>
            <td style="border: 1px solid #cbd5e1; text-align: center; vertical-align: middle; font-size: 8pt; font-weight: bold; color: #1e40af;">{{ number_format($item['forecast_qty'], 0, ',', '.') }}</td>
            <td style="border: 1px solid #cbd5e1; text-align: center; vertical-align: middle; font-size: 8pt;">{{ number_format($item['buffer_qty'], 0, ',', '.') }}</td>
            <td style="border: 1px solid #cbd5e1; text-align: center; vertical-align: middle; font-size: 8pt;">{{ number_format($item['stok_tersedia'], 0, ',', '.') }}</td>
            <td style="border: 1px solid #cbd5e1; text-align: center; vertical-align: middle; font-size: 8pt;">{{ number_format($item['doi_qty'], 0, ',', '.') }} d</td>
            <td style="border: 1px solid #cbd5e1; text-align: center; vertical-align: middle; font-size: 8pt;">{{ number_format($item['moq'], 0, ',', '.') }}</td>
            <td style="border: 1px solid #cbd5e1; text-align: center; vertical-align: middle; font-size: 9pt; font-weight: bold; color: #3730a3; background-color: #eef2ff;">{{ number_format($item['order_qty'], 0, ',', '.') }}</td>
        </tr>
    @endforeach

    <tr><td colspan="{{ count($tigaBulanTerakhir) + 10 }}"></td></tr>
    @php $ex = $stockForecast[0] ?? null; @endphp
    <tr>
        <td colspan="{{ count($tigaBulanTerakhir) + 10 }}" style="font-size: 7pt; color: #334155; background-color: #f8fafc; border: 1px solid #e2e8f0; padding: 5px;">
            <b>How to read:</b> &nbsp; Average = Total / {{ $activeRefMonths }} &nbsp;|&nbsp; Forecast = Average + {{ rtrim(rtrim(number_format($activePercentage, 2, '.', ''), '0'), '.') }}% &nbsp;|&nbsp; Buffer = Average x ({{ $activeDoi ?? 30 }}/30) &nbsp;|&nbsp; DOI = (End Stock / Average) x 30 &nbsp;|&nbsp; Order = IF((End Stock - Forecast) &lt; Buffer) THEN CEILING(Buffer - (End Stock - Forecast), MOQ) ELSE 0
            @if($ex) <br><span style="color: #1e40af;">Example ({{ Str::limit($ex['nama_produk'], 25) }}): Total {{ number_format($ex['total_qty'],0,',','.') }} / {{ $activeRefMonths }} = {{ number_format($ex['avg_qty'],2,',','.') }} | Forecast {{ number_format($ex['avg_qty'],2,',','.') }} +{{ rtrim(rtrim(number_format($activePercentage, 2, '.', ''), '0'), '.') }}% = {{ number_format($ex['forecast_qty'],0,',','.') }} | Buffer {{ number_format($ex['buffer_qty'],0,',','.') }} | DOI {{ number_format($ex['doi_qty'],0,',','.') }}d | Order {{ number_format($ex['order_qty'],0,',','.') }}</span> @endif
        </td>
    </tr>
</table>


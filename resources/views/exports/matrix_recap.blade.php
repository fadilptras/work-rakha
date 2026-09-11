<table>
    <thead>
    {{-- BARIS 1-3: JUDUL --}}
    <tr>
        <th colspan="23" align="center" style="font-weight: bold; font-size: 16px;">LAPORAN SIS CONTRIBUTION {{ $year }}</th>
    </tr>
    <tr>
        <th colspan="23" align="center" style="font-weight: bold; font-size: 12px;">Exported By: {{ Auth::user()->name }}</th>
    </tr>
    <tr><td colspan="23"></td></tr> 

    {{-- BARIS 4: MAIN HEADER --}}
    <tr>
        <th rowspan="2" align="center" valign="center" style="font-weight: bold; border: 1px solid #000000; background-color: #e5e7eb; width: 5px;">NO</th>
        <th rowspan="2" align="center" valign="center" style="font-weight: bold; border: 1px solid #000000; background-color: #e5e7eb; width: 30px;">CLIENT NAME</th>
        <th rowspan="2" align="center" valign="center" style="font-weight: bold; border: 1px solid #000000; background-color: #e5e7eb; width: 25px;">PERUSAHAAN / INSTANSI</th>
        <th rowspan="2" align="center" valign="center" style="font-weight: bold; border: 1px solid #000000; background-color: #e5e7eb; width: 20px;">PIC SALES</th>
        
        <th colspan="2" align="center" style="font-weight: bold; border: 1px solid #000000; background-color: #f3f4f6;">DETAILS</th>
        <th colspan="3" align="center" style="font-weight: bold; border: 1px solid #000000; background-color: #dbeafe; color: #1e3a8a;">INCOME</th>
        <th colspan="12" align="center" style="font-weight: bold; border: 1px solid #000000; background-color: #fee2e2; color: #7f1d1d;">USAGE (PENGELUARAN)</th>
        <th colspan="2" align="center" style="font-weight: bold; border: 1px solid #000000; background-color: #d1fae5; color: #064e3b;">SUMMARY</th>
    </tr>

    {{-- BARIS 5: SUB HEADER --}}
    <tr>
        {{-- Details: Center --}}
        <th align="center" style="font-weight: bold; border: 1px solid #000000; background-color: #f3f4f6; width: 15px; text-align: center;">AREA</th>
        <th align="center" style="font-weight: bold; border: 1px solid #000000; background-color: #f3f4f6; width: 35px; text-align: left;">PRODUCT / ACTIVITY</th>

        {{-- Income: Sales(R), %(C), Net(R) --}}
        <th align="center" style="font-weight: bold; border: 1px solid #000000; background-color: #eff6ff; width: 15px; text-align: right;">SALES</th>
        <th align="center" style="font-weight: bold; border: 1px solid #000000; background-color: #eff6ff; width: 8px; text-align: center;">%</th>
        <th align="center" style="font-weight: bold; border: 1px solid #000000; background-color: #dbeafe; width: 15px; text-align: right;">NET BUDGET</th>

        {{-- Usage Months: Right --}}
        @foreach($months as $month)
            <th align="center" style="font-weight: bold; border: 1px solid #000000; background-color: #ffffff; width: 12px; text-align: right;">{{ strtoupper(substr($month, 0, 3)) }}</th>
        @endforeach

        {{-- Summary: Right --}}
        <th align="center" style="font-weight: bold; border: 1px solid #000000; background-color: #fee2e2; width: 15px; text-align: right;">TOTAL USAGE</th>
        <th align="center" style="font-weight: bold; border: 1px solid #000000; background-color: #6ee7b7; width: 15px; text-align: right;">REMAIN</th>
    </tr>
    </thead>

    {{-- BODY DATA --}}
    <tbody>
    @php
        $gtGross = 0; $gtBudget = 0; $gtUsage = 0; $gtRemain = 0;
        $gtMonthly = array_fill(1, 12, 0);
        $rowNumber = 1;
    @endphp

    @foreach($clients as $client)
        @php
            // 1. Hitung Saldo Awal (Carry Over) - Murni Saldo, dikurangi OUT saja
            $pastInteractions = $client->interactions->filter(fn($i) => \Carbon\Carbon::parse($i->interaction_date)->year < $year);
            $clientCarryOver = $client->opening_balance ?? 0;
            
            foreach($pastInteractions as $past) {
                if ($past->transaction_type == 'IN') {
                    $r = (float)($past->commission_rate ?? 0);
                    $val = $past->sales_amount > 0 ? $past->sales_amount : $past->amount;
                    $clientCarryOver += ($val * ($r/100));
                } 
                elseif ($past->transaction_type == 'OUT') {
                    $clientCarryOver -= $past->amount;
                }
            }

            // Variabel Sub-Total Per Client
            $clientSubGross = 0;
            $clientSubNet = 0;
            $clientSubUsage = 0; // Total usage REAL (yang mengurangi saldo)
            $clientSubMonthly = array_fill(1, 12, 0);

            // Filter Data Tahun Ini
            $interactions = $client->interactions->filter(fn($i) => \Carbon\Carbon::parse($i->interaction_date)->year == $year);
            
            // Grouping
            $groupedProducts = $interactions->groupBy(fn($item) => $item->product_name ?: 'General / Lainnya');
            if($groupedProducts->isEmpty() && $interactions->isEmpty()) {
                 $groupedProducts = collect();
            }
        @endphp

        {{-- === ROW 1: SALDO AWAL === --}}
        <tr>
            <td align="center" style="border: 1px solid #000000;">{{ $rowNumber }}</td>
            <td style="border: 1px solid #000000;">{{ $client->client_name }}</td>
            <td style="border: 1px solid #000000;">{{ $client->customer_name }}</td>
            <td style="border: 1px solid #000000; text-align: center;">{{ $client->ps ?? '-' }}</td>
            <td style="border: 1px solid #000000; text-align: center;">{{ $client->area }}</td>
            
            <td style="border: 1px solid #000000; font-weight: bold; color: #4b5563; background-color: #f9fafb;">
                Saldo Awal Tahun {{ $year }}
            </td>
            
            <td style="border: 1px solid #000000; text-align: right;">-</td>
            <td style="border: 1px solid #000000; text-align: center;">-</td>
            <td style="border: 1px solid #000000; text-align: right;">-</td>

            @foreach($months as $m)
                <td style="border: 1px solid #000000; text-align: right;">-</td>
            @endforeach

            <td style="border: 1px solid #000000; text-align: right;">-</td>
            <td style="border: 1px solid #000000; text-align: right; background-color: #d1fae5; font-weight: bold;">
                {{ number_format($clientCarryOver, 0, ',', '.') }}
            </td>
        </tr>

        {{-- === ROW 2..N: PRODUK & AKTIVITAS === --}}
        @foreach($groupedProducts as $productName => $items)
            @php
                $isEntertainGroup = str_starts_with((string) $productName, 'ENTERTAIN');
                
                if ($isEntertainGroup) {
                    $subGroups = $items->groupBy('notes');
                } else {
                    $subGroups = collect([$productName => $items]);
                }
            @endphp

            @foreach($subGroups as $subKey => $subItems)
                @php
                    // Sales
                    $pGross = $subItems->where('transaction_type', 'IN')->sum(fn($s) => $s->sales_amount > 0 ? $s->sales_amount : $s->amount);
                    
                    $pNetBudget = 0; $rates = [];
                    foreach($subItems->where('transaction_type', 'IN') as $s) {
                        $r = (float)($s->commission_rate ?? 0);
                        $nom = $s->sales_amount > 0 ? $s->sales_amount : $s->amount;
                        $pNetBudget += ($nom * ($r / 100));
                        if($r > 0) $rates[] = $r;
                    }
                    $rateText = (count(array_unique($rates)) > 1) ? 'Var' : ((!empty($rates)) ? $rates[0].'%' : '-');
                    
                    // Usage REAL (Hanya OUT) - Ini yang akan muncul di kolom Usage & Mengurangi Saldo
                    // Untuk Activity (ENTERTAIN), ini akan bernilai 0
                    $pRealUsage = $subItems->where('transaction_type', 'OUT')->sum('amount');
                    
                    // Usage Display (Untuk info di nama Activity)
                    $pEntertainCost = $subItems->where('transaction_type', 'ENTERTAIN')->sum('amount');

                    // Remain: Net - Usage Real (Activity 0, jadi aman)
                    $pRemain = $pNetBudget - $pRealUsage;

                    // Nama Display
                    if ($isEntertainGroup) {
                        $cleanNote = $subKey ?: 'Activity';
                        $displayName = $cleanNote . ' (Rp ' . number_format($pEntertainCost, 0, ',', '.') . ')';
                    } else {
                        $displayName = $productName;
                    }

                    // Akumulasi
                    $clientSubGross += $pGross;
                    $clientSubNet += $pNetBudget;
                    $clientSubUsage += $pRealUsage; // Hanya mengakumulasi OUT
                @endphp

                <tr>
                    <td align="center" style="border: 1px solid #000000;">{{ $rowNumber }}</td>
                    <td style="border: 1px solid #000000;">{{ $client->client_name }}</td>
                    <td style="border: 1px solid #000000;">{{ $client->customer_name }}</td>
                    <td style="border: 1px solid #000000; text-align: center;">{{ $client->ps ?? '-' }}</td>
                    <td style="border: 1px solid #000000; text-align: center;">{{ $client->area }}</td>
                    
                    <td style="border: 1px solid #000000;">{{ $displayName }}</td>
                    
                    <td style="border: 1px solid #000000; text-align: right;">{{ $pGross > 0 ? number_format($pGross, 0, ',', '.') : '-' }}</td>
                    <td style="border: 1px solid #000000; text-align: center;">{{ $rateText }}</td>
                    <td style="border: 1px solid #000000; text-align: right; background-color: #eff6ff;">{{ $pNetBudget > 0 ? number_format($pNetBudget, 0, ',', '.') : '-' }}</td>

                    @foreach($months as $mIndex => $mName)
                        @php
                            // Ambil hanya OUT (Real Usage) untuk kolom bulanan
                            $mUsage = $subItems->where('transaction_type', 'OUT')
                                               ->filter(fn($i) => \Carbon\Carbon::parse($i->interaction_date)->month == $mIndex)
                                               ->sum('amount');
                            $clientSubMonthly[$mIndex] += $mUsage;
                        @endphp
                        <td style="border: 1px solid #000000; text-align: right; {{ $mUsage > 0 ? 'color: #ef4444;' : '' }}">
                            {{-- Jika Activity, $mUsage pasti 0, jadi muncul '-' --}}
                            {{ $mUsage > 0 ? number_format($mUsage, 0, ',', '.') : '-' }}
                        </td>
                    @endforeach

                    {{-- TOTAL USAGE: Menampilkan Usage REAL saja --}}
                    {{-- Jika Activity, $pRealUsage pasti 0, jadi muncul '-' --}}
                    <td style="border: 1px solid #000000; text-align: right; background-color: #fee2e2;">
                        {{ $pRealUsage > 0 ? number_format($pRealUsage, 0, ',', '.') : '-' }}
                    </td>
                    
                    {{-- REMAIN: Right --}}
                    <td style="border: 1px solid #000000; text-align: right; color: #6b7280;">
                        {{ abs($pRemain) > 0 ? number_format($pRemain, 0, ',', '.') : '-' }}
                    </td>
                </tr>
            @endforeach
        @endforeach

        {{-- === ROW: TOTAL PER CLIENT === --}}
        @php
            $clientTotalRemain = $clientCarryOver + $clientSubNet - $clientSubUsage;
            
            $gtGross += $clientSubGross;
            $gtBudget += $clientSubNet;
            $gtUsage += $clientSubUsage; 
            $gtRemain += $clientTotalRemain;
            
            foreach($clientSubMonthly as $k => $v) {
                $gtMonthly[$k] += $v;
            }
        @endphp
        <tr>
            <td colspan="6" style="border: 1px solid #000000; background-color: #cbd5e1; font-weight: bold; text-align: right;">
                TOTAL
            </td>
            
            <td style="border: 1px solid #000000; background-color: #cbd5e1; font-weight: bold; text-align: right;">{{ number_format($clientSubGross, 0, ',', '.') }}</td>
            <td style="border: 1px solid #000000; background-color: #cbd5e1;"></td>
            <td style="border: 1px solid #000000; background-color: #cbd5e1; font-weight: bold; text-align: right;">{{ number_format($clientSubNet, 0, ',', '.') }}</td>

            @foreach($months as $mIndex => $mName)
                <td style="border: 1px solid #000000; background-color: #cbd5e1; font-weight: bold; text-align: right;">
                    {{ $clientSubMonthly[$mIndex] > 0 ? number_format($clientSubMonthly[$mIndex], 0, ',', '.') : '-' }}
                </td>
            @endforeach

            {{-- TOTAL USAGE CLIENT: Sekarang benar-benar TOTAL dari kolom Usage (hanya OUT) --}}
            {{-- Tidak tercampur angka Activity --}}
            <td style="border: 1px solid #000000; background-color: #cbd5e1; font-weight: bold; text-align: right; color: #991b1b;">
                {{ number_format($clientSubUsage, 0, ',', '.') }}
            </td>
            
            <td style="border: 1px solid #000000; background-color: #cbd5e1; font-weight: bold; text-align: right; color: #065f46;">{{ number_format($clientTotalRemain, 0, ',', '.') }}</td>
        </tr>

        {{-- Spacer Row --}}
        <tr><td colspan="23" style="background-color: #ffffff; border-left: 1px solid #000; border-right: 1px solid #000;"></td></tr>

        @php $rowNumber++; @endphp
    @endforeach
    </tbody>

    {{-- FOOTER GRAND TOTAL --}}
    <tfoot>
        <tr>
            <td colspan="6" style="border: 1px solid #000000; font-weight: bold; background-color: #1f2937; color: #ffffff; text-align: right; height: 30px; vertical-align: middle;">
                GRAND TOTAL KESELURUHAN
            </td>
            
            <td style="border: 1px solid #000000; font-weight: bold; background-color: #1f2937; color: #ffffff; text-align: right;">{{ number_format($gtGross, 0, ',', '.') }}</td>
            <td style="border: 1px solid #000000; background-color: #1f2937;"></td>
            <td style="border: 1px solid #000000; font-weight: bold; background-color: #1e40af; color: #ffffff; text-align: right;">{{ number_format($gtBudget, 0, ',', '.') }}</td>

            @foreach($months as $mIndex => $mName)
                <td style="border: 1px solid #000000; font-weight: bold; background-color: #f3f4f6; text-align: right;">{{ $gtMonthly[$mIndex] ? number_format($gtMonthly[$mIndex], 0, ',', '.') : '-' }}</td>
            @endforeach

            <td style="border: 1px solid #000000; font-weight: bold; background-color: #991b1b; color: #ffffff; text-align: right;">{{ number_format($gtUsage, 0, ',', '.') }}</td>
            <td style="border: 1px solid #000000; font-weight: bold; background-color: #065f46; color: #ffffff; text-align: right;">{{ number_format($gtRemain, 0, ',', '.') }}</td>
        </tr>
    </tfoot>
</table>
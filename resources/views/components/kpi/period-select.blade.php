{{-- Dropdown filter periode semester. Parameter: $action (string route), $period (string terpilih). --}}
<form action="{{ $action }}" method="GET" class="flex items-center gap-2">
    <label for="period" class="text-sm font-semibold text-slate-700">Periode:</label>
    <select name="period" id="period" onchange="this.form.submit()" class="bg-white border border-slate-300 text-slate-700 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 shadow-sm">
        @php
            $kpiCurrentYear = date('Y');
            $kpiPeriods = [];
            for ($i = $kpiCurrentYear - 1; $i <= $kpiCurrentYear + 1; $i++) {
                $kpiPeriods[] = "Semester 1 $i";
                $kpiPeriods[] = "Semester 2 $i";
            }
        @endphp
        @foreach($kpiPeriods as $p)
            <option value="{{ $p }}" {{ $period == $p ? 'selected' : '' }}>{{ $p }}</option>
        @endforeach
    </select>
</form>

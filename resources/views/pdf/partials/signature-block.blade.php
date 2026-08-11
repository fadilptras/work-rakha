@php $colWidth = (100 / count($approvers)) . '%'; @endphp
<table class="signatures">
    <tr>
        @foreach($approvers as $a)
        <td style="width: {{ $colWidth }}; padding: 0 5px; text-align: center;">
            <div style="font-weight: bold; margin-bottom: 5px;">{{ $a['label'] }},</div>
            <div style="font-size: 12px; color: #555; margin-bottom: 25px; font-family: 'Times New Roman', Times, serif;">{!! $a['tanggal'] ? $a['tanggal'] : '&nbsp;' !!}</div>
            
            @if($a['status'] === 'disetujui')
                <div style="font-weight: bold; color: #28a745; margin-bottom: 25px; font-family: 'Times New Roman', Times, serif;">[ DISETUJUI ]</div>
                <div style="font-weight: bold; text-decoration: underline; color: #000; font-family: 'Times New Roman', Times, serif;">{{ $a['nama'] ?? '-' }}</div>
                <div style="margin-top: 2px; font-family: 'Times New Roman', Times, serif;">{{ $a['jabatan'] ?? '-' }}</div>
            @elseif($a['status'] === 'selesai')
                <div style="font-weight: bold; color: #007bff; margin-bottom: 25px; font-family: 'Times New Roman', Times, serif;">[ SELESAI ]</div>
                <div style="font-weight: bold; text-decoration: underline; color: #000; font-family: 'Times New Roman', Times, serif;">{{ $a['nama'] ?? '-' }}</div>
                <div style="margin-top: 2px; font-family: 'Times New Roman', Times, serif;">{{ $a['jabatan'] ?? '-' }}</div>
            @elseif($a['status'] === 'ditolak')
                <div style="font-weight: bold; color: #dc3545; margin-bottom: 25px; font-family: 'Times New Roman', Times, serif;">[ DITOLAK ]</div>
                <div style="font-weight: bold; text-decoration: underline; color: #000; font-family: 'Times New Roman', Times, serif;">{{ $a['nama'] ?? '-' }}</div>
                <div style="margin-top: 2px; font-family: 'Times New Roman', Times, serif;">{{ $a['jabatan'] ?? '-' }}</div>
            @elseif($a['status'] === 'skipped')
                <div style="color: #888; font-style: italic; margin: 25px 0; font-family: 'Times New Roman', Times, serif;">( Tidak Diperlukan )</div>
            @else
                <div style="color: #888; font-style: italic; margin: 25px 0; font-family: 'Times New Roman', Times, serif;">( Menunggu Persetujuan )</div>
            @endif
        </td>
        @endforeach
    </tr>
</table>
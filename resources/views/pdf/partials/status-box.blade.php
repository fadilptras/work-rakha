@php
    $warna = match($statusKey) {
        'disetujui', 'selesai' => 'status-hijau',
        'ditolak' => 'status-merah',
        'dibatalkan' => 'status-abu',
        default => 'status-kuning',
    };
@endphp
<div class="status-box {{ $warna }}">STATUS AKHIR: {{ strtoupper($statusLabel ?? $statusKey) }}</div>
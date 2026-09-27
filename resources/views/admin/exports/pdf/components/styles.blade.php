{{-- Template PDF seragam: dipakai semua laporan PDF admin. --}}
<style>
    body {
        font-family: 'Times New Roman', Times, serif;
        font-size: 10px;
        color: #333;
    }

    /* JUDUL LAPORAN (di bawah kop surat resmi) */
    .kop-laporan {
        text-align: center;
        margin-bottom: 12px;
    }
    .kop-laporan-judul {
        margin: 0;
        font-size: 17px;
        text-transform: uppercase;
        color: #1e3a8a;
        font-weight: 800;
    }
    .kop-laporan-periode {
        margin-top: 4px;
        font-size: 10.5px;
        color: #333;
    }
    .kop-info-box {
        border: 1px solid #d1d5db;
        background-color: #f8fafc;
        padding: 5px 8px;
        font-size: 8.5px;
        color: #374151;
        margin-bottom: 12px;
        text-align: center;
    }
    .kop-info-sep { margin: 0 8px; color: #9ca3af; }

    /* TABEL LAPORAN */
    table.report {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 10px;
        background-color: white;
    }
    table.report th, table.report td {
        border: 1px solid #d1d5db;
        padding: 5px 4px;
        vertical-align: middle;
        word-wrap: break-word;
    }
    table.report thead th {
        background-color: #1e3a8a;
        color: white;
        text-transform: uppercase;
        font-weight: bold;
        text-align: center;
    }
    table.report tbody tr:nth-child(even) td { background-color: #f8fafc; }

    /* JUDUL SEKSI (formal: rata kiri, tanpa garis) */
    .section-title {
        font-size: 13px;
        font-weight: 800;
        color: #1e3a8a;
        text-transform: uppercase;
        text-align: left;
        letter-spacing: 1px;
        margin: 16px 0 8px 0;
    }

    /* LEGENDA */
    .legend {
        margin-top: 10px;
        font-size: 8px;
        color: #374151;
        line-height: 1.6;
    }
    .legend strong { color: #1e3a8a; }
    .legend-item { margin-right: 12px; }
    .legend-list { margin: 3px 0 0 16px; padding: 0; }
    .legend-list li { margin-bottom: 1px; }

    /* NOMOR DOKUMEN (kanan bawah) */
    .nomor-dokumen { text-align: right; margin-top: 16px; }
    .nomor-dokumen span {
        display: inline-block;
        border: 1px solid #6b7280;
        padding: 3px 10px;
        font-size: 8.5px;
        font-weight: bold;
        color: #374151;
        letter-spacing: 0.5px;
    }

    /* TABEL MINGGUAN (dipakai rekap absensi & lembur) */
    table.week-table thead th { font-size: 9.5px; padding: 7px 6px; }
    table.week-table tbody tr:nth-child(even) td { background-color: #f8fafc; }

    .col-no { width: 4%; text-align: center; }
    .col-nama { width: 24%; text-align: left; }

    .name-text { font-weight: bold; color: #1e3a8a; font-size: 11px; display: block; text-align: left; }
    .jabatan-text { display: block; text-align: left; color: #64748b; font-style: italic; font-size: 9px; }

    .day-num { display: block; font-size: 11px; font-weight: 800; line-height: 1.2; }
    .day-name { display: block; font-size: 8.5px; font-weight: normal; text-transform: uppercase; line-height: 1.2; }
    th.th-libur { background-color: #b91c1c !important; }
    th.th-sabtu { background-color: #475569 !important; }

    .week-title { font-size: 11px; font-weight: bold; color: #374151; margin: 12px 0 4px 0; }
    .empty-note { font-style: italic; color: #6b7280; margin: 2px 0 8px 0; }

    td.cell-kosong { color: #9ca3af; text-align: center; }

</style>

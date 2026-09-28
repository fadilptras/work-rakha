# Rancangan Penyatuan Export PDF Admin

Tanggal: 27 September 2026
Status: Tahap 1 (absensi + lembur) dieksekusi. Aktivitas dirapikan menyusul. Tahap 2 (cuti, dana, barang) menyusul.

## Masalah

9 file PDF admin tersebar di 5 folder dengan 2 gaya visual berbeda (navy vs abu-abu),
kop/tutup/style diduplikat mentah di tiap file (±60–90 baris duplikat per file),
dan penamaan file tidak konsisten (`absensi_rekap` vs `rekap_pdf` vs `pdf_rekap`).

## Struktur target

Semua PDF admin tinggal di **satu folder**: `resources/views/admin/exports/pdf/`

```
admin/exports/pdf/   (hanya folder components yang Inggris)
├── layout.blade.php                  # kerangka induk (yield: kop-*, konten, legenda)
├── components/
│   ├── styles.blade.php              # CSS seragam
│   ├── header.blade.php              # kop surat resmi (reuse pdf.partials.kop-surat) + judul laporan
│   ├── employee-cell.blade.php       # sel nama + sub (nama, sub)
│   └── date-header.blade.php         # th tanggal mingguan (tanggal, holidays)
├── absensi/
│   ├── rekap.blade.php               # ex absensi_rekap.blade.php
│   └── harian.blade.php              # ex absensi_harian.blade.php (kolom Lembur basi dibuang)
├── lembur/
│   ├── rekap.blade.php               # ex admin/lembur/rekap_pdf.blade.php
│   └── harian.blade.php              # ex admin/lembur/pdf.blade.php
└── aktivitas/
    └── rekap.blade.php               # ex aktivitas.blade.php (layout induk + komponen)
```

| View baru | Controller |
|---|---|
| `admin.exports.pdf.absensi.rekap` | `AdminAbsensiController@downloadPdf` |
| `admin.exports.pdf.absensi.harian` | `AdminAbsensiController@downloadPdfHarian` |
| `admin.exports.pdf.lembur.rekap` | `AdminLemburController@downloadRekapPdf` |
| `admin.exports.pdf.lembur.harian` | `AdminLemburController@downloadPdf` |
| `admin.exports.pdf.aktivitas.rekap` | `AdminAktivitasController@downloadPdf` |
| `rekap-cuti.blade.php` (tahap 2) | `admin/cuti/pdf_rekap.blade.php` | `AdminCutiController@downloadRekapPDF` |
| `sisa-cuti.blade.php` (tahap 2) | `admin/cuti/pdf_pengaturan.blade.php` | `AdminCutiController@downloadPengaturanPDF` |
| `rekap-dana.blade.php` (tahap 2) | `admin/pengajuan-dana/pdf_rekap.blade.php` | `AdminPengajuanDanaController@downloadRekapPDF` |
| `rekap-barang.blade.php` (tahap 2) | `admin/pengajuan-barang/pdf_rekap.blade.php` | `AdminPengajuanBarangController@downloadRekapPDF` |

## Layout induk (`layout.blade.php`)

Kerangka HTML + `@page` (orientasi & margin bisa di-override per laporan via
`@section('orientasi')` / `@section('margin-pdf')`) + `_styles` + `_kop`
(params via `@section('kop-judul'/'kop-periode'/'kop-info')`) + `@yield('konten')`
+ `@yield('legenda')` (opsional) + `_tutup` (stempel cetak + nomor halaman).

## Komponen bersama mingguan (dipakai 2 file rekap)

- `_sel-karyawan.blade.php` — `($nama, $sub)` → span nama + jabatan/divisi.
- `_th-tanggal.blade.php` — `($tanggal, $holidays)` → th nomor + nama hari +
  pewarnaan libur/Sabtu. CSS umum (`col-no/nama`, `day-num/name`, `th-libur/sabtu`,
  `week-title`, zebra `week-table`) dipindah ke `_styles`.

## Batasan cakupan (sengaja tidak disentuh)

- `pdf.documents.*` (surat pengajuan cuti/barang/dana, profil) — dokumen surat, bukan rekap.
- PDF sisi user (`forecast`, KPI, CRM, dsb.).

## Checklist deploy tahap 1

Upload: `layout.blade.php`, `_sel-karyawan`, `_th-tanggal`, 4 file laporan baru,
2 controller (Absensi, Lembur). Hapus 4 file lama. Lalu `php artisan view:clear`.
Tanpa migrate.

## Export Excel (struktur mirror + multi-sheet ala Odoo)

`app/Exports/` dirapikan per domain (namespace mengikuti folder):

```
app/Exports/
├── Concerns/
│   └── LaporanSheet.php         # trait format reusable (kop, zebra, cetak, nomor)
├── Absensi/
│   ├── RekapAbsensiExport.php   # parent WithMultipleSheets (ex single-sheet matrix)
│   │   ├── RekapAbsensiBulananSheet  # agregat + TOTAL rumus SUM
│   │   └── RekapAbsensiMingguanSheet # 7 kolom tanggal per minggu
│   └── HarianAbsensiExport.php  # ex AbsensiHarianExport.php (freeze/filter/cetak)
└── Lembur/
    └── RekapLemburExport.php    # parent + RekapLemburBulananSheet + RekapLemburMingguanSheet
└── Aktivitas/
    └── RekapAktivitasExport.php  # ex AktivitasExport.php (trait LaporanSheet, freeze/filter/cetak)
```

Pola mengikuti OCA `report_xlsx`: workbook multi-sheet (ringkasan + periode),
format reusable via trait, nilai mentah + numberFormat, freeze + autofilter,
print setup (landscape, fit-lebar, ulang judul, footer nomor+halaman),
baris TOTAL Grand, nomor dokumen kanan bawah + footer.
Controller tidak berubah (nama class & constructor sama).
Slot nomor harian (`HarianAbsensiExport::$nomorDokumen`) menunggu nomor dari user.
Catatan deploy: PSR-4 resolve otomatis tanpa `composer dump-autoload`.

Export lain (Forecast, dsb.) belum dipindah — di luar cakupan tahap ini.

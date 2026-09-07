<?php

namespace App\Imports;

use App\Models\Barang;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use Exception;

class StockImport implements ToCollection, WithCalculatedFormulas
{
    /**
     * @var array Menyimpan data produk yang berhasil diekstraksi dari Excel.
     */
    public $extractedData = [];

    /**
     * @var string|null Menyimpan tanggal pencetakan dokumen Excel dari Accurate.
     */
    public $tercetakDate = null;

    /**
     * Proses parsing baris demi baris dari dokumen Excel Accurate.
     *
     * @param Collection $rows
     * @throws Exception
     */
    public function collection(Collection $rows)
    {
        $tercetak_date = null;
        $extracted_data = [];

        // Deteksi format: template sistem (product_code, product_name, stock, stock_po)
        // atau laporan kertas kerja Accurate (Kolom C=Nama, D=Kode, K=Saldo Akhir)
        $isTemplateFormat = false;
        foreach ($rows->take(30) as $row) {
            $c = array_values(is_array($row) ? $row : $row->toArray());
            $head = strtolower(trim((string) ($c[0] ?? '')));
            if ($head === 'product_code' || in_array(strtolower(trim((string) ($c[1] ?? ''))), ['product_name', 'nama barang'])) {
                $isTemplateFormat = true;
                break;
            }
        }

        foreach ($rows as $row) {
            $rowArray = array_values(is_array($row) ? $row : $row->toArray());

            if ($isTemplateFormat) {
                // Format template sistem: A=Kode, B=Nama, C=Stok, D=Stok PO
                if (empty($rowArray[0]) || strtolower(trim((string) $rowArray[0])) === 'product_code') {
                    continue;
                }
                $nama_barang = $rowArray[1] ?? null;
                $kode_barang = trim((string) ($rowArray[0] ?? ''));
                $stok_akhir  = $rowArray[2] ?? null;
            } else {
                // Format laporan Accurate: cari tanggal cetak di Kolom B (indeks 1)
                if (is_null($tercetak_date) && !empty($rowArray[1]) && str_contains((string) $rowArray[1], 'Tercetak pada')) {
                    $tercetak_date = trim((string) $rowArray[1]);
                }

                // Petakan koordinat sel data barang dari Accurate
                $nama_barang = $rowArray[2] ?? null;  // Kolom C: Nama Barang
                $kode_barang = $rowArray[3] ?? null;  // Kolom D: Kode Barang
                $stok_akhir  = $rowArray[10] ?? null; // Kolom K: Saldo Akhir (Qty)
            }

            // Saring baris header, total, atau baris kosong bawaan paginasi Accurate
            if (empty($nama_barang) || $nama_barang === 'Nama Barang' || str_contains(strtolower((string) $nama_barang), 'total nama barang')) {
                continue;
            }

            // Pastikan data memiliki nilai stok yang valid (numerik).
            // Kode barang boleh kosong pada format template (match by nama);
            // pada format Accurate kode wajib terisi.
            if (!is_numeric($stok_akhir)) {
                continue;
            }
            if (!$isTemplateFormat && empty($kode_barang)) {
                continue;
            }

            // Bersihkan spasi ganda tak terlihat pada kode barang hasil ekspor Accurate
            $kode_barang_bersih = preg_replace('/\s+/', ' ', trim((string) $kode_barang));

            $extracted_data[] = [
                'nama' => trim((string) $nama_barang),
                'kode' => $kode_barang_bersih,
                'stok' => (int) $stok_akhir,
            ];
        }

        // Lempar exception jika tidak ada satu pun barang yang lolos kualifikasi impor
        if (empty($extracted_data)) {
            throw new Exception("Data barang tidak ditemukan. Pastikan format file Excel sesuai standar laporan Accurate atau template yang disediakan, dan kode/nama barang telah terdaftar.");
        }

        $this->extractedData = $extracted_data;
        $this->tercetakDate = $tercetak_date;
    }
}

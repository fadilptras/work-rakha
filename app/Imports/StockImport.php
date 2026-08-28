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

        foreach ($rows as $row) {
            $rowArray = $row->toArray();

            // Ekstrak info tanggal pencetakan dari paginasi Accurate (Kolom B / Indeks 1)
            if (is_null($tercetak_date) && !empty($rowArray[1]) && str_contains((string) $rowArray[1], 'Tercetak pada')) {
                $tercetak_date = trim((string) $rowArray[1]);
            }

            // Petakan koordinat sel data barang dari Accurate
            $nama_barang = $rowArray[2] ?? null;  // Kolom C: Nama Barang
            $kode_barang = $rowArray[3] ?? null;  // Kolom D: Kode Barang
            $stok_akhir  = $rowArray[10] ?? null; // Kolom K: Saldo Akhir (Qty)

            // Saring baris header, total, atau baris kosong bawaan paginasi Accurate
            if (empty($nama_barang) || $nama_barang === 'Nama Barang' || str_contains(strtolower((string) $nama_barang), 'total nama barang')) {
                continue;
            }

            // Pastikan data memiliki kode barang dan nilai stok yang valid (numerik)
            if (empty($kode_barang) || !is_numeric($stok_akhir)) {
                continue;
            }

            // Bersihkan spasi ganda tak terlihat pada kode barang hasil ekspor Accurate
            $kode_barang_bersih = preg_replace('/\s+/', ' ', trim((string) $kode_barang));

            // Hanya izinkan barang yang kode barangnya terdaftar di database sistem
            $exists = Barang::where('kode_barang', $kode_barang_bersih)->exists();
            if (!$exists) {
                continue;
            }

            $extracted_data[] = [
                'nama' => trim((string) $nama_barang),
                'kode' => $kode_barang_bersih,
                'stok' => (int) $stok_akhir,
            ];
        }

        // Lempar exception jika tidak ada satu pun barang yang lolos kualifikasi impor
        if (empty($extracted_data)) {
            throw new Exception("Data barang tidak ditemukan. Pastikan format file Excel sesuai standar laporan Accurate dan kode barang telah terdaftar.");
        }

        $this->extractedData = $extracted_data;
        $this->tercetakDate = $tercetak_date;
    }
}

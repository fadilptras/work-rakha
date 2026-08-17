<?php

namespace App\Imports;

use App\Models\Sales;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class SalesImport implements ToCollection, WithHeadingRow
{
    public $importedCount = 0;
    public $refreshedMonths = [];

    /**
     * @param Collection $rows
     */
    public function collection(Collection $rows)
    {
        $monthsToRefresh = [];
        $salesToInsert = [];

        foreach ($rows as $row) {
            // Skip baris yang benar-benar kosong
            if (empty($row['nama_customer']) && empty($row['nama_produk'])) {
                continue;
            }

            // Parsing tanggal (handle format serial excel dan string biasa)
            $tanggal = null;
            if (isset($row['tanggal'])) {
                if (is_numeric($row['tanggal'])) {
                    $tanggal = Date::excelToDateTimeObject($row['tanggal'])->format('Y-m-d');
                } else {
                    $parsedDate = strtotime($row['tanggal']);
                    if ($parsedDate !== false) {
                        $tanggal = date('Y-m-d', $parsedDate);
                    }
                }
            }

            // Jika tanggal tidak valid, lewati baris ini
            if (!$tanggal) {
                continue;
            }

            $bulanString = ['01'=>'Januari','02'=>'Februari','03'=>'Maret','04'=>'April','05'=>'Mei','06'=>'Juni','07'=>'Juli','08'=>'Agustus','09'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember'][date('m', strtotime($tanggal))];
            $tahun = date('Y', strtotime($tanggal));

            // Simpan kombinasi unik (tahun-bulan) untuk mendeteksi bulan apa saja yang perlu dihapus/direfresh
            $key = $tahun . '-' . $bulanString;
            if (!isset($monthsToRefresh[$key])) {
                $monthsToRefresh[$key] = [
                    'tahun' => $tahun,
                    'bulan' => $bulanString
                ];
            }

            $qty = (isset($row['qty']) && trim($row['qty']) !== '') ? intval($row['qty']) : null;
            $hna = (isset($row['hna']) && trim($row['hna']) !== '') ? floatval(str_replace(',', '', $row['hna'])) : 0;
            
            $diskon = null;
            if (isset($row['diskon']) && trim($row['diskon']) !== '') {
                $diskon = floatval(str_replace(',', '', $row['diskon']));
                if ($diskon <= 1 && $diskon > 0) {
                    $diskon = $diskon * 100;
                }
            }
            
            $harga_nett = (isset($row['harga_nett']) && trim($row['harga_nett']) !== '') ? floatval(str_replace(',', '', $row['harga_nett'])) : null;

            $salesToInsert[] = [
                'tanggal'       => $tanggal,
                'nama_customer' => $row['nama_customer'] ?? null,
                'nama_produk'   => $row['nama_produk'] ?? null,
                'qty'           => $qty,
                'satuan'        => $row['satuan'] ?? null,
                'hna'           => $hna,
                'diskon'        => $diskon,
                'harga_nett'    => $harga_nett,
                'bulan'         => $bulanString,
                'ps'            => $row['ps'] ?? null,
                'created_at'    => now(),
                'updated_at'    => now(),
            ];
        }

        // Set tracking variables
        $this->importedCount = count($salesToInsert);
        $this->refreshedMonths = array_keys($monthsToRefresh);

        // Gunakan database transaction agar aman
        DB::transaction(function () use ($monthsToRefresh, $salesToInsert) {
            // 1. Hapus semua data yang ada sebelumnya untuk bulan & tahun yang terdeteksi di Excel
            foreach ($monthsToRefresh as $m) {
                Sales::whereYear('tanggal', $m['tahun'])
                     ->where('bulan', $m['bulan'])
                     ->delete();
            }

            // 2. Insert seluruh data dari Excel secara massal (batch per 1000 baris agar ringan)
            $chunks = array_chunk($salesToInsert, 1000);
            foreach ($chunks as $chunk) {
                Sales::insert($chunk);
            }
        });
    }
}

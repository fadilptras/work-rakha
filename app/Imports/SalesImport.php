<?php

namespace App\Imports;

use App\Models\Sales;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class SalesImport implements ToModel, WithHeadingRow, WithChunkReading, WithBatchInserts
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        // Skip baris yang benar-benar kosong
        if (empty($row['nama_customer']) && empty($row['nama_produk'])) {
            return null;
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

        return new Sales([
            'tanggal'       => $tanggal,
            'nama_customer' => $row['nama_customer'] ?? null,
            'nama_produk'   => $row['nama_produk'] ?? null,
            'qty'           => $qty,
            'satuan'        => $row['satuan'] ?? null,
            'hna'           => $hna,
            'diskon'        => $diskon,
            'harga_nett'    => $harga_nett,
            'bulan'         => $tanggal ? ['01'=>'Januari','02'=>'Februari','03'=>'Maret','04'=>'April','05'=>'Mei','06'=>'Juni','07'=>'Juli','08'=>'Agustus','09'=>'September','10'=>'Oktober','11'=>'November','12'=>'Desember'][date('m', strtotime($tanggal))] : null,
            'ps'            => $row['ps'] ?? null,
        ]);
    }

    public function batchSize(): int
    {
        return 1000;
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}

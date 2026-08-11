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

        return new Sales([
            'tanggal'       => $tanggal,
            'nama_customer' => $row['nama_customer'] ?? null,
            'nama_produk'   => $row['nama_produk'] ?? null,
            'qty'           => isset($row['qty']) ? intval($row['qty']) : null,
            'satuan'        => $row['satuan'] ?? null,
            'hna'           => $row['hna'] ?? 0,
            'diskon'        => isset($row['diskon']) && is_numeric($row['diskon']) ? ($row['diskon'] <= 1 ? $row['diskon'] * 100 : $row['diskon']) : 0,
            'harga_nett'    => isset($row['harga_nett']) ? floatval($row['harga_nett']) : null,
            'bulan'         => $row['bulan'] ?? null,
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

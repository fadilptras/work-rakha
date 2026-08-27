<?php

namespace App\Imports;

use App\Models\Barang;
use App\Models\StockLog;
use App\Models\StockLogDetail;
use App\Models\DailyStockHistory;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;

class StockImport implements ToCollection
{
    public $importedCount = 0;
    public $logId = null;

    public function collection(Collection $rows)
    {
        $user = Auth::user();
        
        DB::transaction(function () use ($rows, $user) {
            $headerRowIndex = null;
            $colKode = null;
            $colNama = null;
            $colStok = null;
            $colStokPo = null;

            // Step 1: Scan for the header row
            foreach ($rows as $index => $row) {
                $rowArray = $row->toArray();
                
                // Normalize cell values to lowercase strings
                $normalizedCells = array_map(function($cell) {
                    return strtolower(trim(strval($cell)));
                }, $rowArray);

                $foundKode = false;
                $foundNama = false;
                $foundStok = false;

                foreach ($normalizedCells as $colIdx => $val) {
                    if (empty($val)) continue;

                    // Match Kode Barang
                    if (in_array($val, ['kode', 'kode barang', 'kode produk', 'no. barang', 'no barang', 'item code', 'no.']) || str_contains($val, 'kode barang') || str_contains($val, 'no. barang') || str_contains($val, 'item code')) {
                        $colKode = $colIdx;
                        $foundKode = true;
                    }
                    // Match Nama Barang
                    if (in_array($val, ['nama', 'nama barang', 'nama produk', 'deskripsi', 'item name', 'description', 'nama barang / jasa']) || str_contains($val, 'nama barang') || str_contains($val, 'deskripsi') || str_contains($val, 'item name')) {
                        $colNama = $colIdx;
                        $foundNama = true;
                    }
                    // Match Stok
                    if (in_array($val, ['stok', 'stok tersedia', 'tersedia', 'qty', 'quantity', 'kuantitas', 'saldo', 'stok sistem', 'on hand', 'stok akhir']) || str_contains($val, 'stok tersedia') || str_contains($val, 'kuantitas') || str_contains($val, 'stok akhir')) {
                        $colStok = $colIdx;
                        $foundStok = true;
                    }
                    // Match PO (optional)
                    if (in_array($val, ['po', 'po qty', 'sedang dikirim', 'sedang dikirim po', 'pesanan', 'on po', 'outstanding po', 'po stok']) || str_contains($val, 'sedang dikirim') || str_contains($val, 'pesanan') || str_contains($val, 'po qty')) {
                        $colStokPo = $colIdx;
                    }
                }

                // Header found if we locate code/name and stock column
                if (($foundKode || $foundNama) && $foundStok) {
                    $headerRowIndex = $index;
                    break;
                }
            }

            // Fallback columns if header row is not found
            if ($headerRowIndex === null) {
                $colKode = 0;
                $colNama = 1;
                $colStok = 2;
                $colStokPo = 3;
                $headerRowIndex = 0;
            }

            // Step 2: Parse data rows after the header row
            $validRows = [];
            for ($i = $headerRowIndex + 1; $i < $rows->count(); $i++) {
                $rowArray = $rows[$i]->toArray();

                $kodeVal = isset($rowArray[$colKode]) ? trim(strval($rowArray[$colKode])) : '';
                $namaVal = isset($rowArray[$colNama]) ? trim(strval($rowArray[$colNama])) : '';
                $stokVal = isset($rowArray[$colStok]) ? trim(strval($rowArray[$colStok])) : '0';
                $stokPoVal = ($colStokPo !== null && isset($rowArray[$colStokPo])) ? trim(strval($rowArray[$colStokPo])) : '0';

                // Skip summary/footer rows
                $normalizedNama = strtolower($namaVal);
                $normalizedKode = strtolower($kodeVal);
                if (
                    (empty($namaVal) && empty($kodeVal)) ||
                    str_starts_with($normalizedNama, 'total') ||
                    str_starts_with($normalizedNama, 'grand total') ||
                    str_starts_with($normalizedNama, 'subtotal') ||
                    str_starts_with($normalizedKode, 'total') ||
                    str_starts_with($normalizedKode, 'grand total') ||
                    str_starts_with($normalizedKode, 'subtotal')
                ) {
                    continue;
                }

                // Extract numeric values, strip out commas or periods
                $cleanStok = filter_var($stokVal, FILTER_SANITIZE_NUMBER_INT);
                $cleanStokPo = filter_var($stokPoVal, FILTER_SANITIZE_NUMBER_INT);

                $validRows[] = [
                    'kode' => $kodeVal,
                    'nama' => $namaVal,
                    'stok' => $cleanStok !== false && $cleanStok !== '' ? intval($cleanStok) : 0,
                    'stok_po' => $cleanStokPo !== false && $cleanStokPo !== '' ? intval($cleanStokPo) : 0,
                ];
            }

            if (empty($validRows)) {
                return;
            }

            // Create StockLog
            $log = StockLog::create([
                'user_id' => $user ? $user->id : null,
                'source' => 'excel',
                'items_count' => count($validRows),
                'status' => 'success',
            ]);

            $this->logId = $log->id;

            foreach ($validRows as $row) {
                $kodeBarang = $row['kode'] ?: null;
                $namaBarang = $row['nama'] ?: null;
                $newStok = $row['stok'];
                $newStokPo = $row['stok_po'];

                $barang = null;
                if ($kodeBarang) {
                    $barang = Barang::where('kode_barang', $kodeBarang)->first();
                }
                if (!$barang && $namaBarang) {
                    $barang = Barang::where('nama_barang', $namaBarang)->first();
                }

                // Auto-create new product if it does not exist
                if (!$barang && $namaBarang) {
                    $barang = Barang::create([
                        'kode_barang' => $kodeBarang,
                        'nama_barang' => $namaBarang,
                        'stok' => 0,
                        'stok_po' => 0,
                    ]);
                }

                if ($barang) {
                    $oldStok = $barang->stok;
                    $oldStokPo = $barang->stok_po;

                    // Detail Log
                    StockLogDetail::create([
                        'stock_log_id' => $log->id,
                        'barang_id' => $barang->id,
                        'old_stok' => $oldStok,
                        'new_stok' => $newStok,
                        'old_stok_po' => $oldStokPo,
                        'new_stok_po' => $newStokPo,
                    ]);

                    // Update Barang
                    $barang->update([
                        'stok' => $newStok,
                        'stok_po' => $newStokPo,
                    ]);

                    // Daily History
                    DailyStockHistory::updateOrCreate(
                        ['barang_id' => $barang->id, 'tanggal' => date('Y-m-d')],
                        ['stok' => $newStok, 'stok_po' => $newStokPo]
                    );

                    $this->importedCount++;
                }
            }
        });
    }
}

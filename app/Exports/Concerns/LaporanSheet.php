<?php

namespace App\Exports\Concerns;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\RichText\RichText;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Komponen bersama export Excel laporan (pola OCA report_xlsx: format reusable).
 * Dipakai sheet absensi & lembur (rekap bulanan, mingguan, harian).
 */
trait LaporanSheet
{
    /**
     * Baris kop standar: perusahaan, judul, periode/info, baris kosong.
     */
    protected function barisKop(string $judul, string $periode): array
    {
        return [
            ['PT RAKHA NUSANTARA MEDIKA'],
            [$judul],
            [$periode],
            [''],
        ];
    }

    /**
     * Gaya font 3 baris kop (dipakai di styles()).
     * PENTING: gabungkan dengan operator union (+) bukan array_merge(),
     * karena array_merge() menomori ulang key numerik (Maatwebsite butuh key = nomor baris).
     */
    protected function gayaKop(): array
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 16]],
            2 => ['font' => ['bold' => true, 'size' => 12]],
            3 => ['font' => ['bold' => true, 'size' => 10]],
        ];
    }

    /**
     * Merge + tengahkan baris kop (1 s/d $barisKop) selebar $totalKolom.
     */
    protected function terapkanKop(Worksheet $sheet, int $totalKolom, int $barisKop = 3): void
    {
        $last = Coordinate::stringFromColumnIndex($totalKolom);
        for ($r = 1; $r <= $barisKop; $r++) {
            $sheet->mergeCells("A{$r}:{$last}{$r}");
        }
        $sheet->getStyle("A1:{$last}{$barisKop}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("A1:{$last}{$barisKop}")->getFont()->setColor(new Color('FF1E3A8A'));
    }

    /**
     * Belang-belang baris data (abu sangat muda di baris genap).
     */
    protected function zebra(Worksheet $sheet, int $barisAwal, int $barisAkhir, string $kolomAkhir): void
    {
        for ($r = $barisAwal; $r <= $barisAkhir; $r++) {
            if ($r % 2 === 0) {
                $sheet->getStyle("A{$r}:{$kolomAkhir}{$r}")->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFF8FAFC');
            }
        }
    }

    /**
     * Border tipis + wrap untuk area data.
     */
    protected function borderData(Worksheet $sheet, string $range): void
    {
        $sheet->getStyle($range)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'FFD1D5DB'],
                ],
            ],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
        ]);
    }

    /**
     * Setup cetak: landscape, muat 1 halaman lebar, ulang baris judul,
     * footer nomor dokumen + halaman.
     */
    protected function setupCetak(Worksheet $sheet, int $barisJudulAwal, int $barisJudulAkhir, ?string $nomorDokumen = null): void
    {
        $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
        $sheet->getPageSetup()->setFitToPage(true);
        $sheet->getPageSetup()->setFitToWidth(1);
        $sheet->getPageSetup()->setFitToHeight(0);
        $sheet->getPageMargins()->setTop(0.4);
        $sheet->getPageMargins()->setBottom(0.4);
        $sheet->getPageMargins()->setLeft(0.4);
        $sheet->getPageMargins()->setRight(0.4);
        $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd($barisJudulAwal, $barisJudulAkhir);
        $kaki = $nomorDokumen ? $nomorDokumen . '   |   ' : '';
        $sheet->getHeaderFooter()->setOddFooter('&R' . $kaki . 'Halaman &P dari &N');
    }

    /**
     * Teks kaya multi-baris: tiap baris bisa beda tebal/warna/ukuran,
     * agar info penting (mis. Telat) tidak nyaru dengan teks lain dalam 1 sel.
     * Format $baris: [['teks' => ..., 'bold' => true, 'color' => 'FF....', 'size' => 11], ...]
     */
    protected function richTeks(array $baris): RichText
    {
        $rt = new RichText();
        foreach (array_values($baris) as $i => $b) {
            if ($i > 0) {
                $rt->createText("\n");
            }
            $run = $rt->createTextRun((string) ($b['teks'] ?? ''));
            if (!empty($b['bold'])) {
                $run->getFont()->setBold(true);
            }
            if (!empty($b['color'])) {
                $run->getFont()->setColor(new Color($b['color']));
            }
            if (!empty($b['size'])) {
                $run->getFont()->setSize((int) $b['size']);
            }
        }

        return $rt;
    }

    /**
     * Tulis nomor kontrol dokumen di kanan bawah (lewati bila kosong).
     */
    protected function tulisNomorDokumen(Worksheet $sheet, string $kolom, int $baris, ?string $nomor): void
    {
        if (!$nomor) {
            return;
        }
        $sheet->setCellValue("{$kolom}{$baris}", $nomor);
        $sheet->getStyle("{$kolom}{$baris}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle("{$kolom}{$baris}")->getFont()->setBold(true)->setSize(8)
            ->setColor(new Color('FF4B5563'));
    }
}

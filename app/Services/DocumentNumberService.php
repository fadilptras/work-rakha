<?php

namespace App\Services;

use App\Models\DocumentSequence;
use Illuminate\Support\Facades\DB;

/**
 * Penomoran dokumen gaya SPH: <seq>/<Segment>/RAKHA/<romawi>/<tahun>.
 * Atomik via lockForUpdate dalam satu transaksi (tiruan SphQuotation::createWithNumber).
 */
class DocumentNumberService
{
    public const SEGMENTS = [
        'PO' => 'PO',
        'GRN' => 'GRN',
        'PRT' => 'PRT',
        'SO' => 'Sales',
        'DO' => 'DO',
        'SJ' => 'SJ',
        'SOP' => 'SOP',
    ];

    public static function romanMonth(int $month): string
    {
        $map = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];

        return $map[max(1, min(12, $month)) - 1];
    }

    public function nextSequence(string $type): int
    {
        return DB::transaction(function () use ($type) {
            $row = DocumentSequence::lockForUpdate()
                ->where('type', $type)
                ->whereNull('year')
                ->first();

            if (! $row) {
                $row = DocumentSequence::create(['type' => $type, 'year' => null, 'last_no' => 0]);
            }

            $row->increment('last_no');

            return (int) $row->fresh()->last_no;
        });
    }

    public function formatNumber(string $type, int $sequence, ?\DateTimeInterface $date = null): string
    {
        $date = $date ?? now();
        $segment = self::SEGMENTS[$type] ?? $type;

        return sprintf(
            '%d/%s/RAKHA/%s/%s',
            $sequence,
            $segment,
            self::romanMonth((int) $date->format('n')),
            $date->format('Y')
        );
    }

    /** Nomor SOP opname ala Accurate: SOP/MM/YY/NNNNN (counter global berlanjut). */
    public function formatSop(int $sequence, ?\DateTimeInterface $date = null): string
    {
        $date = $date ?? now();

        return sprintf(
            'SOP/%s/%s/%05d',
            $date->format('m'),
            $date->format('y'),
            $sequence
        );
    }

    /** Nomor berikutnya tanpa commit — untuk preview form. */
    public function previewNext(string $type): string
    {
        $last = (int) (DocumentSequence::where('type', $type)->whereNull('year')->value('last_no') ?? 0);

        return $this->formatNumber($type, $last + 1);
    }

    /**
     * Nomor SJ harian: SJ.YYYYMMDD.NOURUT (NOURUT reset tiap hari, 3 digit).
     * Kunci sequence memakai type "SJ-YYYYMMDD" agar atomik per hari.
     */
    public static function sjDailyKey(?\DateTimeInterface $date = null): string
    {
        $date = $date ?? now();

        return 'SJ-' . $date->format('Ymd');
    }

    public static function formatSj(string $key, int $sequence): string
    {
        $ymd = substr($key, 3); // buang prefix "SJ-"

        return sprintf('SJ.%s.%03d', $ymd, $sequence);
    }

    /** Ambil nomor SJ berikutnya secara atomik (commit sequence). */
    public function nextSj(): string
    {
        $key = self::sjDailyKey();

        return self::formatSj($key, $this->nextSequence($key));
    }

    /** Preview nomor SJ berikutnya tanpa commit — untuk form. */
    public function previewSj(): string
    {
        $key = self::sjDailyKey();
        $last = (int) (DocumentSequence::where('type', $key)->whereNull('year')->value('last_no') ?? 0);

        return self::formatSj($key, $last + 1);
    }
}

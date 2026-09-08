<?php

namespace App\Models;

use App\Models\Concerns\SoftDeletesFlag;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SphQuotation extends Model
{
    use HasFactory, SoftDeletesFlag;

    protected $fillable = [
        'sph_sequence',
        'sph_number',
        'date',
        'customer_name',
        'customer_company',
        'ps',
        'ps_phone',
        'vat_percent',
        'items',
        'grand_total',
        'user_id',
        'is_deleted',
    ];

    protected $casts = [
        'date' => 'date',
        'items' => 'array',
        'grand_total' => 'decimal:2',
        'vat_percent' => 'integer',
        'is_deleted' => 'boolean',
    ];

    /**
     * Buat SPH baru beserta nomornya secara atomik. `lockForUpdate()` dan
     * INSERT berada dalam satu transaksi, sehingga tidak ada race condition:
     * dua penyimpanan bersamaan tidak akan mendapat sph_sequence/nomor yang sama.
     *
     * Format nomor: <urutan>/Sales/RAKHA/<bulan romawi>/<tahun>
     * Contoh: 318/Sales/RAKHA/VIII/2026
     */
    public static function createWithNumber(array $attributes): self
    {
        return DB::transaction(function () use ($attributes) {
            $lastRecord = static::lockForUpdate()
                ->orderBy('sph_sequence', 'desc')
                ->first();

            $nextSequence = $lastRecord ? $lastRecord->sph_sequence + 1 : 318;

            return static::create(array_merge($attributes, [
                'sph_sequence' => $nextSequence,
                'sph_number'   => static::formatSphNumber($nextSequence),
            ]));
        });
    }

    /**
     * Format nomor SPH dari urutan angka.
     */
    public static function formatSphNumber(int $sequence): string
    {
        $romanMonths = [
            'I', 'II', 'III', 'IV', 'V', 'VI',
            'VII', 'VIII', 'IX', 'X', 'XI', 'XII',
        ];

        $currentMonth = $romanMonths[(int) now()->format('n') - 1];
        $currentYear  = now()->format('Y');

        return sprintf('%d/Sales/RAKHA/%s/%s', $sequence, $currentMonth, $currentYear);
    }

    /**
     * Ganti angka urutan di awal sph_number dengan urutan baru, mempertahankan
     * akhiran "/Sales/RAKHA/<bulan>/<tahun>" yang sudah terpasang sebelumnya.
     * Dipakai saat renumber setelah soft delete, supaya bulan/tahun dokumen
     * lama tidak berubah mengikuti tanggal hari ini.
     */
    public static function sequenceToNumber(string $currentNumber, int $newSequence): string
    {
        return preg_replace('/^\d+/', (string) $newSequence, $currentNumber) ?? $currentNumber;
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

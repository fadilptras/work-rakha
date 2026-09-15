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

    /**
     * Accessor: selalu kembalikan ps_phone dalam format 08...
     * agar PDF/detail/history konsisten meski data lama tersimpan
     * dengan format +62 / 62 / 8.
     */
    public function getPsPhoneAttribute($value): ?string
    {
        if ($value === null || trim((string) $value) === '' || trim((string) $value) === '-') {
            return $value;
        }
        $digits = preg_replace('/[^0-9]/', '', (string) $value);
        if ($digits === '' || $digits === null) {
            return $value;
        }
        if (str_starts_with($digits, '62')) {
            $digits = '0' . substr($digits, 2);
        } elseif (str_starts_with($digits, '8')) {
            $digits = '0' . $digits;
        }
        return $digits;
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

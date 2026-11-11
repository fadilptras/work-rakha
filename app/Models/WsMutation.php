<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WsMutation extends Model
{
    protected $table = 'ws_mutation';

    use HasFactory;

    public const DIRECTION_IN = 'in';

    public const DIRECTION_OUT = 'out';

    public const STATUS_POSTED = 'posted';

    public const STATUS_VOID = 'void';

    public const SOURCE_MANUAL = 'manual';

    public const SOURCE_FAKTUR = 'faktur';

    public const PURPOSE_SALE = 'Penjualan';

    public const PURPOSE_SAMPLE = 'Sample';

    public const PURPOSE_RETURN = 'Retur Barang';

    public const PURPOSE_PURCHASE = 'Pembelian';

    /** Keterangan barang keluar yang diizinkan. */
    public const OUT_PURPOSES = [self::PURPOSE_SALE, self::PURPOSE_SAMPLE, self::PURPOSE_RETURN];

    /** Keterangan barang masuk yang diizinkan. */
    public const IN_PURPOSES = [self::PURPOSE_PURCHASE];

    /** Daftar untuk form keluar (kompatibilitas view lama). */
    public const PURPOSES = self::OUT_PURPOSES;

    /**
     * Normalisasi keterangan keluar ke nilai kanonik.
     * Nilai lama 'Retur Supplier' tetap diterima agar data lama tidak rusak.
     * Mengembalikan null bila di luar daftar yang diizinkan.
     */
    public static function normalizeOutPurpose(?string $purpose): ?string
    {
        $value = trim((string) $purpose);
        if ($value === '') {
            return null;
        }
        $lower = strtolower($value);
        if (in_array($lower, ['retur supplier', 'retur barang', 'return barang', 'retur', 'return'], true)) {
            return self::PURPOSE_RETURN;
        }
        if (in_array($lower, ['sample', 'saple', 'sampel'], true)) {
            return self::PURPOSE_SAMPLE;
        }
        if (in_array($lower, ['penjualan', 'jual', 'sale'], true)) {
            return self::PURPOSE_SALE;
        }
        foreach (self::OUT_PURPOSES as $allowed) {
            if (strcasecmp($value, $allowed) === 0) {
                return $allowed;
            }
        }

        return null;
    }

    protected $fillable = [
        'doc_no',
        'direction',
        'mutation_date',
        'customer_id',
        'supplier_id',
        'ship_address',
        'expedition',
        'po_no',
        'purpose',
        'source',
        'source_no',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'mutation_date' => 'date',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** Baris barang dokumen ini (termasuk audit/cancelled). */
    public function moves(): HasMany
    {
        return $this->hasMany(StockMove::class, 'ref_id')
            ->where('ref_type', 'ws_mutation');
    }

    /** Baris aktif dokumen ini (untuk tampilan & cetak). */
    public function activeMoves(): HasMany
    {
        return $this->moves()->where('status', '!=', StockMove::STATUS_CANCELLED);
    }

    /** Scope: hanya dokumen aktif. */
    public function scopePosted($query)
    {
        return $query->where($query->getModel()->getTable().'.status', self::STATUS_POSTED);
    }

    /** Berkas PDF surat jalan dokumen ini. */
    public function files(): HasMany
    {
        return $this->hasMany(SjFile::class, 'ws_mutation_id')->latest();
    }

    /** Label pihak tujuan: customer / supplier / Pemakaian Internal. */
    public function partyLabel(): string
    {
        if ($this->relationLoaded('customer') || $this->customer_id) {
            $name = $this->customer?->customer_name;
            if ($name) {
                return $name;
            }
        }
        if ($this->relationLoaded('supplier') || $this->supplier_id) {
            $name = $this->supplier?->supplier_name;
            if ($name) {
                return $name;
            }
        }

        return 'Pemakaian Internal';
    }

    /**
     * Filter arsip dokumen keluar (satu sumber untuk halaman SJ + export).
     * Kunci: search, direction, customer_id (__other = tanpa customer),
     * date_from, date_to. Arsip SJ khusus barang keluar.
     */
    public function scopeApplyFilters($query, array $f)
    {
        $isDate = fn ($v) => is_string($v) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $v);

        return $query
            ->where('status', self::STATUS_POSTED)
            ->when(empty($f['direction']), fn ($q) => $q->where('direction', self::DIRECTION_OUT))
            ->when(! empty($f['direction']), fn ($q) => $q->where('direction', $f['direction']))
            ->when(! empty($f['search']), function ($q) use ($f) {
                $s = trim((string) $f['search']);
                $q->where(fn ($w) => $w->where('doc_no', 'like', "%{$s}%")
                    ->orWhere('po_no', 'like', "%{$s}%")
                    ->orWhere('purpose', 'like', "%{$s}%")
                    ->orWhereHas('customer', fn ($c) => $c->where('customer_name', 'like', "%{$s}%"))
                    ->orWhereHas('supplier', fn ($s2) => $s2->where('supplier_name', 'like', "%{$s}%")));
            })
            ->when(! empty($f['direction']), fn ($q) => $q->where('direction', $f['direction']))
            ->when(array_key_exists('customer_id', $f) && $f['customer_id'] !== null && $f['customer_id'] !== '', function ($q) use ($f) {
                $cid = $f['customer_id'];
                if ($cid === '__other') {
                    $q->whereNull('customer_id');
                } elseif (ctype_digit((string) $cid)) {
                    $q->where('customer_id', $cid);
                }
            })
            ->when(isset($f['date_from']) && $isDate($f['date_from']), fn ($q) => $q->whereDate('mutation_date', '>=', $f['date_from']))
            ->when(isset($f['date_to']) && $isDate($f['date_to']), fn ($q) => $q->whereDate('mutation_date', '<=', $f['date_to']));
    }
}

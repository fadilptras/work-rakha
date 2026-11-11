<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Hasil Stock Opname per produk + periode (YYYY-MM).
 * Satu baris current per produk/periode; jejak posting ada di stock_moves.
 */
class StockOpname extends Model
{
    public const STATUS_DRAFT = 'draft';

    public const STATUS_DONE = 'done';

    protected $fillable = [
        'warehouse_id', 'product_id', 'period', 'opname_date', 'opname_at',
        'status', 'recorded_qty', 'counted_qty', 'diff_qty', 'note',
        'ba_creator', 'ba_checker', 'stock_move_id', 'revision',
        'created_by', 'validated_by', 'validated_at',
    ];

    protected $casts = [
        'opname_date' => 'date',
        'opname_at' => 'datetime',
        'validated_at' => 'datetime',
        'recorded_qty' => 'decimal:2',
        'counted_qty' => 'decimal:2',
        'diff_qty' => 'decimal:2',
        'revision' => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public function stockMove(): BelongsTo
    {
        return $this->belongsTo(StockMove::class);
    }

    public function isDone(): bool
    {
        return $this->status === self::STATUS_DONE;
    }

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function hasDifference(): bool
    {
        return abs((float) $this->diff_qty) >= 0.005;
    }

    public function scopeForPeriod($query, string $period)
    {
        return $query->where('period', $period);
    }
}

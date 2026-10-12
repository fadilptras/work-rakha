<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id', 'batch_no', 'receipt_date', 'expiry_date', 'qty_initial', 'qty_current',
    ];

    protected $casts = [
        'receipt_date' => 'date',
        'expiry_date' => 'date',
        'qty_initial' => 'decimal:2',
        'qty_current' => 'decimal:2',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function scopeAvailable($query)
    {
        return $query->where('qty_current', '>', 0);
    }

    /** Batch terdekat kedaluwarsa dulu (FEFO). */
    public function scopeFefo($query)
    {
        return $query->orderByRaw('expiry_date IS NULL, expiry_date ASC')->orderBy('receipt_date', 'asc')->orderBy('id', 'asc');
    }
}

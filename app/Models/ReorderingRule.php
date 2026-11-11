<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReorderingRule extends Model
{
    use HasFactory;

    protected $fillable = ['product_id', 'min_qty', 'max_qty', 'active'];

    protected $casts = [
        'min_qty' => 'decimal:2',
        'max_qty' => 'decimal:2',
        'active' => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Rincian mutasi stok per log, disimpan sebagai 1 baris per log
 * dengan JSON array `items`:
 * [{product_id, old_stok, new_stok, old_stok_po, new_stok_po}, ...]
 */
class StockLogDetail extends Model
{
    protected $fillable = [
        'stock_log_id',
        'items',
    ];

    protected $casts = [
        'items' => 'array',
    ];

    public function stockLog()
    {
        return $this->belongsTo(StockLog::class);
    }
}
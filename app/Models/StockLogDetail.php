<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockLogDetail extends Model
{
    protected $fillable = [
        'stock_log_id',
        'barang_id',
        'old_stok',
        'new_stok',
        'old_stok_po',
        'new_stok_po',
    ];

    public function stockLog()
    {
        return $this->belongsTo(StockLog::class);
    }

    public function barang()
    {
        return $this->belongsTo(Barang::class);
    }
}

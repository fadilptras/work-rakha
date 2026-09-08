<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyStockHistory extends Model
{
    protected $fillable = [
        'product_id',
        'tanggal',
        'stok',
        'stok_po',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}

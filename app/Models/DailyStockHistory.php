<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyStockHistory extends Model
{
    protected $fillable = [
        'barang_id',
        'tanggal',
        'stok',
        'stok_po',
    ];

    public function barang()
    {
        return $this->belongsTo(Barang::class);
    }
}

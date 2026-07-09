<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    protected $fillable = ['tanggal', 'keterangan', 'is_cuti_bersama'];

    // Casting agar tanggal otomatis jadi Carbon object
    protected $casts = [
        'tanggal' => 'date',
        'is_cuti_bersama' => 'boolean',
    ];
}

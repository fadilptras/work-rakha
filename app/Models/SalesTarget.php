<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesTarget extends Model
{
    protected $table = 'sales_targets';

    protected $fillable = [
        'tahun',
        'bulan_angka',
        'bulan',
        'ps',
        'target_amount',
        'sales_last_year_amount',
    ];
}

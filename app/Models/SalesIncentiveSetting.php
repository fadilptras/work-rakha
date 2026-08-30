<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesIncentiveSetting extends Model
{
    protected $table = 'sales_incentive_settings';

    protected $fillable = [
        'tahun',
        'bulan',
        'type',
        'basis',
        'min_achievement',
        'incentive_value',
    ];
}

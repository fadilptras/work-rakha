<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesTarget extends Model
{
    protected $table = 'sales_targets';

    protected $fillable = [
        'year',
        'month_number',
        'month',
        'ps',
        'target_amount',
        'last_year_amount',
    ];
}

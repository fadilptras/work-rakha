<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesOutletClosing extends Model
{
    use HasFactory;

    protected $table = 'sales_outlet_closings';

    protected $fillable = [
        'year',
        'month',
        'ps',
        'customer_name',
        'closing_rate',
        'closing_count',
    ];

    protected $casts = [
        'year' => 'integer',
        'closing_rate' => 'float',
        'closing_count' => 'integer',
    ];
}

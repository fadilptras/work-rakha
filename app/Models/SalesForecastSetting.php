<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesForecastSetting extends Model
{
    use HasFactory;

    protected $table = 'sales_forecast_settings';

    protected $fillable = [
        'year',
        'month',
        'percentage',
    ];
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesClosing extends Model
{
    use HasFactory;

    public const TYPE_SALES = 'sales';
    public const TYPE_PURCHASE = 'purchase';

    protected $table = 'sales_closings';

    protected $fillable = [
        'type',
        'year',
        'month',
        'date',
        'ps',
        'customer_name',
        'supplier_name',
        'product_name',
        'qty',
        'unit',
        'amount',
        'note',
        'created_by',
    ];

    protected $casts = [
        'year' => 'integer',
        'amount' => 'float',
    ];
}

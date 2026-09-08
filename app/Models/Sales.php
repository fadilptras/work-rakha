<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sales extends Model
{
    protected $table = 'sales';
    protected $fillable = [
        'date',
        'customer_name',
        'product_name',
        'qty',
        'unit',
        'base_price',
        'discount',
        'net_price',
        'month',
        'ps',
    ];
}
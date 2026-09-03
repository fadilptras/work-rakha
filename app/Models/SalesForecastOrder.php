<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesForecastOrder extends Model
{
    use HasFactory;

    protected $table = 'sales_forecast_orders';

    protected $fillable = [
        'tahun',
        'bulan_acuan',
        'nama_produk',
        'forecast_qty',
        'suggested_qty',
        'user_id',
    ];

    /**
     * Relasi opsional ke User (jika ingin melacak siapa yang menginput/mengubah)
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

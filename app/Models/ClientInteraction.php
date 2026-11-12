<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class ClientInteraction extends Model
{
    use HasFactory;

    protected $table = 'client_interactions';

    protected $fillable = [
        'client_id',
        'customer_id',
        'product_id',
        'product_name',
        'transaction_type',
        'amount',
        'interaction_date',
        'notes',
        'sales_amount',
        'commission_rate',
    ];

    protected $casts = [
        'interaction_date' => 'date',
    ];

    protected static ?array $cachedTableColumns = null;

    /**
     * Dapatkan daftar kolom fisik tabel client_interactions.
     */
    public static function getTableColumns(): array
    {
        if (static::$cachedTableColumns === null) {
            static::$cachedTableColumns = Schema::getColumnListing('client_interactions');
        }

        return static::$cachedTableColumns;
    }

    protected static function booted()
    {
        static::saving(function ($interaction) {
            $cols = static::getTableColumns();
            // Hapus atribut yang tidak ada di kolom database fisik (seperti customer_id / product_id bila belum dimigrasi)
            foreach (array_keys($interaction->attributes) as $attr) {
                if (!in_array($attr, $cols, true)) {
                    unset($interaction->attributes[$attr]);
                }
            }
        });
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}

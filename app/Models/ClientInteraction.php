<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientInteraction extends Model
{
    use HasFactory;

    protected $table = 'client_interactions';

    protected $fillable = [
        'client_id',
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

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
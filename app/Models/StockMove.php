<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMove extends Model
{
    use HasFactory;

    public const TYPE_RECEIPT = 'receipt';
    public const TYPE_DELIVERY = 'delivery';
    public const TYPE_TRANSFER = 'transfer';
    public const TYPE_ADJUSTMENT = 'adjustment';
    public const TYPE_SCRAP = 'scrap';

    public const STATUS_WAITING = 'waiting';
    public const STATUS_READY = 'ready';
    public const STATUS_DONE = 'done';
    public const STATUS_CANCELLED = 'cancelled';

    // Lokasi virtual (bukan rak fisik).
    public const LOC_STOCK = 'Stock';
    public const LOC_ADJUSTMENT = 'Adjustment';
    public const LOC_SCRAP = 'Scrap';

    protected $fillable = [
        'warehouse_id', 'product_id', 'batch_no', 'doc_no', 'partner', 'po_no', 'purpose', 'ship_address', 'expedition', 'qty', 'uom',
        'from_location', 'to_location', 'type', 'status',
        'ref_type', 'ref_id', 'expiry_date', 'reason',
        'user_id', 'validated_by', 'validated_at',
    ];

    protected $casts = [
        'qty' => 'decimal:2',
        'expiry_date' => 'date',
        'validated_at' => 'datetime',
    ];

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public function isInbound(): bool
    {
        return in_array($this->type, [self::TYPE_RECEIPT, self::TYPE_ADJUSTMENT], true)
            && $this->to_location === self::LOC_STOCK;
    }

    public function scopeOpen($query)
    {
        return $query->whereIn('status', [self::STATUS_WAITING, self::STATUS_READY]);
    }

    public static array $typeLabels = [
        self::TYPE_RECEIPT => 'Penerimaan',
        self::TYPE_DELIVERY => 'Pengeluaran',
        self::TYPE_TRANSFER => 'Transfer',
        self::TYPE_ADJUSTMENT => 'Penyesuaian',
        self::TYPE_SCRAP => 'Scrap/Rusak',
    ];

    public static array $statusLabels = [
        self::STATUS_WAITING => 'Menunggu',
        self::STATUS_READY => 'Siap',
        self::STATUS_DONE => 'Selesai',
        self::STATUS_CANCELLED => 'Batal',
    ];
}

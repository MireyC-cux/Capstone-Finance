<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseOrderItem extends Model
{
    protected $primaryKey = 'po_item_id';

    protected $fillable = [
        'purchase_order_id',
        'item_id',
        'description',
        'quantity',
        'unit_price',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
    ];

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id', 'purchase_order_id');
    }

    public function serviceRequestItem(): BelongsTo
    {
        return $this->belongsTo(ServiceRequestItem::class, 'item_id', 'item_id');
    }

    public function getTotalPriceAttribute(): string
    {
        $total = (float)$this->quantity * (float)$this->unit_price;
        return number_format($total, 2, '.', '');
    }
}

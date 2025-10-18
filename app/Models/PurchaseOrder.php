<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseOrder extends Model
{
    protected $primaryKey = 'po_id';

    protected $fillable = [
        'ap_id',
        'supplier_id',
        'service_request_id',
        'po_number',
        'po_date',
        'status',
        'total_amount',
        'created_by',
        'approved_by',
        'remarks',
    ];

    protected $casts = [
        'po_date' => 'date',
        'total_amount' => 'decimal:2',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function serviceRequest(): BelongsTo
    {
        return $this->belongsTo(ServiceRequest::class, 'service_request_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class, 'purchase_order_id');
    }

    public function accountsPayable(): BelongsTo
    {
        return $this->belongsTo(AccountsPayable::class, 'ap_id', 'ap_id');
    }

    public function getComputedTotalAttribute(): string
    {
        $sum = $this->items->sum(function ($i) { return (float)$i->quantity * (float)$i->unit_price; });
        return number_format($sum, 2, '.', '');
    }
}

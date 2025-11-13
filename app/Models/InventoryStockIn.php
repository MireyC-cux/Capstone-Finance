<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryStockIn extends Model
{
    use HasFactory;

    protected $table = 'inventory_stock_in';
    protected $primaryKey = 'stock_in_id';

    protected $fillable = [
        'po_number',
        'purchase_order_id',
        'supplier_id',
        'delivered_date',
        'status',
        'items',
        'payment_status',
        'remarks',
    ];

    protected $casts = [
        'items' => 'array',
        'delivered_date' => 'date',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
    }
}

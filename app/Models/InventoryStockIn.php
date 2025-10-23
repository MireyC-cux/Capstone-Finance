<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryStockIn extends Model
{
    protected $table = 'inventory_stock_in';
    protected $primaryKey = 'stock_in_id';
    protected $fillable = [
        'po_id','item_id','quantity','unit_cost','received_date','received_by','remarks'
    ];

    public function item() { return $this->belongsTo(InventoryItem::class, 'item_id', 'item_id'); }
    public function purchaseOrder() { return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id', 'purchase_order_id'); }
}

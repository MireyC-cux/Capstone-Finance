<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryStockOut extends Model
{
    protected $table = 'inventory_stock_out';
    protected $primaryKey = 'stock_out_id';
    protected $fillable = [
        'service_request_id','item_id','quantity','issued_to','issued_date','purpose','remarks'
    ];

    public function item() { return $this->belongsTo(InventoryItem::class, 'item_id', 'item_id'); }
    public function serviceRequest() { return $this->belongsTo(ServiceRequest::class, 'service_request_id', 'service_request_id'); }
}

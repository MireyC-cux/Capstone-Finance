<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryItem extends Model
{
    protected $primaryKey = 'item_id';
    protected $fillable = [
        'item_name','category','brand','model','unit','reorder_level','unit_cost','selling_price','status'
    ];

    public function balance() { return $this->hasOne(InventoryBalance::class, 'item_id', 'item_id'); }
    public function stockIns() { return $this->hasMany(InventoryStockIn::class, 'item_id', 'item_id'); }
    public function stockOuts() { return $this->hasMany(InventoryStockOut::class, 'item_id', 'item_id'); }
    public function adjustments() { return $this->hasMany(InventoryAdjustment::class, 'item_id', 'item_id'); }
}

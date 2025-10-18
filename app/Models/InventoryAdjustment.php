<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryAdjustment extends Model
{
    protected $primaryKey = 'adjustment_id';
    protected $fillable = [
        'item_id','adjustment_type','quantity','reason','adjusted_by','adjustment_date'
    ];

    public function item() { return $this->belongsTo(InventoryItem::class, 'item_id', 'item_id'); }
}

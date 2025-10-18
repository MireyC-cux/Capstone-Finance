<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryBalance extends Model
{
    public $timestamps = false;
    protected $primaryKey = 'item_id';
    public $incrementing = false;
    protected $fillable = ['item_id','current_stock','last_updated'];

    public function item() { return $this->belongsTo(InventoryItem::class, 'item_id', 'item_id'); }
}

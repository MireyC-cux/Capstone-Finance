<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryStockOut extends Model
{
    use HasFactory;

    protected $table = 'inventory_stock_out';
    protected $primaryKey = 'stock_out_id';

    protected $fillable = [
        'item_id',
        'service_type',
        'item_name',       // optional
        'aircon_type_id',
        'quantity',
        'issued_date',
        'status',
    ];

    public function item()
    {
        return $this->belongsTo(ServiceRequestItem::class, 'item_id', 'item_id');
    }

     public function airconType()
    {
        return $this->belongsTo(AirconType::class, 'aircon_type_id', 'aircon_type_id');
    }
}

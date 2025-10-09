<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AirconType extends Model
{
    protected $primaryKey = 'aircon_type_id';
    protected $fillable = [
        'name',
        'brand',
        'capacity',
        'model',
        'category',
        'base_price',
        'description',
        'status'
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
        'status' => 'string'
    ];

    public function airconServicePrices()
    {
        return $this->hasMany(AirconServicePrice::class, 'aircon_type_id');
    }

    public function serviceRequestItems()
    {
        return $this->hasMany(ServiceRequestItem::class, 'aircon_type_id');
    }
}

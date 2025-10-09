<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $primaryKey = 'services_id';
    protected $fillable = [
        'service_type',
        'status'
    ];

    protected $casts = [
        'status' => 'string'
    ];

    public function airconServicePrices()
    {
        return $this->hasMany(AirconServicePrice::class, 'services_id');
    }

    public function serviceRequestItems()
    {
        return $this->hasMany(ServiceRequestItem::class, 'services_id');
    }
}

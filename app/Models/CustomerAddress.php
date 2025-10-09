<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerAddress extends Model
{
    protected $primaryKey = 'address_id';
    protected $fillable = [
        'customer_id',
        'label',
        'street_address',
        'barangay',
        'city',
        'province',
        'zip_code',
        'is_default'
    ];

    protected $casts = [
        'is_default' => 'boolean'
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function serviceRequests()
    {
        return $this->hasMany(ServiceRequest::class, 'address_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $primaryKey = 'customer_id';
    protected $fillable = [
        'full_name',
        'email',
        'business_name',
        'contact_info'
    ];

    public function addresses()
    {
        return $this->hasMany(CustomerAddress::class, 'customer_id');
    }

    public function serviceRequests()
    {
        return $this->hasMany(ServiceRequest::class, 'customer_id');
    }

    public function accountsReceivable()
    {
        return $this->hasMany(AccountsReceivable::class, 'customer_id');
    }

    public function billings()
    {
        return $this->hasMany(Billing::class, 'customer_id');
    }
}

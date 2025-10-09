<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceRequest extends Model
{
    public $incrementing = false;
    protected $primaryKey = 'service_request_id';
    protected $fillable = [
        'customer_id',
        'address_id',
        'service_date',
        'start_date',
        'end_date',
        'start_time',
        'end_time',
        'type_of_payment',
        'order_status',
        'payment_status',
        'accomplishment_date',
        'remarks',
        'service_request_number'
    ];

    protected $dates = [
        'service_date',
        'start_date',
        'end_date',
        'accomplishment_date',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'order_status' => 'string',
        'payment_status' => 'string'
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function address()
    {
        return $this->belongsTo(CustomerAddress::class, 'address_id');
    }

    public function items()
    {
        return $this->hasMany(ServiceRequestItem::class, 'service_request_id');
    }

    public function technicianAssignments()
    {
        return $this->hasMany(TechnicianAssignment::class, 'service_request_id');
    }

    public function accountsReceivable()
    {
        return $this->hasOne(AccountsReceivable::class, 'service_request_id');
    }

    public function billings()
    {
        return $this->hasMany(Billing::class, 'service_request_id');
    }
}

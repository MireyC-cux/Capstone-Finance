<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Billing extends Model
{
    protected $table = 'billings';
    protected $primaryKey = 'billing_id';
    protected $fillable = [
        'service_request_id',
        'customer_id',
        'billing_date',
        'due_date',
        'total_amount',
        'discount',
        'tax',
        'status'
    ];

    protected $dates = [
        'billing_date',
        'due_date',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'status' => 'string'
    ];

    public function serviceRequest()
    {
        return $this->belongsTo(ServiceRequest::class, 'service_request_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class, 'billing_id');
    }
}

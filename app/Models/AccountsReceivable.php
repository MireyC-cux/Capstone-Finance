<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountsReceivable extends Model
{
    protected $table = 'accounts_receivable';
    protected $primaryKey = 'ar_id';
    protected $fillable = [
        'customer_id',
        'service_request_id',
        'invoice_number',
        'invoice_date',
        'due_date',
        'total_amount',
        'amount_paid',
        'status',
        'payment_terms'
    ];
    
    // Balance is a virtual column, so we'll add an accessor for it
    protected $appends = ['balance'];
    
    public function getBalanceAttribute()
    {
        return $this->total_amount - $this->amount_paid;
    }

    protected $dates = [
        'invoice_date',
        'due_date',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'status' => 'string'
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function serviceRequest()
    {
        return $this->belongsTo(ServiceRequest::class, 'service_request_id');
    }

    public function payments()
    {
        return $this->hasMany(PaymentReceived::class, 'ar_id');
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class, 'ar_id');
    }
}

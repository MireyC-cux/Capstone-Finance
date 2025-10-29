<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentHistory extends Model
{
    protected $table = 'payment_history';
    protected $primaryKey = 'payment_id';

    protected $fillable = [
        'billing_id',
        'service_request_id',
        'payment_date',
        'due_date',
        'type_of_payment',
        'amount',
        'status',
        'or_file_path',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'due_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function serviceRequest()
    {
        return $this->belongsTo(ServiceRequest::class, 'service_request_id', 'service_request_id');
    }

    public function billing()
    {
        return $this->belongsTo(Billing::class, 'billing_id', 'billing_id');
    }
}

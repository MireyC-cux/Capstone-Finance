<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentReceived extends Model
{
    protected $primaryKey = 'payment_id';
    protected $fillable = [
        'ar_id',
        'payment_date',
        'amount',
        'payment_method',
        'reference_number'
    ];

    protected $dates = [
        'payment_date'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_method' => 'string'
    ];

    public function accountsReceivable()
    {
        return $this->belongsTo(AccountsReceivable::class, 'ar_id');
    }
}

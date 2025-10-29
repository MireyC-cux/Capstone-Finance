<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentReceived extends Model
{
    protected $table = 'payments_received';
    protected $primaryKey = 'payment_id';
    protected $fillable = [
        'ar_id',
        'payment_date',
        'amount',
        'payment_method',
        'payment_type',
        'reference_number',
        'or_file_path'
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

    public function cashFlow()
    {
        return $this->hasOne(CashFlow::class, 'source_id', 'payment_id')
            ->where('source_type', 'Invoice Payment');
    }
}

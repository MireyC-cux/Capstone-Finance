<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentMade extends Model
{
    protected $primaryKey = 'payment_id';
    protected $fillable = [
        'ap_id',
        'payment_date',
        'amount',
        'payment_method',
        'reference_number',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function accountsPayable(): BelongsTo
    {
        return $this->belongsTo(AccountsPayable::class, 'ap_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountsPayable extends Model
{
    protected $primaryKey = 'ap_id';
    protected $fillable = [
        'supplier_id',
        'purchase_order_id',
        'invoice_number',
        'invoice_date',
        'due_date',
        'total_amount',
        'amount_paid',
        'status',
        'payment_terms'
    ];

    protected $dates = [
        'invoice_date',
        'due_date'
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'status' => 'string'
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function payments()
    {
        return $this->hasMany(PaymentMade::class, 'ap_id');
    }
}

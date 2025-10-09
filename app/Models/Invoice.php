<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $primaryKey = 'invoice_id';
    protected $fillable = [
        'billing_id',
        'ar_id',
        'invoice_number',
        'invoice_date',
        'due_date',
        'amount',
        'status'
    ];

    protected $table = 'invoices';
    
    protected $dates = [
        'invoice_date',
        'due_date',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'status' => 'string',
        'invoice_date' => 'date',
        'due_date' => 'date'
    ];

    public function billing()
    {
        return $this->belongsTo(Billing::class, 'billing_id');
    }

    public function accountsReceivable()
    {
        return $this->belongsTo(AccountsReceivable::class, 'ar_id');
    }
}

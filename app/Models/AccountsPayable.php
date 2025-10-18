<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class AccountsPayable extends Model
{
    protected $table = 'accounts_payable';
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

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id', 'po_id');
    }

    public function getIsOverdueAttribute(): bool
    {
        if ($this->status === 'Paid') return false;
        return Carbon::parse($this->due_date)->isPast();
    }

    public function getTotalPaidAttribute(): string
    {
        $sum = (float) $this->payments()->sum('amount');
        return number_format($sum, 2, '.', '');
    }
}

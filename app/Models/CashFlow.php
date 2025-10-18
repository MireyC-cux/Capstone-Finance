<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashFlow extends Model
{
    protected $table = 'cash_flow';
    protected $primaryKey = 'cashflow_id';
    protected $fillable = [
        'transaction_type',
        'source_type',
        'source_id',
        'account_id',
        'amount',
        'transaction_date',
        'description',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'transaction_date' => 'date',
    ];

    public function source()
    {
        return $this->morphTo('source', 'source_type', 'source_id');
    }
}

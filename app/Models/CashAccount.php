<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashAccount extends Model
{
    protected $table = 'cash_accounts';
    protected $primaryKey = 'account_id';
    protected $fillable = [
        'account_name', 'balance', 'type'
    ];

    protected $casts = [
        'balance' => 'decimal:2',
    ];
}

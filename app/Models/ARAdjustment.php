<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ARAdjustment extends Model
{
    protected $table = 'ar_adjustments';
    protected $primaryKey = 'adjustment_id';
    protected $fillable = [
        'ar_id',
        'adjustment_date',
        'type',
        'amount',
        'reason',
    ];

    protected $casts = [
        'adjustment_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function accountsReceivable()
    {
        return $this->belongsTo(AccountsReceivable::class, 'ar_id');
    }
}

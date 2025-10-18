<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashAdvance extends Model
{
    protected $primaryKey = 'cash_advance_id';
    protected $fillable = [
        'employeeprofiles_id',
        'amount',
        'reason',
        'filed_date',
        'approved_date',
        'status',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'filed_date' => 'datetime',
        'approved_date' => 'datetime',
    ];

    public function employeeProfile(): BelongsTo
    {
        return $this->belongsTo(EmployeeProfile::class, 'employeeprofiles_id');
    }
}

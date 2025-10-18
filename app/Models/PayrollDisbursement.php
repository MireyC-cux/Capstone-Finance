<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollDisbursement extends Model
{
    protected $primaryKey = 'disbursement_id';
    protected $fillable = [
        'payroll_id',
        'employeeprofiles_id',
        'payment_date',
        'payment_method',
        'reference_number',
        'status',
    ];

    protected $casts = [
        'payment_date' => 'date',
    ];

    public function payroll(): BelongsTo
    {
        return $this->belongsTo(Payroll::class, 'payroll_id');
    }

    public function employeeProfile(): BelongsTo
    {
        return $this->belongsTo(EmployeeProfile::class, 'employeeprofiles_id');
    }
}

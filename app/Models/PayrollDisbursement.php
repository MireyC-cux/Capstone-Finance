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
        'proof_of_payment',
    ];

    protected $casts = [
        'payment_date' => 'date',
    ];

    // Relationship to Payroll
    public function payroll(): BelongsTo
    {
        return $this->belongsTo(Payroll::class, 'payroll_id');
    }

    // Relationship to EmployeeProfile (note the method name)
    public function employeeProfile(): BelongsTo
    {
        return $this->belongsTo(Employeeprofiles::class, 'employeeprofiles_id', 'employeeprofiles_id');
    }
}

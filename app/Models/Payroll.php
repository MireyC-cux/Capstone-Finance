<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payroll extends Model
{
    protected $primaryKey = 'payroll_id';
    protected $fillable = [
        'employeeprofiles_id',
        'total_days_of_work',
        'pay_period',
        'pay_period_start',
        'pay_period_end',
        'basic_salary',
        'overtime_pay',
        'deductions',
        'cash_advance',
        'net_pay',
        'status',
    ];

    protected $casts = [
        'pay_period_start' => 'date',
        'pay_period_end' => 'date',
        'basic_salary' => 'decimal:2',
        'overtime_pay' => 'decimal:2',
        'deductions' => 'decimal:2',
        'cash_advance' => 'decimal:2',
        'net_pay' => 'decimal:2',
    ];

    public function employeeProfile(): BelongsTo
    {
        return $this->belongsTo(EmployeeProfile::class, 'employeeprofiles_id');
    }
}

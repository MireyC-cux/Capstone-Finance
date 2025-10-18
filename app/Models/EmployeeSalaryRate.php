<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeSalaryRate extends Model
{
    protected $table = 'employee_salary_rates';
    protected $primaryKey = 'employee_salary_id';

    protected $fillable = [
        'employeeprofiles_id',
        'salary_rate_id',
        'custom_salary_rate',
        'effective_date',
        'status',
    ];

    protected $casts = [
        'custom_salary_rate' => 'decimal:2',
        'effective_date' => 'date',
    ];

    public function employeeProfile(): BelongsTo
    {
        return $this->belongsTo(EmployeeProfile::class, 'employeeprofiles_id');
    }

    public function salaryRate(): BelongsTo
    {
        return $this->belongsTo(SalaryRate::class, 'salary_rate_id');
    }
}

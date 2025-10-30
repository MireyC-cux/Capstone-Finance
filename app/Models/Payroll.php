<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    use HasFactory;

    protected $primaryKey = 'payroll_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'employeeprofiles_id',
        'total_days_of_work',
        'pay_period',
        'pay_period_start',
        'pay_period_end',
        'salary_rate',
        'basic_salary',
        'overtime_pay',
        'gross_pay',
        'tax_deduction',
        'sss_contribution',
        'philhealth_contribution',
        'pagibig_contribution',
        'deductions',
        'bonuses',
        'bonus_amount',
        'net_pay',
        'status',
        'year',
        'month',
    ];

    protected $casts = [
        'pay_period_start' => 'date',
        'pay_period_end' => 'date',
        'salary_rate' => 'decimal:2',
        'basic_salary' => 'decimal:2',
        'overtime_pay' => 'decimal:2',
        'gross_pay' => 'decimal:2',
        'tax_deduction' => 'decimal:2',
        'sss_contribution' => 'decimal:2',
        'philhealth_contribution' => 'decimal:2',
        'pagibig_contribution' => 'decimal:2',
        'deductions' => 'decimal:2',
        'bonus_amount' => 'decimal:2',
        'net_pay' => 'decimal:2',
    ];

    public function employeeprofiles()
    {
        return $this->belongsTo(Employeeprofiles::class, 'employeeprofiles_id', 'employeeprofiles_id');
    }
}
     
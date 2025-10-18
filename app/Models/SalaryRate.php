<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalaryRate extends Model
{
    protected $primaryKey = 'salary_rate_id';
    protected $fillable = [
        'position',
        'salary_rate',
        'status',
    ];

    protected $casts = [
        'salary_rate' => 'decimal:2',
    ];

    public function employeeSalaryRates(): HasMany
    {
        return $this->hasMany(EmployeeSalaryRate::class, 'salary_rate_id');
    }
}

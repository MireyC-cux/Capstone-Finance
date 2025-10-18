<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Salaries extends Model
{
    protected $primaryKey = 'salaries_id';
    protected $fillable = [
        
        'basic_salary',
        'effective_date',
        'status',
    ];

    protected $casts = [
        'basic_salary' => 'decimal:2',
        'effective_date' => 'date',
    ];

    public function employeeProfile(): BelongsTo
    {
        return $this->belongsTo(EmployeeProfiles::class, 'employeeprofiles_id');
    }
}

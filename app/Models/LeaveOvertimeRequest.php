<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveOvertimeRequest extends Model
{
    protected $primaryKey = 'request_id';
    protected $fillable = [
        'employeeprofiles_id',
        'leave_days',
        'overtime_hours',
        'status',
        'request_date',
    ];

    protected $casts = [
        'request_date' => 'datetime',
    ];

    public function employeeProfile(): BelongsTo
    {
        return $this->belongsTo(EmployeeProfile::class, 'employeeprofiles_id');
    }
}

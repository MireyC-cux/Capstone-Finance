<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    protected $primaryKey = 'attendance_id';
    protected $fillable = [
        'employeeprofiles_id',
        'date',
        'time_in',
        'time_out',
        'flag',
        'status',
    ];

    protected $dates = [
        'date',
        'time_in',
        'time_out',
    ];

    public function employeeProfile(): BelongsTo
    {
        return $this->belongsTo(EmployeeProfile::class, 'employeeprofiles_id');
    }
}

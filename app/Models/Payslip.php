<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payslip extends Model
{
    protected $primaryKey = 'payslip_id';
    public $timestamps = false;

    protected $fillable = [
        'payroll_id',
        'employeeprofiles_id',
        'pdf_name',
        'pdf_mime',
        'pdf_file',
        'generated_at',
    ];

    protected $casts = [
        'generated_at' => 'datetime',
    ];

    protected $hidden = [
        'pdf_file',
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

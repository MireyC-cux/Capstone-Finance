<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OvertimeRequest extends Model
{
    protected $table = 'overtime_requests';
    protected $primaryKey = 'overtime_request_id';

    protected $fillable = [
        'employeeprofiles_id',
        'service_request_item_id',
        'hours',
        'amount',
        'filed_date',
        'approved_date',
        'status',
        'created_by',
        'release_date',
        'reason',
    ];

    protected $casts = [
        'hours' => 'decimal:2',
        'amount' => 'decimal:2',
        'filed_date' => 'datetime',
        'approved_date' => 'datetime',
        'release_date' => 'datetime',
    ];

    public function employeeProfile(): BelongsTo
    {
        return $this->belongsTo(EmployeeProfile::class, 'employeeprofiles_id');
    }
}

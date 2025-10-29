<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expenses extends Model
{
    protected $primaryKey = 'expense_id';
    protected $fillable = [
        'employeeprofiles_id',
        'supplier_id',
        'expense_name',
        'category',
        'description',
        'amount',
        'expense_date',
        'paid_to',
        'payment_method',
        'reference_number',
        'remarks',
        'status',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'expense_date' => 'date',
    ];

    public function employeeProfile(): BelongsTo
    {
        return $this->belongsTo(EmployeeProfiles::class, 'employeeprofiles_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }
}

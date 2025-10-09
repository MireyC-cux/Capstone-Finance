<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinancialReportSummary extends Model
{
    protected $primaryKey = 'report_id';
    protected $fillable = [
        'report_type',
        'period_start',
        'period_end',
        'total_income',
        'total_expenses',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'total_income' => 'decimal:2',
        'total_expenses' => 'decimal:2',
    ];
}

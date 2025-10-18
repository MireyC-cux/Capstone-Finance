<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class BusinessFinancial extends Model
{
    protected $table = 'business_financials';
    protected $primaryKey = 'financial_id';

    protected $fillable = [
        'capital',
        'total_inflows',
        'total_outflows',
        'as_of_date',
        'remarks',
    ];

    protected $casts = [
        'capital' => 'decimal:2',
        'total_inflows' => 'decimal:2',
        'total_outflows' => 'decimal:2',
        'current_balance' => 'decimal:2',
        'profit' => 'decimal:2',
        'as_of_date' => 'date',
    ];

    public static function recalcTotals(): void
    {
        $sums = CashFlow::query()
            ->where(function ($q) {
                // Exclude capital injections/withdrawals from totals; they are tracked in 'capital'
                $q->where('source_type', '!=', 'Other')
                  ->orWhere(function ($q2) {
                      $q2->where('source_type', 'Other')
                         ->where(function ($qq) {
                             $qq->whereNull('description')
                                ->orWhere(function ($qq2) {
                                    $qq2->where('description', 'not like', 'Owner Capital%');
                                });
                         });
                  });
            })
            ->selectRaw("SUM(CASE WHEN transaction_type='Inflow' THEN amount ELSE 0 END) as inflows, SUM(CASE WHEN transaction_type='Outflow' THEN amount ELSE 0 END) as outflows")
            ->first();

        $inflows = (float)($sums->inflows ?? 0);
        $outflows = (float)($sums->outflows ?? 0);

        DB::transaction(function () use ($inflows, $outflows) {
            $row = self::lockForUpdate()->first();
            if (!$row) {
                $row = new self();
                $row->as_of_date = now()->toDateString();
            }
            $row->total_inflows = number_format($inflows, 2, '.', '');
            $row->total_outflows = number_format($outflows, 2, '.', '');
            $row->as_of_date = now()->toDateString();
            $row->save();
        });
    }
}


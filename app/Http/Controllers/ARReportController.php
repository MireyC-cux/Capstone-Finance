<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\AccountsReceivable;

class ARReportController extends Controller
{
    public function agingReport(Request $request)
    {
        $today = Carbon::today();

        $ars = AccountsReceivable::with('customer')
            ->whereIn('status', ['Unpaid', 'Partially Paid', 'Overdue'])
            ->get();

        $grouped = [];
        foreach ($ars as $ar) {
            $customer = $ar->customer?->business_name ?? $ar->customer?->full_name ?? 'Unknown';
            if (!isset($grouped[$customer])) {
                $grouped[$customer] = [
                    'Current' => 0,
                    '1-30' => 0,
                    '31-60' => 0,
                    '61-90' => 0,
                    '91+' => 0,
                    'Total' => 0,
                ];
            }
            $balance = max(0, (float) $ar->total_amount - (float) $ar->amount_paid);
            $days = Carbon::parse($ar->due_date)->diffInDays($today, false);
            if ($days <= 0) {
                $grouped[$customer]['Current'] += $balance;
            } elseif ($days <= 30) {
                $grouped[$customer]['1-30'] += $balance;
            } elseif ($days <= 60) {
                $grouped[$customer]['31-60'] += $balance;
            } elseif ($days <= 90) {
                $grouped[$customer]['61-90'] += $balance;
            } else {
                $grouped[$customer]['91+'] += $balance;
            }
            $grouped[$customer]['Total'] += $balance;
        }

        return view('accounts_receivable.aging', [
            'today' => $today,
            'grouped' => $grouped,
        ]);
    }

    public function exportAgingReport(Request $request)
    {
        return back()->with('info', 'Export (PDF/Excel) coming soon');
    }
}

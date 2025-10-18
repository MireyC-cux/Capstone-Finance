<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\CashFlow;
use App\Models\BusinessFinancial;
use App\Models\Expenses;
use Barryvdh\DomPDF\Facade\Pdf;

class CashFlowController extends Controller
{
    public function index(Request $request)
    {
        $bf = BusinessFinancial::first();
        if (!$bf) {
            $bf = BusinessFinancial::create([
                'capital' => 0,
                'total_inflows' => 0,
                'total_outflows' => 0,
                'as_of_date' => now()->toDateString(),
            ]);
        }
        // Ensure up-to-date totals
        BusinessFinancial::recalcTotals();
        $bf->refresh();

        $totalInflows = (float) $bf->total_inflows;
        $totalOutflows = (float) $bf->total_outflows;
        $currentBalance = (float) $bf->current_balance;
        $profit = (float) $bf->profit;

        $start = Carbon::now()->subMonths(11)->startOfMonth();
        $series = CashFlow::query()
            ->where('transaction_date', '>=', $start->toDateString())
            ->where(function ($q) {
                $q->where('source_type', '!=', 'Other')
                  ->orWhere(function ($q2) {
                      $q2->where('source_type', 'Other')
                         ->where(function ($qq) {
                             $qq->whereNull('description')
                                ->orWhere('description', 'not like', 'Owner Capital%');
                         });
                  });
            })
            ->selectRaw("DATE_FORMAT(transaction_date,'%Y-%m') as ym, SUM(CASE WHEN transaction_type='Inflow' THEN amount ELSE 0 END) as inflow, SUM(CASE WHEN transaction_type='Outflow' THEN amount ELSE 0 END) as outflow")
            ->groupBy('ym')
            ->orderBy('ym')
            ->get();

        // Compute profit per month for chart convenience
        $profitSeries = $series->map(function ($row) {
            $row->profit = (float)($row->inflow ?? 0) - (float)($row->outflow ?? 0);
            return $row;
        });

        $recent = CashFlow::orderByDesc('transaction_date')->orderByDesc('cashflow_id')->limit(10)->get();

        $expenseCategories = ['Utilities','Maintenance','Transportation','Office Supplies','Other'];
        $categoryBreakdown = Expenses::select('category', DB::raw('SUM(amount) as total'))
            ->where('expense_date', '>=', $start->toDateString())
            ->groupBy('category')
            ->pluck('total', 'category');

        return view('finance.cashflow.index', compact(
            'bf', 'totalInflows', 'totalOutflows', 'currentBalance', 'profit', 'series', 'profitSeries', 'recent', 'expenseCategories', 'categoryBreakdown'
        ));
    }

    public function exportPdf(Request $request)
    {
        $start = $request->filled('start') ? Carbon::parse($request->start)->startOfDay() : Carbon::now()->startOfMonth();
        $end = $request->filled('end') ? Carbon::parse($request->end)->endOfDay() : Carbon::now()->endOfMonth();

        $flows = CashFlow::whereBetween('transaction_date', [$start->toDateString(), $end->toDateString()])
            ->orderBy('transaction_date')
            ->get();

        $totals = [
            'inflows' => (float)$flows->where('transaction_type', 'Inflow')->sum('amount'),
            'outflows' => (float)$flows->where('transaction_type', 'Outflow')->sum('amount'),
        ];
        $totals['profit'] = $totals['inflows'] - $totals['outflows'];

        $pdf = Pdf::setOptions([
            'defaultFont' => 'DejaVu Sans',
            'isRemoteEnabled' => true,
        ])->loadView('finance.cashflow.report_pdf', [
            'flows' => $flows,
            'start' => $start->toDateString(),
            'end' => $end->toDateString(),
            'totals' => $totals,
        ]);
        return $pdf->download('cashflow_'.$start->format('Ymd').'-'.$end->format('Ymd').'.pdf');
    }

    public function exportCsv(Request $request)
    {
        $start = $request->filled('start') ? Carbon::parse($request->start)->startOfDay() : Carbon::now()->startOfMonth();
        $end = $request->filled('end') ? Carbon::parse($request->end)->endOfDay() : Carbon::now()->endOfMonth();

        $rows = CashFlow::whereBetween('transaction_date', [$start->toDateString(), $end->toDateString()])
            ->orderBy('transaction_date')
            ->get(['transaction_date','transaction_type','source_type','source_id','amount','description']);

        $filename = 'cashflow_'.$start->format('Ymd').'-'.$end->format('Ymd').'.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($rows) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Date','Type','Source','Source ID','Amount','Description']);
            foreach ($rows as $r) {
                fputcsv($handle, [
                    $r->transaction_date->format('Y-m-d'),
                    $r->transaction_type,
                    $r->source_type,
                    $r->source_id,
                    number_format((float)$r->amount, 2, '.', ''),
                    $r->description,
                ]);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}

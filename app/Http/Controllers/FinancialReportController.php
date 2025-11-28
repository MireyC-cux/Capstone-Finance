<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\ServiceRequest;
use App\Models\ServiceRequestItem;
use App\Models\Expenses;
use App\Models\Payroll;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\BusinessFinancial;

class FinancialReportController extends Controller
{
    // GET /admin/financial-report
    public function index(Request $request)
    {
        // Render the finance reports index view
        return view('finance.reports.index');
    }

    private function dateRange(Request $request): array
    {
        // Optional: last N months ending today
        $lastMonths = (int)($request->query('last_months', 0));
        if ($lastMonths > 0) {
            $end = Carbon::now()->endOfDay();
            $start = (clone $end)->subMonths(max(0, $lastMonths - 1))->startOfMonth();
            return [$start, $end, (int)$start->year, null];
        }
        $year = (int)($request->query('year', now()->year));
        $month = $request->query('month');
        if ($month) {
            $start = Carbon::create($year, (int)$month, 1)->startOfMonth();
            $end = (clone $start)->endOfMonth();
        } else {
            $start = Carbon::create($year, 1, 1)->startOfYear();
            $end = (clone $start)->endOfYear();
        }
        return [$start, $end, $year, $month ? (int)$month : null];
    }

    public function getKpis(Request $request)
    {
        [$start, $end] = $this->dateRange($request);

        $latestInvBase = DB::table('invoices as i')
            ->join(DB::raw('(SELECT billing_id, MAX(invoice_date) as max_date FROM invoices GROUP BY billing_id) as mx'), function($j){
                $j->on('i.billing_id','=','mx.billing_id')->on('i.invoice_date','=','mx.max_date');
            })
            ->select('i.billing_id','i.status');

        $revenue = DB::table('service_requests as sr')
            ->leftJoin('billings as b', 'b.service_request_id', '=', 'sr.service_request_id')
            ->leftJoinSub($latestInvBase, 'latest_inv', function($join){ $join->on('b.billing_id','=','latest_inv.billing_id'); })
            ->where('latest_inv.status', 'Paid')
            ->whereBetween('sr.created_at', [$start, $end])
            ->sum('sr.order_total');

        $expenses = Expenses::whereBetween('expense_date', [$start, $end])
            ->sum('amount');

        // Payroll: prefer net_pay if exists, else compute
        $payrollRows = Payroll::whereBetween('pay_period_start', [$start, $end])->get();
        $payroll = $payrollRows->sum(function ($p) {
            return (float)($p->net_pay ?? ($p->basic_salary + $p->overtime_pay + ($p->bonuses ?? 0) - ($p->deductions ?? 0)));
        });

        $netProfit = $revenue - $expenses - $payroll;
        $profitMargin = $revenue > 0 ? ($netProfit / $revenue) * 100 : 0;

        return response()->json([
            'revenue' => round($revenue, 2),
            'expenses' => round($expenses, 2),
            'payroll' => round($payroll, 2),
            'net_profit' => round($netProfit, 2),
            'profit_margin' => round($profitMargin, 2),
        ]);
    }

    public function getRevenueTrend(Request $request)
    {
        [$start, $end] = $this->dateRange($request);

        $latestInvBase = DB::table('invoices as i')
            ->join(DB::raw('(SELECT billing_id, MAX(invoice_date) as max_date FROM invoices GROUP BY billing_id) as mx'), function($j){
                $j->on('i.billing_id','=','mx.billing_id')->on('i.invoice_date','=','mx.max_date');
            })
            ->select('i.billing_id','i.status');

        $map = DB::table('service_requests as sr')
            ->leftJoin('billings as b', 'b.service_request_id', '=', 'sr.service_request_id')
            ->leftJoinSub($latestInvBase, 'latest_inv', function($join){ $join->on('b.billing_id','=','latest_inv.billing_id'); })
            ->select(
                DB::raw("DATE_FORMAT(sr.created_at, '%Y-%m') as ym"),
                DB::raw('SUM(sr.order_total) as total')
            )
            ->where('latest_inv.status', 'Paid')
            ->whereBetween('sr.created_at', [$start, $end])
            ->groupBy('ym')
            ->orderBy('ym')
            ->pluck('total', 'ym');

        $labels = [];
        $values = [];
        $cur = (clone $start)->startOfMonth();
        $endMonth = (clone $end)->startOfMonth();
        while ($cur <= $endMonth) {
            $key = $cur->format('Y-m');
            $labels[] = $cur->format('F Y');
            $values[] = (float)($map[$key] ?? 0);
            $cur->addMonth();
        }
        return response()->json(['labels' => $labels, 'values' => $values]);
    }

    public function getExpenseBreakdown(Request $request)
    {
        [$start, $end] = $this->dateRange($request);
        $rows = Expenses::select('category', DB::raw('SUM(amount) as total'))
            ->whereBetween('expense_date', [$start, $end])
            ->groupBy('category')
            ->orderByDesc('total')
            ->get();
        return response()->json([
            'labels' => $rows->pluck('category'),
            'values' => $rows->pluck('total')->map(fn($v) => (float)$v),
        ]);
    }

    public function getPayrollSummary(Request $request)
    {
        [$start, $end] = $this->dateRange($request);
        $rows = Payroll::select(
                DB::raw("DATE_FORMAT(pay_period_start, '%Y-%m') as ym"),
                DB::raw('SUM(COALESCE(net_pay, basic_salary + overtime_pay + COALESCE(bonuses,0) - COALESCE(deductions,0))) as total')
            )
            ->whereBetween('pay_period_start', [$start, $end])
            ->groupBy('ym')
            ->orderBy('ym')
            ->pluck('total', 'ym');

        $labels = [];
        $values = [];
        $cur = (clone $start)->startOfMonth();
        $endMonth = (clone $end)->startOfMonth();
        while ($cur <= $endMonth) {
            $key = $cur->format('Y-m');
            $labels[] = $cur->format('F Y');
            $values[] = (float)($rows[$key] ?? 0);
            $cur->addMonth();
        }
        return response()->json(['labels' => $labels, 'values' => $values]);
    }

    public function getNetProfitData(Request $request)
    {
        [$start, $end] = $this->dateRange($request);

        $latestInvBase = DB::table('invoices as i')
            ->join(DB::raw('(SELECT billing_id, MAX(invoice_date) as max_date FROM invoices GROUP BY billing_id) as mx'), function($j){
                $j->on('i.billing_id','=','mx.billing_id')->on('i.invoice_date','=','mx.max_date');
            })
            ->select('i.billing_id','i.status');

        $rev = DB::table('service_requests as sr')
            ->leftJoin('billings as b', 'b.service_request_id', '=', 'sr.service_request_id')
            ->leftJoinSub($latestInvBase, 'latest_inv', function($join){ $join->on('b.billing_id','=','latest_inv.billing_id'); })
            ->select(DB::raw("DATE_FORMAT(sr.created_at, '%Y-%m') as ym"), DB::raw('SUM(sr.order_total) as total'))
            ->where('latest_inv.status', 'Paid')
            ->whereBetween('sr.created_at', [$start, $end])
            ->groupBy('ym')->pluck('total', 'ym');

        $exp = Expenses::select(DB::raw("DATE_FORMAT(expense_date, '%Y-%m') as ym"), DB::raw('SUM(amount) as total'))
            ->whereBetween('expense_date', [$start, $end])
            ->groupBy('ym')->pluck('total', 'ym');

        $pay = Payroll::select(DB::raw("DATE_FORMAT(pay_period_start, '%Y-%m') as ym"))
            ->whereBetween('pay_period_start', [$start, $end])
            ->get()->groupBy('ym')->map(function ($group) {
                return $group->sum(function ($p) {
                    return (float)($p->net_pay ?? ($p->basic_salary + $p->overtime_pay + ($p->bonuses ?? 0) - ($p->deductions ?? 0)));
                });
            });

        $labels = [];
        $revenue = [];
        $expenses = [];
        $payroll = [];
        $profit = [];
        $cur = (clone $start)->startOfMonth();
        $endMonth = (clone $end)->startOfMonth();
        while ($cur <= $endMonth) {
            $key = $cur->format('Y-m');
            $labels[] = $cur->format('F Y');
            $r = (float)($rev[$key] ?? 0);
            $e = (float)($exp[$key] ?? 0);
            $p = (float)($pay[$key] ?? 0);
            $revenue[] = $r;
            $expenses[] = $e;
            $payroll[] = $p;
            $profit[] = $r - $e - $p;
            $cur->addMonth();
        }
        return response()->json([
            'labels' => $labels,
            'revenue' => $revenue,
            'expenses' => $expenses,
            'payroll' => $payroll,
            'profit' => $profit,
        ]);
    }

    public function getCashFlowData(Request $request)
    {
        [$start, $end] = $this->dateRange($request);

        $latestInvBase = DB::table('invoices as i')
            ->join(DB::raw('(SELECT billing_id, MAX(invoice_date) as max_date FROM invoices GROUP BY billing_id) as mx'), function($j){
                $j->on('i.billing_id','=','mx.billing_id')->on('i.invoice_date','=','mx.max_date');
            })
            ->select('i.billing_id','i.status');

        $rev = DB::table('service_requests as sr')
            ->leftJoin('billings as b', 'b.service_request_id', '=', 'sr.service_request_id')
            ->leftJoinSub($latestInvBase, 'latest_inv', function($join){ $join->on('b.billing_id','=','latest_inv.billing_id'); })
            ->select(DB::raw("DATE_FORMAT(sr.created_at, '%Y-%m') as ym"), DB::raw('SUM(sr.order_total) as total'))
            ->where('latest_inv.status', 'Paid')
            ->whereBetween('sr.created_at', [$start, $end])
            ->groupBy('ym')->pluck('total', 'ym');

        $exp = Expenses::select(DB::raw("DATE_FORMAT(expense_date, '%Y-%m') as ym"), DB::raw('SUM(amount) as total'))
            ->whereBetween('expense_date', [$start, $end])
            ->groupBy('ym')->pluck('total', 'ym');

        $pay = Payroll::select(DB::raw("DATE_FORMAT(pay_period_start, '%Y-%m') as ym"))
            ->whereBetween('pay_period_start', [$start, $end])
            ->get()->groupBy('ym')->map(function ($group) {
                return $group->sum(function ($p) {
                    return (float)($p->net_pay ?? ($p->basic_salary + $p->overtime_pay + ($p->bonuses ?? 0) - ($p->deductions ?? 0)));
                });
            });

        $po = PurchaseOrder::select(DB::raw("DATE_FORMAT(order_date, '%Y-%m') as ym"), DB::raw('SUM(total_amount) as total'))
            ->whereBetween('order_date', [$start, $end])
            ->groupBy('ym')->pluck('total', 'ym');

        $labels = [];
        $inflows = [];
        $outflows = [];
        $cur = (clone $start)->startOfMonth();
        $endMonth = (clone $end)->startOfMonth();
        while ($cur <= $endMonth) {
            $key = $cur->format('Y-m');
            $labels[] = $cur->format('F Y');
            $r = (float)($rev[$key] ?? 0);
            $e = (float)($exp[$key] ?? 0);
            $p = (float)($pay[$key] ?? 0);
            $poTot = (float)($po[$key] ?? 0);
            $inflows[] = $r;
            $outflows[] = $e + $p + $poTot;
            $cur->addMonth();
        }
        return response()->json([
            'labels' => $labels,
            'inflows' => $inflows,
            'outflows' => $outflows,
        ]);
    }

    public function getTaxSummary(Request $request)
    {
        [$start, $end] = $this->dateRange($request);
        // Use overall_tax_amount from service_requests per month as tax collected proxy
        $map = ServiceRequest::select(DB::raw("DATE_FORMAT(created_at, '%Y-%m') as ym"), DB::raw('SUM(overall_tax_amount) as total'))
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('ym')
            ->orderBy('ym')
            ->pluck('total', 'ym');

        $labels = [];
        $values = [];
        $cur = (clone $start)->startOfMonth();
        $endMonth = (clone $end)->startOfMonth();
        while ($cur <= $endMonth) {
            $key = $cur->format('Y-m');
            $labels[] = $cur->format('F Y');
            $values[] = (float)($map[$key] ?? 0);
            $cur->addMonth();
        }
        return response()->json(['labels' => $labels, 'values' => $values]);
    }

    public function getPaymentStatusSummary(Request $request)
    {
        [$start, $end] = $this->dateRange($request);
        $latestInvBase = DB::table('invoices as i')
            ->join(DB::raw('(SELECT billing_id, MAX(invoice_date) as max_date FROM invoices GROUP BY billing_id) as mx'), function($j){
                $j->on('i.billing_id','=','mx.billing_id')->on('i.invoice_date','=','mx.max_date');
            })
            ->select('i.billing_id','i.status');

        $rows = DB::table('service_requests as sr')
            ->leftJoin('billings as b', 'b.service_request_id', '=', 'sr.service_request_id')
            ->leftJoinSub($latestInvBase, 'latest_inv', function($join){ $join->on('b.billing_id','=','latest_inv.billing_id'); })
            ->select(DB::raw("COALESCE(latest_inv.status, 'Unpaid') as status"), DB::raw('COUNT(*) as cnt'))
            ->whereBetween('sr.created_at', [$start, $end])
            ->groupBy('status')
            ->orderBy('status')
            ->get();
        return response()->json([
            'labels' => $rows->pluck('status'),
            'values' => $rows->pluck('cnt')->map(fn($v) => (int)$v),
        ]);
    }

    public function getSupplierSpending(Request $request)
    {
        [$start, $end] = $this->dateRange($request);
        $rows = PurchaseOrder::select('supplier_id', DB::raw('SUM(total_amount) as total'))
            ->whereBetween('order_date', [$start, $end])
            ->groupBy('supplier_id')
            ->with('supplier')
            ->get();
        $labels = $rows->map(fn($r) => optional($r->supplier)->supplier_name ?? ('Supplier #' . $r->supplier_id));
        $values = $rows->pluck('total')->map(fn($v) => (float)$v);
        return response()->json(['labels' => $labels, 'values' => $values]);
    }
}

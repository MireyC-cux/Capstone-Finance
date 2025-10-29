<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\PaymentReceived;
use App\Models\Expenses;
use App\Models\EmployeeProfile;
use App\Models\Invoice;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Schema;
use App\Models\ActivityLog;

class FinanceController extends Controller
{
    public function home()
    {
        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth()->toDateString();
        $endOfMonth = $now->copy()->endOfMonth()->toDateString();
        $startOfLastMonth = $now->copy()->subMonthNoOverflow()->startOfMonth()->toDateString();
        $endOfLastMonth = $now->copy()->subMonthNoOverflow()->endOfMonth()->toDateString();

        $revenueThisMonth = (float) PaymentReceived::whereBetween('payment_date', [$startOfMonth, $endOfMonth])->sum('amount');
        $revenueLastMonth = (float) PaymentReceived::whereBetween('payment_date', [$startOfLastMonth, $endOfLastMonth])->sum('amount');

        $expensesThisMonth = (float) Expenses::whereBetween('expense_date', [$startOfMonth, $endOfMonth])->sum('amount');
        $expensesLastMonth = (float) Expenses::whereBetween('expense_date', [$startOfLastMonth, $endOfLastMonth])->sum('amount');

        $profitThisMonth = $revenueThisMonth - $expensesThisMonth;
        $profitLastMonth = $revenueLastMonth - $expensesLastMonth;

        $pctChange = function (float $current, float $previous): array {
            if ($previous == 0.0) {
                if ($current == 0.0) {
                    return [0.0, true];
                }
                return [100.0, true];
            }
            $change = (($current - $previous) / $previous) * 100.0;
            return [$change, $change >= 0];
        };

        [$revenueChangePct, $revenuePositive] = $pctChange($revenueThisMonth, $revenueLastMonth);
        [$expensesChangePct, $expensesPositive] = $pctChange($expensesThisMonth, $expensesLastMonth);
        [$profitChangePct, $profitPositive] = $pctChange($profitThisMonth, $profitLastMonth);

        $activeEmployees = (int) EmployeeProfile::where('status', 'Active')->count();
        if ($activeEmployees === 0) {
            $activeEmployees = (int) EmployeeProfile::count();
        }
        $newEmployeesThisMonth = (int) EmployeeProfile::whereBetween('hire_date', [$startOfMonth, $endOfMonth])->count();

        // Build revenue chart data for last 6 months (including current)
        $revenueChartLabels = [];
        $revenueChartData = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = $now->copy()->subMonths($i);
            $mStart = $month->copy()->startOfMonth()->toDateString();
            $mEnd = $month->copy()->endOfMonth()->toDateString();
            $sum = (float) PaymentReceived::whereBetween('payment_date', [$mStart, $mEnd])->sum('amount');
            $revenueChartLabels[] = $month->format('M Y');
            $revenueChartData[] = round($sum, 2);
        }

        $iconMap = [
            'ar_payment' => ['icon' => 'fas fa-money-bill-wave', 'bg' => '#ecfdf5', 'color' => '#10b981'],
            'po_approved' => ['icon' => 'fas fa-file-signature', 'bg' => '#eff6ff', 'color' => '#3b82f6'],
            'payroll_disbursed' => ['icon' => 'fas fa-wallet', 'bg' => '#fff7ed', 'color' => '#f97316'],
            'capital_injected' => ['icon' => 'fas fa-arrow-down', 'bg' => '#ecfdf5', 'color' => '#10b981'],
            'capital_withdrawn' => ['icon' => 'fas fa-arrow-up', 'bg' => '#fef2f2', 'color' => '#ef4444'],
            'expense_recorded' => ['icon' => 'fas fa-receipt', 'bg' => '#fef3c7', 'color' => '#f59e0b'],
            'inventory_item_created' => ['icon' => 'fas fa-box-open', 'bg' => '#eef2ff', 'color' => '#6366f1'],
            'inventory_item_updated' => ['icon' => 'fas fa-edit', 'bg' => '#f0fdf4', 'color' => '#22c55e'],
            'inventory_item_deleted' => ['icon' => 'fas fa-trash', 'bg' => '#fef2f2', 'color' => '#ef4444'],
        ];

        $activities = [];
        if (Schema::hasTable('activity_logs')) {
            $activities = ActivityLog::orderByDesc('id')->limit(8)->get()->map(function ($log) use ($iconMap) {
                $map = $iconMap[$log->event_type] ?? ['icon' => 'fas fa-info-circle', 'bg' => '#f3f4f6', 'color' => '#6b7280'];
                return [
                    'icon' => $map['icon'],
                    'bg' => $map['bg'],
                    'color' => $map['color'],
                    'title' => $log->title,
                    'when' => $log->created_at ? Carbon::parse($log->created_at) : null,
                ];
            })->toArray();
        }

        $formatMoney = fn (float $v) => number_format($v, 0);

        return view('home', [
            'totalRevenue' => $formatMoney($revenueThisMonth),
            'revenueChangePct' => round($revenueChangePct, 1),
            'revenueChangeDirection' => $revenuePositive ? 'positive' : 'negative',

            'totalExpenses' => $formatMoney($expensesThisMonth),
            'expensesChangePct' => round($expensesChangePct, 1),
            'expensesChangeDirection' => $expensesPositive ? 'negative' : 'positive',

            'netProfit' => $formatMoney($profitThisMonth),
            'profitChangePct' => round($profitChangePct, 1),
            'profitChangeDirection' => $profitPositive ? 'positive' : 'negative',

            'activeEmployees' => $activeEmployees,
            'newEmployeesThisMonth' => $newEmployeesThisMonth,
            'activities' => $activities,
            'revenueChartLabels' => $revenueChartLabels,
            'revenueChartData' => $revenueChartData,
        ]);
    }

    public function accountsReceivable()
    {
        return redirect()->route('accounts-receivable.index');
    }

    public function reports(Request $request)
    {
        $now = Carbon::now();
        $start = $request->filled('start') ? Carbon::parse($request->start)->startOfDay() : $now->copy()->subMonths(11)->startOfMonth();
        $end = $request->filled('end') ? Carbon::parse($request->end)->endOfDay() : $now->copy()->endOfDay();
        $s = $start->toDateString();
        $e = $end->toDateString();

        $monthKeys = function (string $s, string $e) {
            $ss = Carbon::parse($s)->startOfMonth();
            $ee = Carbon::parse($e)->endOfMonth();
            $keys = [];
            while ($ss <= $ee) { $keys[] = $ss->format('Y-m'); $ss->addMonth(); }
            return $keys;
        };
        $months = $monthKeys($s, $e);

        $salesRows = DB::table('service_requests')
            ->where('order_status', 'Completed')
            ->whereRaw('COALESCE(accomplishment_date, created_at) between ? and ?', [$s, $e])
            ->selectRaw('DATE_FORMAT(COALESCE(accomplishment_date, created_at), "%Y-%m") as ym, SUM(COALESCE(order_total,0)) as sales')
            ->groupBy('ym')->orderBy('ym')->get();
        $paidRows = DB::table('payments_received')
            ->whereBetween('payment_date', [$s, $e])
            ->selectRaw('DATE_FORMAT(payment_date, "%Y-%m") as ym, SUM(amount) as paid')
            ->groupBy('ym')->orderBy('ym')->get();
        $overdueRows = DB::table('accounts_receivable')
            ->where('status', 'Overdue')
            ->whereBetween('due_date', [$s, $e])
            ->selectRaw('DATE_FORMAT(due_date, "%Y-%m") as ym, SUM(total_amount - amount_paid) as overdue')
            ->groupBy('ym')->pluck('overdue', 'ym');
        $salesMap = $salesRows->pluck('sales', 'ym');
        $paidMap = $paidRows->pluck('paid', 'ym');
        $revSeries = [];$revTable=[];$revSalesTot=0.0;$revPaidTot=0.0;
        foreach ($months as $m) { $sales=(float)($salesMap[$m]??0); $paid=(float)($paidMap[$m]??0); $unpaid=max(0,$sales-$paid); $overdue=(float)($overdueRows[$m]??0); $revSeries[]=['ym'=>$m,'sales'=>$sales,'paid'=>$paid,'unpaid'=>$unpaid,'overdue'=>$overdue]; $revTable[]=['month'=>$m,'sales'=>$sales,'paid'=>$paid,'unpaid'=>$unpaid,'overdue'=>$overdue]; $revSalesTot+=$sales; $revPaidTot+=$paid; }
        $revOutstandingTot = (float)(DB::table('accounts_receivable')->whereIn('status',['Unpaid','Partially Paid','Overdue'])->selectRaw('SUM(total_amount - amount_paid) as v')->value('v') ?? 0);
        $revOverdueTot = (float)(DB::table('accounts_receivable')->whereIn('status',['Unpaid','Partially Paid','Overdue'])->whereDate('due_date','<',Carbon::today()->toDateString())->selectRaw('SUM(total_amount - amount_paid) as v')->value('v') ?? 0);
        $revenue = ['series'=>$revSeries,'table'=>$revTable,'totals'=>['sales_total'=>$revSalesTot,'paid_total'=>$revPaidTot,'outstanding_total'=>$revOutstandingTot,'overdue_total'=>$revOverdueTot]];

        $expCat = DB::table('expenses')->whereBetween('expense_date', [$s, $e])->select('category', DB::raw('SUM(amount) as total'))->groupBy('category')->pluck('total','category')->toArray();
        $expMonthMap = DB::table('expenses')->whereBetween('expense_date', [$s, $e])->selectRaw('DATE_FORMAT(expense_date, "%Y-%m") as ym, SUM(amount) as total')->groupBy('ym')->orderBy('ym')->pluck('total','ym');
        $paidRevenueMap = $paidRows->pluck('paid','ym');
        $expSeries=[];$expTable=[];$expTotal=0.0; foreach ($months as $m) { $exp=(float)($expMonthMap[$m]??0); $rev=(float)($paidRevenueMap[$m]??0); $ratio=$rev>0?($exp/$rev):null; $expSeries[]=['ym'=>$m,'expense'=>$exp]; $expTable[]=['month'=>$m,'expense'=>$exp,'revenue'=>$rev,'ratio'=>$ratio]; $expTotal+=$exp; }
        $expenses = ['by_category'=>$expCat,'series'=>$expSeries,'table'=>$expTable,'totals'=>['expense_total'=>$expTotal]];

        $today = Carbon::today();
        $ars = DB::table('accounts_receivable as ar')
            ->leftJoin('customers as c','c.customer_id','=','ar.customer_id')
            ->whereIn('ar.status',['Unpaid','Partially Paid','Overdue'])
            ->select('ar.ar_id','c.business_name','c.full_name','ar.total_amount','ar.amount_paid','ar.due_date')
            ->get();
        $arAdjByAr = DB::table('ar_adjustments')
            ->whereIn('type', ['Discount','Write-off'])
            ->select('ar_id', DB::raw('SUM(amount) as total'))
            ->groupBy('ar_id')
            ->pluck('total','ar_id');
        $arGrouped=[]; 
        foreach ($ars as $ar) { 
            $customer = $ar->business_name ?: ($ar->full_name ?: 'Unknown'); 
            if (!isset($arGrouped[$customer])) { 
                $arGrouped[$customer]=[
                    'customer'=>$customer,
                    'total'=>0.0,
                    'paid'=>0.0,
                    'balance'=>0.0,
                    'aging'=>['Current'=>0,'1-30'=>0,'31-60'=>0,'61-90'=>0,'91+'=>0]
                ]; 
            } 
            $adj = (float)($arAdjByAr[$ar->ar_id] ?? 0);
            $netTotal = max(0.0, (float)$ar->total_amount - $adj);
            $bal = max(0.0, $netTotal - (float)$ar->amount_paid);
            $arGrouped[$customer]['total'] += $netTotal; 
            $arGrouped[$customer]['paid'] += (float)$ar->amount_paid; 
            $arGrouped[$customer]['balance'] += $bal; 
            $days = Carbon::parse($ar->due_date)->diffInDays($today, false); 
            if ($days<=0) $arGrouped[$customer]['aging']['Current'] += $bal; 
            elseif($days<=30) $arGrouped[$customer]['aging']['1-30'] += $bal; 
            elseif($days<=60) $arGrouped[$customer]['aging']['31-60'] += $bal; 
            elseif($days<=90) $arGrouped[$customer]['aging']['61-90'] += $bal; 
            else $arGrouped[$customer]['aging']['91+'] += $bal; 
        }
        $arRows = array_values($arGrouped); 
        $arTotals=[
            'total'=>array_sum(array_column($arRows,'total')),
            'paid'=>array_sum(array_column($arRows,'paid')),
            'balance'=>array_sum(array_column($arRows,'balance'))
        ];
        $ar = ['rows'=>$arRows,'totals'=>$arTotals];

        $aps = DB::table('accounts_payable as ap')->leftJoin('suppliers as s','s.supplier_id','=','ap.supplier_id')->whereIn('ap.status',['Unpaid','Partially Paid','Overdue'])->select('s.supplier_name','ap.total_amount','ap.amount_paid','ap.due_date')->get();
        $apGrouped=[]; foreach ($aps as $apRow) { $supplier = $apRow->supplier_name ?: 'Unknown'; if (!isset($apGrouped[$supplier])) { $apGrouped[$supplier]=['supplier'=>$supplier,'total'=>0.0,'paid'=>0.0,'balance'=>0.0,'aging'=>['Current'=>0,'1-30'=>0,'31-60'=>0,'61-90'=>0,'91+'=>0]]; } $bal = max(0,(float)$apRow->total_amount-(float)$apRow->amount_paid); $apGrouped[$supplier]['total']+=(float)$apRow->total_amount; $apGrouped[$supplier]['paid']+=(float)$apRow->amount_paid; $apGrouped[$supplier]['balance']+=$bal; $days = Carbon::parse($apRow->due_date)->diffInDays($today, false); if ($days<=0) $apGrouped[$supplier]['aging']['Current']+=$bal; elseif($days<=30) $apGrouped[$supplier]['aging']['1-30']+=$bal; elseif($days<=60) $apGrouped[$supplier]['aging']['31-60']+=$bal; elseif($days<=90) $apGrouped[$supplier]['aging']['61-90']+=$bal; else $apGrouped[$supplier]['aging']['91+']+=$bal; }
        $apRows = array_values($apGrouped); $apTotals=['total'=>array_sum(array_column($apRows,'total')),'paid'=>array_sum(array_column($apRows,'paid')),'balance'=>array_sum(array_column($apRows,'balance'))];
        $ap = ['rows'=>$apRows,'totals'=>$apTotals];

        $cfRows = DB::table('cash_flow')->whereBetween('transaction_date', [$s, $e])->selectRaw("DATE_FORMAT(transaction_date,'%Y-%m') as ym, SUM(CASE WHEN transaction_type='Inflow' THEN amount ELSE 0 END) as inflow, SUM(CASE WHEN transaction_type='Outflow' THEN amount ELSE 0 END) as outflow")->groupBy('ym')->orderBy('ym')->get()->keyBy('ym');
        $cfSeries=[];$cfTable=[];$totIn=0.0;$totOut=0.0; foreach ($months as $m) { $in=(float)($cfRows[$m]->inflow??0); $out=(float)($cfRows[$m]->outflow??0); $net=$in-$out; $cfSeries[]=['ym'=>$m,'inflow'=>$in,'outflow'=>$out,'net'=>$net]; $cfTable[]=['month'=>$m,'inflow'=>$in,'outflow'=>$out,'net'=>$net]; $totIn+=$in; $totOut+=$out; }
        $cash = ['series'=>$cfSeries,'table'=>$cfTable,'totals'=>['inflow'=>$totIn,'outflow'=>$totOut,'net'=>$totIn-$totOut]];

        $payrolls = DB::table('payrolls')
            ->whereBetween('pay_period_start', [$s, $e])
            ->select('payroll_id','pay_period','salary_rate','total_days_of_work','overtime_pay','deductions','status')
            ->orderBy('pay_period')
            ->get();
        $paidIds = DB::table('payroll_disbursements')->where('status','Paid')->whereBetween('payment_date', [$s, $e])->pluck('payroll_id')->unique()->toArray();
        $byPeriod=[]; foreach ($payrolls as $p) { $period=$p->pay_period; if (!isset($byPeriod[$period])) { $byPeriod[$period]=['period'=>$period,'total'=>0.0,'paid'=>0.0]; } $base=(float)$p->salary_rate * (float)$p->total_days_of_work; $ot=(float)($p->overtime_pay ?? 0); $ded=(float)($p->deductions ?? 0); $amount=max(0.0, $base + $ot - $ded); $byPeriod[$period]['total'] += $amount; if (in_array($p->payroll_id, $paidIds, true) || strcasecmp($p->status,'Paid')===0) { $byPeriod[$period]['paid'] += $amount; } }
        $pyRows = array_values(array_map(function($r){ $r['pending']=max(0.0,$r['total']-$r['paid']); return $r; }, $byPeriod));
        $payroll = ['rows'=>$pyRows,'totals'=>['total'=>array_sum(array_column($pyRows,'total')),'paid'=>array_sum(array_column($pyRows,'paid')),'pending'=>array_sum(array_column($pyRows,'pending'))]];

        $incomeMap = $paidRows->pluck('paid','ym');
        $expenseMap = $expMonthMap;
        $payrollPaidMap = DB::table('payroll_disbursements as pd')
            ->join('payrolls as p','p.payroll_id','=','pd.payroll_id')
            ->where('pd.status','Paid')
            ->whereBetween('pd.payment_date', [$s, $e])
            ->selectRaw('DATE_FORMAT(pd.payment_date, "%Y-%m") as ym, SUM(COALESCE((p.salary_rate * p.total_days_of_work) + COALESCE(p.overtime_pay,0) - COALESCE(p.deductions,0), 0)) as total')
            ->groupBy('ym')
            ->orderBy('ym')
            ->pluck('total','ym');
        $apPaidMap = DB::table('payments_made')->whereBetween('payment_date', [$s, $e])->selectRaw('DATE_FORMAT(payment_date, "%Y-%m") as ym, SUM(amount) as total')->groupBy('ym')->orderBy('ym')->pluck('total','ym');
        $pnlTable=[]; $iTot=0.0;$xTot=0.0;$pyTot=0.0;$apTot=0.0;$netTot=0.0; foreach ($months as $m) { $i=(float)($incomeMap[$m]??0); $x=(float)($expenseMap[$m]??0); $py=(float)($payrollPaidMap[$m]??0); $apv=(float)($apPaidMap[$m]??0); $n=$i-($x+$py+$apv); $pnlTable[]=['month'=>$m,'income'=>$i,'expense'=>$x,'payroll'=>$py,'ap_payments'=>$apv,'net'=>$n]; $iTot+=$i; $xTot+=$x; $pyTot+=$py; $apTot+=$apv; $netTot+=$n; }
        $pnl = ['table'=>$pnlTable,'totals'=>['income'=>$iTot,'expense'=>$xTot,'payroll'=>$pyTot,'ap_payments'=>$apTot,'net'=>$netTot]];

        if ($request->filled('export') && $request->filled('type')) {
            $type = $request->get('type');
            $fmt = strtolower($request->get('export'));
            $file = $type.'_'.now()->format('Ymd_His');
            $csv = function(array $headers, array $rows) use ($file) {
                $filename = $file.'.csv';
                $headersOut = ['Content-Type' => 'text/csv', 'Content-Disposition' => "attachment; filename=\"{$filename}\""];
                return response()->stream(function() use($headers,$rows){ $h=fopen('php://output','w'); fputcsv($h,$headers); foreach($rows as $r){ fputcsv($h,$r);} fclose($h); }, 200, $headersOut);
            };
            if ($fmt === 'csv') {
                if ($type==='revenue') {
                    return $csv(['Month','Sales','Paid','Unpaid','Overdue'], array_map(fn($r)=>[$r['month'],$r['sales'],$r['paid'],$r['unpaid'],$r['overdue']], $revenue['table']));
                } elseif ($type==='expenses') {
                    return $csv(['Month','Expense','Revenue','Expense/Revenue'], array_map(fn($r)=>[$r['month'],$r['expense'],$r['revenue'],$r['ratio']], $expenses['table']));
                } elseif ($type==='cashflow') {
                    return $csv(['Month','Inflow','Outflow','Net'], array_map(fn($r)=>[$r['month'],$r['inflow'],$r['outflow'],$r['net']], $cash['table']));
                } elseif ($type==='payroll') {
                    return $csv(['Pay Period','Total','Paid','Pending'], array_map(fn($r)=>[$r['period'],$r['total'],$r['paid'],$r['pending']], $payroll['rows']));
                } elseif ($type==='pnl') {
                    return $csv(['Month','Income','Expense','Payroll','AP Payments','Net'], array_map(fn($r)=>[$r['month'],$r['income'],$r['expense'],$r['payroll'],$r['ap_payments'],$r['net']], $pnl['table']));
                } elseif ($type==='ar') {
                    return $csv(['Customer','Total','Paid','Balance'], array_map(fn($r)=>[$r['customer'],$r['total'],$r['paid'],$r['balance']], $ar['rows']));
                } elseif ($type==='ap') {
                    return $csv(['Supplier','Total','Paid','Balance'], array_map(fn($r)=>[$r['supplier'],$r['total'],$r['paid'],$r['balance']], $ap['rows']));
                }
            } elseif ($fmt === 'pdf') {
                $titleMap = ['revenue'=>'Revenue Report','expenses'=>'Expense Report','cashflow'=>'Cash Flow Report','payroll'=>'Payroll & Salary Expense','pnl'=>'Profit & Loss','ar'=>'Accounts Receivable','ap'=>'Accounts Payable'];
                $title = $titleMap[$type] ?? 'Report';
                $period = $s.' to '.$e;
                $headers=[]; $rows=[]; $totals=[];
                if ($type==='revenue') { $headers=['Month','Sales','Paid','Unpaid','Overdue']; $rows=array_map(fn($r)=>[$r['month'],$r['sales'],$r['paid'],$r['unpaid'],$r['overdue']], $revenue['table']); $totals=$revenue['totals']; }
                elseif ($type==='expenses') { $headers=['Month','Expense','Revenue','Expense/Revenue']; $rows=array_map(fn($r)=>[$r['month'],$r['expense'],$r['revenue'],$r['ratio']], $expenses['table']); $totals=$expenses['totals']; }
                elseif ($type==='cashflow') { $headers=['Month','Inflow','Outflow','Net']; $rows=array_map(fn($r)=>[$r['month'],$r['inflow'],$r['outflow'],$r['net']], $cash['table']); $totals=$cash['totals']; }
                elseif ($type==='payroll') { $headers=['Pay Period','Total','Paid','Pending']; $rows=array_map(fn($r)=>[$r['period'],$r['total'],$r['paid'],$r['pending']], $payroll['rows']); $totals=$payroll['totals']; }
                elseif ($type==='pnl') { $headers=['Month','Income','Expense','Payroll','AP Payments','Net']; $rows=array_map(fn($r)=>[$r['month'],$r['income'],$r['expense'],$r['payroll'],$r['ap_payments'],$r['net']], $pnl['table']); $totals=$pnl['totals']; }
                elseif ($type==='ar') { $headers=['Customer','Total','Paid','Balance']; $rows=array_map(fn($r)=>[$r['customer'],$r['total'],$r['paid'],$r['balance']], $ar['rows']); $totals=$ar['totals']; }
                elseif ($type==='ap') { $headers=['Supplier','Total','Paid','Balance']; $rows=array_map(fn($r)=>[$r['supplier'],$r['total'],$r['paid'],$r['balance']], $ap['rows']); $totals=$ap['totals']; }

                $thead = '<tr>'.implode('', array_map(fn($h)=>'<th>'.htmlspecialchars((string)$h).'</th>', $headers)).'</tr>';
                $tbody = '';
                foreach ($rows as $r) {
                    $tbody .= '<tr>';
                    foreach ($r as $cell) {
                        $tbody .= '<td>'.htmlspecialchars((string)$cell).'</td>';
                    }
                    $tbody .= '</tr>';
                }
                $tfoot = '';
                if (!empty($totals)) {
                    $colspan = max(1, count($headers)) - 1;
                    foreach ($totals as $k=>$v) {
                        $label = ucwords(str_replace('_',' ', (string)$k));
                        $val = number_format((float)$v, 2, '.', ',');
                        $tfoot .= '<tr><td colspan="'.$colspan.'">'.htmlspecialchars($label).'</td><td style="text-align:right">'.htmlspecialchars($val).'</td></tr>';
                    }
                }
                $html = '<!DOCTYPE html><html><head><meta charset="utf-8"><style>body{font-family:DejaVu Sans,sans-serif;font-size:12px}h1{font-size:18px;margin-bottom:6px}.meta{color:#555;margin-bottom:10px}table{width:100%;border-collapse:collapse}th,td{border:1px solid #ddd;padding:6px}th{background:#f3f4f6;text-align:left}tfoot td{font-weight:bold}</style></head><body><h1>'.htmlspecialchars($title).'</h1><div class="meta">Period: '.htmlspecialchars($period).'</div><table><thead>'.$thead.'</thead><tbody>'.$tbody.'</tbody><tfoot>'.$tfoot.'</tfoot></table></body></html>';
                $pdf = Pdf::setOptions(['defaultFont' => 'DejaVu Sans', 'isRemoteEnabled' => true])->loadHTML($html);
                return $pdf->download($file.'.pdf');
            }
        }

        return view('home', [
            'reportsMode' => true,
            'start' => $s,
            'end' => $e,
            'revenue' => $revenue,
            'expenses' => $expenses,
            'ar' => $ar,
            'ap' => $ap,
            'cash' => $cash,
            'payroll' => $payroll,
            'pnl' => $pnl,
        ]);
    }
}

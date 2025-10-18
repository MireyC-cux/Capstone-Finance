<?php

namespace App\Http\Controllers;

use App\Models\CashFlow;
use App\Models\Payroll;
use App\Models\PayrollDisbursement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use App\Models\ActivityLog;

class DisbursementController extends Controller
{
    public function index(Request $request)
    {
        $start = $request->filled('start') ? Carbon::parse($request->start) : Carbon::today()->startOfMonth();
        $end = $request->filled('end') ? Carbon::parse($request->end) : Carbon::today()->endOfMonth();

        $rows = PayrollDisbursement::with(['employeeProfile','payroll'])
            ->whereBetween('payment_date', [$start->toDateString(), $end->toDateString()])
            ->orderBy('payment_date', 'desc')
            ->paginate(20);

        return view('finance.disbursements.index', [
            'rows' => $rows,
            'start' => $start->toDateString(),
            'end' => $end->toDateString(),
        ]);
    }
    public function disburseSalary(Request $request)
    {
        $data = $request->validate([
            'payroll_id' => 'required|integer|exists:payrolls,payroll_id',
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:Cash,Bank Transfer,GCash,Check,Other',
            'reference_number' => 'nullable|string|max:255',
            'account_id' => 'nullable|integer|exists:cash_accounts,account_id',
        ]);

        $payroll = Payroll::with('employeeProfile')->findOrFail($data['payroll_id']);
        if ($payroll->status !== 'Approved') {
            return back()->with('error', 'Payroll must be Approved before disbursement.');
        }

        DB::transaction(function () use ($payroll, $data) {
            PayrollDisbursement::create([
                'payroll_id' => $payroll->payroll_id,
                'employeeprofiles_id' => $payroll->employeeprofiles_id,
                'payment_date' => $data['payment_date'],
                'payment_method' => $data['payment_method'],
                'reference_number' => $data['reference_number'] ?? null,
                'status' => 'Paid',
            ]);

            $payroll->update(['status' => 'Paid']);

            CashFlow::create([
                'transaction_type' => 'Outflow',
                'source_type' => 'Expense',
                'source_id' => $payroll->payroll_id,
                'account_id' => $data['account_id'] ?? null,
                'amount' => $payroll->net_pay,
                'transaction_date' => $data['payment_date'],
                'description' => 'Salary disbursement for payroll #'.$payroll->payroll_id,
            ]);

            ActivityLog::create([
                'event_type' => 'payroll_disbursed',
                'title' => 'Payroll #'.$payroll->payroll_id.' disbursed (₱'.number_format((float)$payroll->net_pay, 2).')',
                'context_type' => 'Payroll',
                'context_id' => $payroll->payroll_id,
                'amount' => $payroll->net_pay,
                'meta' => [
                    'employeeprofiles_id' => $payroll->employeeprofiles_id,
                    'payment_method' => $data['payment_method'],
                    'reference_number' => $data['reference_number'] ?? null,
                ],
            ]);
        });

        return back()->with('success', 'Salary disbursement recorded.');
    }

    public function exportTable(Request $request)
    {
        $start = $request->filled('start') ? Carbon::parse($request->start) : Carbon::today()->startOfMonth();
        $end = $request->filled('end') ? Carbon::parse($request->end) : Carbon::today()->endOfMonth();

        $rows = PayrollDisbursement::with(['employeeProfile', 'payroll'])
            ->whereBetween('payment_date', [$start->toDateString(), $end->toDateString()])
            ->orderBy('payment_date', 'desc')
            ->get();

        $pdf = Pdf::setOptions([
            'defaultFont' => 'DejaVu Sans',
            'isRemoteEnabled' => true,
        ])->loadView('finance.disbursements.table_pdf', [
            'rows' => $rows,
            'period' => $start->format('Y-m-d').' to '.$end->format('Y-m-d'),
        ]);
        return $pdf->download('disbursements_'.$start->format('Ymd').'-'.$end->format('Ymd').'.pdf');
    }
}

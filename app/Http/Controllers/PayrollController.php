<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\CashAdvance;
use App\Models\Deduction;
use App\Models\EmployeeProfile;
use App\Models\EmployeeSalaryRate;
use App\Models\Payroll;
use App\Models\SalaryRate;
use App\Models\Payslip;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\OvertimeRequest;



class PayrollController extends Controller
{


public function dashboard(Request $request)
{
    // ✅ Filters
    $employeeFilter = $request->input('employee');
    $positionFilter = $request->input('position');
    $filterType = $request->input('filter_type', 'period');
    $periodStartInput = $request->input('period_start');
    $periodEndInput = $request->input('period_end');
    $monthInput = $request->input('month'); // format YYYY-MM

    // ✅ Determine target range (pay period or whole month)
    if ($filterType === 'month' && !empty($monthInput)) {
        $start = Carbon::createFromFormat('Y-m', $monthInput)->startOfMonth();
        $end = Carbon::createFromFormat('Y-m', $monthInput)->endOfMonth();
    } elseif (!empty($periodStartInput) && !empty($periodEndInput)) {
        $start = Carbon::parse($periodStartInput);
        $end = Carbon::parse($periodEndInput);
    } else {
        // default to current semi-monthly
      $today = now();

if ($today->day <= 15) {
    $start = $today->copy()->startOfMonth();
    $end   = $today->copy()->setDay(15);
} else {
    $start = $today->copy()->setDay(16);
    $end   = $today->copy()->endOfMonth();
}

    }

    // ✅ Query payrolls with related employee profiles
    $query = Payroll::with('employeeprofiles');

    // ✅ Apply filters dynamically
    if (!empty($employeeFilter)) {
        $query->whereHas('employeeprofiles', function ($q) use ($employeeFilter) {
            $q->where('first_name', 'like', '%' . $employeeFilter . '%')
              ->orWhere('last_name', 'like', '%' . $employeeFilter . '%');
        });
    }

    if (!empty($positionFilter)) {
        $query->whereHas('employeeprofiles', function ($q) use ($positionFilter) {
            $q->where('position', 'like', '%' . $positionFilter . '%');
        });
    }

    // ✅ If a range is provided/derived, use it; otherwise fallback to latest per employee
    $query->whereDate('pay_period_start', '>=', $start->toDateString())
          ->whereDate('pay_period_end', '<=', $end->toDateString());

    $payrolls = $query->orderBy('pay_period_start')->get();

    // ✅ Convert payrolls into $rows array for your Blade view
    $rows = $payrolls->map(function ($payroll) {
        $employee = $payroll->employeeprofiles;
        $ps = Carbon::parse($payroll->pay_period_start);
        $pe = Carbon::parse($payroll->pay_period_end);

        // ✅ Fetch total approved OT hours from overtime_requests table for this employee & payroll's own period
        $totalOtHours = OvertimeRequest::where('employeeprofiles_id', $employee->employeeprofiles_id)
            ->where('status', 'Approved')
            ->whereBetween('approved_date', [$ps->toDateString(), $pe->toDateString()])
            ->sum('hours');

        // ✅ Compute OT pay from approved records within the payroll period
        $otPay = OvertimeRequest::where('employeeprofiles_id', $employee->employeeprofiles_id)
            ->where('status', 'Approved')
            ->whereBetween('approved_date', [$ps->toDateString(), $pe->toDateString()])
            ->sum('amount');

        return [
            'employee' => $employee,
            'position' => $employee->position ?? 'N/A',
            'period' => $payroll->pay_period ?? ($ps->toDateString().' - '.$pe->toDateString()),
            'days_worked' => $payroll->total_days_of_work ?? 0,
            'ot_hours' => $totalOtHours,
            'ot_pay' => $otPay,
            'deductions' => $payroll->deductions ?? 0,
            'cash_advance' => $payroll->cash_advance ?? 0,
            'net' => $payroll->net_pay ?? 0,
            'status' => $payroll->status ?? 'N/A',
            'payroll' => $payroll,
        ];
    });
    $approvalStatus = \App\Models\Expenses::latest()->value('admin_approval');

    // ✅ Return everything to the Blade view
    return view('finance.payroll.index', [
        'rows' => $rows,
        'filters' => [
            'employee' => $employeeFilter,
            'position' => $positionFilter,
            'filter_type' => $filterType,
            'period_start' => $start->toDateString(),
            'period_end' => $end->toDateString(),
            'month' => $monthInput,
        ],
        'period_start' => $start->toDateString(),
        'period_end' => $end->toDateString(),
        'approvalStatus' => $approvalStatus,
    ]);
}


    public function generatePayroll(Request $request)
    {
        $request->validate([
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
            'employee_ids' => 'array',
            'employee_ids.*' => 'integer',
        ]);
$start = Carbon::parse($request->period_start)->startOfDay();
$end   = Carbon::parse($request->period_end)->endOfDay();


        $employees = EmployeeProfile::query()
            ->when($request->filled('employee_ids'), function ($q) use ($request) {
                $q->whereIn('employeeprofiles_id', $request->employee_ids);
            })
            ->get();

        DB::transaction(function () use ($employees, $start, $end) {
            foreach ($employees as $emp) {
                $computed = $this->computePayrollForEmployee($emp, $start, $end);

                $payroll = Payroll::updateOrCreate(
                    [
                        'employeeprofiles_id' => $emp->employeeprofiles_id,
                        'pay_period_start' => $start->toDateString(),
                        'pay_period_end' => $end->toDateString(),
                    ],
                    [
                        'total_days_of_work' => $computed['days_worked'],
                        'pay_period' => $this->formatPayPeriod($start, $end),
                        'salary_rate' => $computed['base'],
                        'overtime_pay' => $computed['ot_pay'],
                        'deductions' => $computed['deductions'],
                        'cash_advance' => $computed['cash_advance'],
                        'net_pay' => $computed['net'],
                        'status' => 'Pending',
                    ]
                );

                // optionally link deductions to payroll
                Deduction::where('employeeprofiles_id', $emp->employeeprofiles_id)
                    ->whereBetween('created_at', [$start->startOfDay(), $end->endOfDay()])
                    ->update(['payroll_id' => $payroll->payroll_id]);
            }
        });

        return back()->with('success', 'Payroll generated and saved as Pending');
    }

    public function approvePayroll(Request $request, Payroll $payroll)
    {
        $request->validate([
            'action' => 'required|in:approve,reject',
            'remarks' => 'nullable|string|max:255',
        ]);

        $payroll->status = $request->action === 'approve' ? 'Approved' : 'Rejected';
        $payroll->save();

        return back()->with('success', 'Payroll status updated to '.$payroll->status);
    }

    public function downloadPayslip(Payroll $payroll)
    {
        $emp = $payroll->employeeprofiles;
        if (!$emp) {
            $emp = \App\Models\Employeeprofiles::find($payroll->employeeprofiles_id);
        }
        if (!$emp) {
            abort(404, 'Employee profile not found for this payroll');
        }
        $data = [
            'employee' => $emp,
            'payroll' => $payroll,
        ];

        $pdf = Pdf::setOptions([
            'defaultFont' => 'DejaVu Sans',
            'isRemoteEnabled' => true,
        ])->loadView('finance.payroll.payslip_pdf', $data);
        $filename = 'payslip_'.$emp->last_name.'_'.$payroll->pay_period.'.pdf';
        // Save to DB as per schema
        Payslip::updateOrCreate(
            [
                'payroll_id' => $payroll->payroll_id,
                'employeeprofiles_id' => $emp->employeeprofiles_id,
            ],
            [
                'pdf_name' => $filename,
                'pdf_mime' => 'application/pdf',
                'pdf_file' => $pdf->output(),
                'generated_at' => now(),
            ]
        );

        return $pdf->download($filename);
    }

    public function exportTable(Request $request)
    {
        [$start, $end] = $this->resolvePeriod($request);

        // Mirror the dashboard filters on Payroll model
        $employeeFilter = $request->input('employee');
        $positionFilter = $request->input('position');

        $query = Payroll::with('employeeprofiles')
            ->whereDate('pay_period_start', '>=', $start->toDateString())
            ->whereDate('pay_period_end', '<=', $end->toDateString());

        if (!empty($employeeFilter)) {
            $query->whereHas('employeeprofiles', function ($q) use ($employeeFilter) {
                $q->where('first_name', 'like', '%'.$employeeFilter.'%')
                  ->orWhere('last_name', 'like', '%'.$employeeFilter.'%');
            });
        }
        if (!empty($positionFilter)) {
            $query->whereHas('employeeprofiles', function ($q) use ($positionFilter) {
                $q->where('position', 'like', '%'.$positionFilter.'%');
            });
        }

        $payrolls = $query->orderBy('pay_period_start')->get();

        $rows = $payrolls->map(function (Payroll $payroll) {
            $emp = $payroll->employeeprofiles;
            $ps = Carbon::parse($payroll->pay_period_start);
            $pe = Carbon::parse($payroll->pay_period_end);

            $otHours = OvertimeRequest::where('employeeprofiles_id', $emp->employeeprofiles_id)
                ->where('status', 'Approved')
                ->whereBetween('approved_date', [$ps->toDateString(), $pe->toDateString()])
                ->sum('hours');
            $otPay = OvertimeRequest::where('employeeprofiles_id', $emp->employeeprofiles_id)
                ->where('status', 'Approved')
                ->whereBetween('approved_date', [$ps->toDateString(), $pe->toDateString()])
                ->sum('amount');

            return [
                'employee' => $emp,
                'position' => $emp->position,
                'salary_rate' => $payroll->salary_rate ?? 0,
                'period' => $payroll->pay_period ?? ($ps->toDateString().' - '.$pe->toDateString()),
                'days_worked' => $payroll->total_days_of_work ?? 0,
                'ot_hours' => $otHours,
                'ot_pay' => round($otPay, 2),
                'deductions' => round($payroll->deductions ?? 0, 2),
                'cash_advance' => round($payroll->cash_advance ?? 0, 2),
                'net' => round($payroll->net_pay ?? 0, 2),
                'status' => $payroll->status ?? 'N/A',
            ];
        });

        $pdf = Pdf::setOptions([
            'defaultFont' => 'DejaVu Sans',
            'isRemoteEnabled' => true,
        ])->loadView('finance.payroll.table_pdf', [
            'rows' => $rows,
            'period' => $this->formatPayPeriod($start, $end),
        ]);
        return $pdf->download('payroll_table_'.$start->format('Ymd').'-'.$end->format('Ymd').'.pdf');
    }

    // public function approvals(Request $request)
    // {
    //     [$start, $end] = $this->resolvePeriod($request);
    //     $payrolls = Payroll::with('employeeProfile')
    //         ->whereBetween('pay_period_start', [$start->toDateString(), $end->toDateString()])
    //         ->whereIn('status', ['Pending','Rejected'])
    //         ->orderBy('status')
    //         ->orderByDesc('payroll_id')
    //         ->paginate(20);

    //     return view('finance.payroll.approvals', [
    //         'payrolls' => $payrolls,
    //         'period_start' => $start->toDateString(),
    //         'period_end' => $end->toDateString(),
    //     ]);
    // }

    // ===== Helpers =====
    protected function resolvePeriod(Request $request): array
    {
        if ($request->input('filter_type') === 'month' && $request->filled('month')) {
            $m = Carbon::createFromFormat('Y-m', $request->input('month'));
            return [$m->copy()->startOfMonth(), $m->copy()->endOfMonth()];
        }
        if ($request->filled(['period_start','period_end'])) {
            return [Carbon::parse($request->period_start), Carbon::parse($request->period_end)];
        }
       $today = Carbon::today();

if ($today->day <= 15) {
    $start = $today->copy()->startOfMonth();
    $end   = $today->copy()->setDay(15);
} else {
    $start = $today->copy()->setDay(16);
    $end   = $today->copy()->endOfMonth();
}

return [$start, $end];

    }

    protected function formatPayPeriod(Carbon $start, Carbon $end): string
    {
        return $start->format('Y-m-d').' to '.$end->format('Y-m-d');
    }

    protected function getDaysWorked(EmployeeProfile $emp, Carbon $start, Carbon $end): int
    {
        return Attendance::where('employeeprofiles_id', $emp->employeeprofiles_id)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->whereNotNull('time_out')
            ->distinct('date')
            ->count('date');
    }

   
    protected function getEffectiveDailyRate(EmployeeProfile $emp, Carbon $asOf): float
    {
        $custom = EmployeeSalaryRate::where('employeeprofiles_id', $emp->employeeprofiles_id)
            ->where('status', 'active')
            ->whereDate('effective_date', '<=', $asOf->toDateString())
            ->orderByDesc('effective_date')
            ->first();

        if ($custom) {
            if ($custom->custom_salary_rate) {
                return (float) $custom->custom_salary_rate;
            }
            if ($custom->salaryRate) {
                return (float) $custom->salaryRate->salary_rate;
            }
        }

        $default = SalaryRate::where('position', $emp->position)->where('status', 'active')->first();
        return (float) ($default->salary_rate ?? 0);
    }

    protected function computePayrollForEmployee(EmployeeProfile $emp, Carbon $start, Carbon $end): array
    {
        $daysWorked = $this->getDaysWorked($emp, $start, $end);
        $totalDaysInSemiMonth = $start->diffInDays($end) + 1;
        $rate = $this->getEffectiveDailyRate($emp, $start);
        $otHours = $this->getApprovedOtHours($emp, $start, $end);
        $otCap = min($otHours, 5 * $daysWorked);
        $otPay = ($rate / 8) * $otCap;
        $base = $rate * $daysWorked;
        $deductions = $this->getStatutoryDeductions($emp, $start, $end);
        $cashAdvanceTotal = $this->getApprovedCashAdvanceTotal($emp, $start, $end);
        $cashAdvanceApplied = $cashAdvanceTotal * ($daysWorked / max($totalDaysInSemiMonth, 1));
        $net = $base + $otPay - $deductions - $cashAdvanceApplied;

        return [
            'days_worked' => $daysWorked,
            'base' => round($base, 2),
            'ot_pay' => round($otPay, 2),
            'deductions' => round($deductions, 2),
            'cash_advance' => round($cashAdvanceApplied, 2),
            'net' => round($net, 2),    
        ];
    }

 public function sendApproval(Request $request)
{
    try {
        \App\Models\Expenses::create([
            'expense_name' => 'Payroll Total Approval',
            'category' => 'Payroll Total',
            'amount' => $request->grand_total,
            'description' => "Total Gross: ₱" . number_format($request->total_gross, 2) .
                             ", Total Net: ₱" . number_format($request->total_net, 2) .
                             ", Grand Total: ₱" . number_format($request->grand_total, 2),
            'admin_approval' => 'Pending',
            'expense_date' => now(),
        ]);

        return back()->with('success', 'Payroll total successfully sent for approval.');
    } catch (\Exception $e) {
        return back()->with('error', 'Failed to send approval: ' . $e->getMessage());
    }
}


}


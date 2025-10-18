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

class PayrollController extends Controller
{
    public function dashboard(Request $request)
    {
        [$start, $end] = $this->resolvePeriod($request);

        $query = EmployeeProfile::query();
        if ($request->filled('employee')) {
            $query->where(function($q) use ($request) {
                $q->where('first_name', 'like', '%'.$request->employee.'%')
                  ->orWhere('last_name', 'like', '%'.$request->employee.'%');
            });
        }
        if ($request->filled('position')) {
            $query->where('position', $request->position);
        }

        $employees = $query->orderBy('last_name')->get();

        $rows = $employees->map(function (EmployeeProfile $emp) use ($start, $end) {
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

            $existingPayroll = Payroll::where('employeeprofiles_id', $emp->employeeprofiles_id)
                ->whereDate('pay_period_start', $start)
                ->whereDate('pay_period_end', $end)
                ->first();

            return [
                'employee' => $emp,
                'position' => $emp->position,
                'period' => $this->formatPayPeriod($start, $end),
                'days_worked' => $daysWorked,
                'ot_hours' => $otHours,
                'ot_pay' => round($otPay, 2),
                'deductions' => round($deductions, 2),
                'cash_advance' => round($cashAdvanceApplied, 2),
                'net' => round($net, 2),
                'status' => $existingPayroll?->status ?? 'Not Generated',
                'payroll' => $existingPayroll,
            ];
        });

        return view('finance.payroll.index', [
            'rows' => $rows,
            'period_start' => $start->toDateString(),
            'period_end' => $end->toDateString(),
            'filters' => [
                'employee' => $request->employee,
                'position' => $request->position,
                'status' => $request->status,
            ],
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

        $start = Carbon::parse($request->input('period_start'));
        $end = Carbon::parse($request->input('period_end'));

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
                        'basic_salary' => $computed['base'],
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
        $emp = $payroll->employeeProfile;
        // Compute deduction breakdown within payroll period
        $startDate = $payroll->pay_period_start ?? $payroll->start_date ?? null;
        $endDate = $payroll->pay_period_end ?? $payroll->end_date ?? null;
        $deductions = ['income_tax' => 0, 'sss' => 0, 'philhealth' => 0, 'pagibig' => 0];
        if ($startDate && $endDate) {
            $sum = \DB::table('deductions')
                ->selectRaw('COALESCE(SUM(income_tax),0) as income_tax, COALESCE(SUM(sss),0) as sss, COALESCE(SUM(philhealth),0) as philhealth, COALESCE(SUM(pagibig),0) as pagibig')
                ->where('employeeprofiles_id', $emp->employeeprofiles_id)
                ->whereBetween('created_at', [\Carbon\Carbon::parse($startDate)->startOfDay(), \Carbon\Carbon::parse($endDate)->endOfDay()])
                ->first();
            if ($sum) {
                $deductions = [
                    'income_tax' => (float)$sum->income_tax,
                    'sss' => (float)$sum->sss,
                    'philhealth' => (float)$sum->philhealth,
                    'pagibig' => (float)$sum->pagibig,
                ];
            }
        }

        $data = [
            'employee' => $emp,
            'payroll' => $payroll,
            'deductions' => $deductions,
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

        $query = EmployeeProfile::query();
        if ($request->filled('employee')) {
            $query->where(function($q) use ($request) {
                $q->where('first_name', 'like', '%'.$request->employee.'%')
                  ->orWhere('last_name', 'like', '%'.$request->employee.'%');
            });
        }
        if ($request->filled('position')) {
            $query->where('position', $request->position);
        }
        $employees = $query->orderBy('last_name')->get();

        $rows = $employees->map(function (EmployeeProfile $emp) use ($start, $end) {
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

            $existingPayroll = Payroll::where('employeeprofiles_id', $emp->employeeprofiles_id)
                ->whereDate('pay_period_start', $start)
                ->whereDate('pay_period_end', $end)
                ->first();

            return [
                'employee' => $emp,
                'position' => $emp->position,
                'period' => $this->formatPayPeriod($start, $end),
                'days_worked' => $daysWorked,
                'ot_hours' => $otHours,
                'ot_pay' => round($otPay, 2),
                'deductions' => round($deductions, 2),
                'cash_advance' => round($cashAdvanceApplied, 2),
                'net' => round($net, 2),
                'status' => $existingPayroll?->status ?? 'Not Generated',
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

    public function approvals(Request $request)
    {
        [$start, $end] = $this->resolvePeriod($request);
        $payrolls = Payroll::with('employeeProfile')
            ->whereBetween('pay_period_start', [$start->toDateString(), $end->toDateString()])
            ->whereIn('status', ['Pending','Rejected'])
            ->orderBy('status')
            ->orderByDesc('payroll_id')
            ->paginate(20);

        return view('finance.payroll.approvals', [
            'payrolls' => $payrolls,
            'period_start' => $start->toDateString(),
            'period_end' => $end->toDateString(),
        ]);
    }

    // ===== Helpers =====
    protected function resolvePeriod(Request $request): array
    {
        if ($request->filled(['period_start','period_end'])) {
            return [Carbon::parse($request->period_start), Carbon::parse($request->period_end)];
        }
        $today = Carbon::today();
        if ($today->day <= 15) {
            $start = $today->copy()->startOfMonth();
            $end = $today->copy()->startOfMonth()->addDays(14);
        } else {
            $start = $today->copy()->startOfMonth()->addDays(15);
            $end = $today->copy()->endOfMonth();
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

    protected function getApprovedOtHours(EmployeeProfile $emp, Carbon $start, Carbon $end): int
    {
        return (int) (\App\Models\LeaveOvertimeRequest::where('employeeprofiles_id', $emp->employeeprofiles_id)
            ->where('status', 'approved')
            ->whereBetween('request_date', [$start->startOfDay(), $end->endOfDay()])
            ->sum('overtime_hours') ?? 0);
    }

    protected function getApprovedCashAdvanceTotal(EmployeeProfile $emp, Carbon $start, Carbon $end): float
    {
        return (float) (CashAdvance::where('employeeprofiles_id', $emp->employeeprofiles_id)
            ->where('status', 'approved')
            ->whereDate('approved_date', '<=', $end->toDateString())
            ->sum('amount') ?? 0);
    }

    protected function getStatutoryDeductions(EmployeeProfile $emp, Carbon $start, Carbon $end): float
    {
        $rows = Deduction::where('employeeprofiles_id', $emp->employeeprofiles_id)
            ->whereBetween('created_at', [$start->startOfDay(), $end->endOfDay()])
            ->get();
        $total = 0.0;
        foreach ($rows as $d) {
            $total += (float)($d->income_tax ?? 0) + (float)($d->sss ?? 0) + (float)($d->philhealth ?? 0) + (float)($d->pagibig ?? 0) + (float)($d->amount ?? 0);
        }
        return $total;
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
}

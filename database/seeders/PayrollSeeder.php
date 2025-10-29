<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EmployeeProfile;
use App\Models\SalaryRate;
use App\Models\EmployeeSalaryRate;
use App\Models\Attendance;
use App\Models\OvertimeRequest;
use App\Models\CashAdvance;
use App\Models\Deduction;
use App\Models\Payroll;
use Carbon\Carbon;

class PayrollSeeder extends Seeder
{
    public function run(): void
    {
        [$start, $end] = $this->resolveCurrentSemiMonth();
        $employees = EmployeeProfile::all();
        foreach ($employees as $emp) {
            $daysWorked = Attendance::where('employeeprofiles_id', $emp->employeeprofiles_id)
                ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
                ->whereNotNull('time_out')
                ->distinct('date')
                ->count('date');

            $totalDaysInSemiMonth = $start->diffInDays($end) + 1;
            $rate = $this->getEffectiveDailyRate($emp, $start);

            $otHours = (int) (OvertimeRequest::where('employeeprofiles_id', $emp->employeeprofiles_id)
                ->where('status', 'approved')
                ->whereBetween('approved_date', [$start->copy()->startOfDay(), $end->copy()->endOfDay()])
                ->sum('hours') ?? 0);
            $otCap = min($otHours, 5 * $daysWorked);
            $otPay = ($rate / 8) * $otCap;

            $base = $rate * $daysWorked;
            $deductions = Deduction::where('employeeprofiles_id', $emp->employeeprofiles_id)
                ->whereBetween('created_at', [$start->copy()->startOfDay(), $end->copy()->endOfDay()])
                ->get()
                ->sum(function ($d) {
                    return (float)($d->income_tax ?? 0) + (float)($d->sss ?? 0) + (float)($d->philhealth ?? 0) + (float)($d->pagibig ?? 0) + (float)($d->amount ?? 0);
                });

            $cashAdvanceTotal = (float) (CashAdvance::where('employeeprofiles_id', $emp->employeeprofiles_id)
                ->where('status', 'approved')
                ->whereDate('approved_date', '<=', $end->toDateString())
                ->sum('amount') ?? 0);
            $cashAdvanceApplied = $cashAdvanceTotal * ($daysWorked / max($totalDaysInSemiMonth, 1));

            $net = $base + $otPay - $deductions - $cashAdvanceApplied;

            Payroll::updateOrCreate(
                [
                    'employeeprofiles_id' => $emp->employeeprofiles_id,
                    'pay_period_start' => $start->toDateString(),
                    'pay_period_end' => $end->toDateString(),
                ],
                [
                    'total_days_of_work' => $daysWorked,
                    'pay_period' => $start->format('Y-m-d').' to '.$end->format('Y-m-d'),
                    'basic_salary' => round($base,2),
                    'overtime_pay' => round($otPay,2),
                    'deductions' => round($deductions,2),
                    'cash_advance' => round($cashAdvanceApplied,2),
                    'net_pay' => round($net,2),
                    'status' => 'Pending',
                ]
            );
        }
    }

    protected function resolveCurrentSemiMonth(): array
    {
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
}

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: 'DejaVu Sans'; font-size: 12px; color: #111; }
        .header { text-align: center; margin-bottom: 14px; }
        .subtitle { color: #6b7280; font-size: 11px; margin-top: 4px; }
        .card { border: 1px solid #e5e7eb; border-radius: 8px; padding: 12px; margin-bottom: 12px; }
        .title { font-weight: 700; font-size: 13px; margin-bottom: 6px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { text-align: left; padding: 8px; border-bottom: 1px solid #eee; }
        th { background: #f8fafc; }
        .kvs td { border-bottom: none; padding: 4px 8px; }
        .right { text-align: right; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Payslip</h2>
        <div class="subtitle">{{ $payroll->pay_period }}</div>
    </div>

    <div class="card">
        <div class="title">Employee</div>
        <table class="kvs">
            <tr>
                <td><strong>Name</strong></td>
                <td>{{ $employee->last_name }}, {{ $employee->first_name }}</td>
                <td class="right"><strong>Position</strong></td>
                <td class="right">{{ $employee->position }}</td>
            </tr>
        </table>
    </div>

    <div class="card">
        <div class="title">Summary</div>
        <table>
            <tr>
                <th>Basic Salary</th>
                <td class="right">PHP {{ number_format($payroll->basic_salary, 2) }}</td>
            </tr>
            <tr>
                <th>Overtime Pay</th>
                <td class="right">PHP {{ number_format($payroll->overtime_pay ?? 0, 2) }}</td>
            </tr>
        </table>
    </div>

    <div class="card">
        <div class="title">Deductions</div>
        <table>
            <tr>
                <th>Income Tax</th>
                <td class="right">PHP {{ number_format(($deductions['income_tax'] ?? 0), 2) }}</td>
            </tr>
            <tr>
                <th>SSS</th>
                <td class="right">PHP {{ number_format(($deductions['sss'] ?? 0), 2) }}</td>
            </tr>
            <tr>
                <th>PhilHealth</th>
                <td class="right">PHP {{ number_format(($deductions['philhealth'] ?? 0), 2) }}</td>
            </tr>
            <tr>
                <th>Pag-IBIG</th>
                <td class="right">PHP {{ number_format(($deductions['pagibig'] ?? 0), 2) }}</td>
            </tr>
            <tr>
                <th style="border-top:1px solid #e5e7eb">Total Deductions</th>
                <td class="right" style="border-top:1px solid #e5e7eb">PHP {{ number_format(array_sum($deductions ?? []), 2) }}</td>
            </tr>
            <tr>
                <th>Cash Advance</th>
                <td class="right">PHP {{ number_format($payroll->cash_advance ?? 0, 2) }}</td>
            </tr>
            <tr>
                <th>Net Pay</th>
                <td class="right"><strong>PHP {{ number_format($payroll->net_pay ?? 0, 2) }}</strong></td>
            </tr>
        </table>
    </div>

    <div style="margin-top: 16px; font-size: 11px; color: #6b7280;">
        Generated on {{ now()->format('Y-m-d H:i') }}
    </div>
</body>
</html>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: 'DejaVu Sans'; font-size: 12px; color: #111; }
        h2 { text-align: center; margin-bottom: 10px; }
        .subtitle { text-align: center; color: #6b7280; font-size: 11px; margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { text-align: left; padding: 8px; border-bottom: 1px solid #eee; }
        th { background: #f8fafc; font-weight: 700; }
        .right { text-align: right; }
    </style>
    </head>
<body>
    <h2>Payroll Summary</h2>
    <div class="subtitle">{{ $period }}</div>
    <table>
        <thead>
            <tr>
                <th>Employee</th>
                <th>Position</th>
                <th>Days</th>
                <th>OT (hrs)</th>
                <th class="right">OT Pay</th>
                <th class="right">Deductions</th>
                <th class="right">Cash Adv</th>
                <th class="right">Net Pay</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $r)
            <tr>
                <td>{{ $r['employee']->last_name }}, {{ $r['employee']->first_name }}</td>
                <td>{{ $r['position'] }}</td>
                <td>{{ $r['days_worked'] }}</td>
                <td>{{ $r['ot_hours'] }}</td>
                <td class="right">PHP {{ number_format($r['ot_pay'],2) }}</td>
                <td class="right">PHP {{ number_format($r['deductions'],2) }}</td>
                <td class="right">PHP {{ number_format($r['cash_advance'],2) }}</td>
                <td class="right"><strong>PHP {{ number_format($r['net'],2) }}</strong></td>
                <td>{{ $r['status'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>

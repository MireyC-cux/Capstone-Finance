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
    <h2>Salary Disbursements</h2>
    <div class="subtitle">{{ $period }}</div>
    <table>
        <thead>
            <tr>
                <th>Payment Date</th>
                <th>Employee</th>
                <th>Method</th>
                <th>Reference</th>
                <th class="right">Amount</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $d)
            <tr>
                <td>{{ $d->payment_date }}</td>
                <td>{{ $d->employeeProfile->last_name }}, {{ $d->employeeProfile->first_name }}</td>
                <td>{{ $d->payment_method }}</td>
                <td>{{ $d->reference_number }}</td>
                <td class="right">PHP {{ number_format($d->payroll->net_pay ?? 0, 2) }}</td>
                <td>{{ $d->status }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>

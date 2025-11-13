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
            @forelse($rows as $d)
            <tr>
                <td>{{ optional($d->payment_date)->format('Y-m-d') ?? '-' }}</td>
                <td>
                    {{ optional($d->employeeProfile)->last_name ?? 'N/A' }},
                    {{ optional($d->employeeProfile)->first_name ?? '' }}
                </td>
                <td>{{ $d->payment_method ?? '-' }}</td>
                <td>{{ $d->reference_number ?? '-' }}</td>
                <td class="right">PHP {{ number_format($d->payroll->net_pay ?? 0, 2) }}</td>
                <td>{{ $d->status ?? 'N/A' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center">No disbursements found for this period.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>

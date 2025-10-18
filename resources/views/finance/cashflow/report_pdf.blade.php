<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Cash Flow Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        h1 { font-size: 18px; margin-bottom: 6px; }
        .meta { color: #555; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 6px; }
        th { background: #f3f4f6; text-align: left; }
        tfoot td { font-weight: bold; }
    </style>
</head>
<body>
    <h1>Cash Flow Report</h1>
    <div class="meta">Period: {{ $start }} to {{ $end }}</div>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Type</th>
                <th>Source</th>
                <th style="text-align:right">Amount</th>
                <th>Description</th>
            </tr>
        </thead>
        <tbody>
            @foreach($flows as $f)
                <tr>
                    <td>{{ \Illuminate\Support\Carbon::parse($f->transaction_date)->format('Y-m-d') }}</td>
                    <td>{{ $f->transaction_type }}</td>
                    <td>{{ $f->source_type }}</td>
                    <td style="text-align:right">₱ {{ number_format((float)$f->amount, 2) }}</td>
                    <td>{{ $f->description }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3">Total Inflows</td>
                <td style="text-align:right">₱ {{ number_format($totals['inflows'], 2) }}</td>
                <td></td>
            </tr>
            <tr>
                <td colspan="3">Total Outflows</td>
                <td style="text-align:right">₱ {{ number_format($totals['outflows'], 2) }}</td>
                <td></td>
            </tr>
            <tr>
                <td colspan="3">Profit</td>
                <td style="text-align:right">₱ {{ number_format($totals['profit'], 2) }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>
</body>
</html>

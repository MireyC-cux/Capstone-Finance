@extends('layouts.finance_app')

@section('title', 'Cash Flow Dashboard')

@section('content')
<div class="max-w-7xl mx-auto">
    <h1 class="mb-6">Cash Flow & Expense Tracking</h1>

    @if(session('success'))
        <div class="mb-4 p-3 rounded bg-green-100 text-green-800">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 p-3 rounded bg-red-100 text-red-800">{{ session('error') }}</div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
        <div class="p-4 rounded-lg bg-white shadow">
            <div class="text-sm text-gray-500">Total Inflows</div>
            <div class="text-2xl font-bold text-emerald-600">₱ {{ number_format($totalInflows, 2) }}</div>
        </div>
        <div class="p-4 rounded-lg bg-white shadow">
            <div class="text-sm text-gray-500">Total Outflows</div>
            <div class="text-2xl font-bold text-rose-600">₱ {{ number_format($totalOutflows, 2) }}</div>
        </div>
        <div class="p-4 rounded-lg bg-white shadow">
            <div class="text-sm text-gray-500">Current Balance</div>
            <div class="text-2xl font-bold text-sky-600">₱ {{ number_format($currentBalance, 2) }}</div>
        </div>
        <div class="p-4 rounded-lg bg-white shadow">
            <div class="text-sm text-gray-500">Profit</div>
            <div class="text-2xl font-bold {{ $profit >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">₱ {{ number_format($profit, 2) }}</div>
        </div>
        <div class="p-4 rounded-lg bg-white shadow">
            <div class="text-sm text-gray-500">Capital</div>
            <div class="text-2xl font-bold text-amber-600">₱ {{ number_format((float)($bf->capital ?? 0), 2) }}</div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 p-4 bg-white rounded-lg shadow">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-semibold">Monthly Income vs Expenses</h2>
                <div class="space-x-2">
                    <a class="px-3 py-1 text-sm rounded bg-slate-100 hover:bg-slate-200" href="{{ route('finance.cashflow.export.csv') }}">Export CSV</a>
                    <a class="px-3 py-1 text-sm rounded bg-slate-100 hover:bg-slate-200" href="{{ route('finance.cashflow.export.pdf') }}">Export PDF</a>
                    <a href="{{ route('finance.expenses') }}" class="px-3 py-1 text-sm rounded bg-amber-600 text-white hover:bg-amber-700">Expenses</a>
                </div>
            </div>
            <div class="w-full max-w-5xl mx-auto">
                <canvas id="cashChart" class="w-full h-64"></canvas>
            </div>
        </div>

        <div class="p-4 bg-white rounded-lg shadow">
            <h2 class="font-semibold mb-3">Capital Management</h2>
            <form class="mb-4" method="post" action="{{ route('finance.capital.set') }}">
                @csrf
                <div class="text-sm text-gray-500 mb-1">Set Capital</div>
                <div class="flex gap-2">
                    <input type="number" step="0.01" name="amount" class="border rounded px-2 py-1 w-full" placeholder="Amount" required>
                    <input type="text" name="remarks" class="border rounded px-2 py-1 w-full" placeholder="Remarks (optional)">
                    <button class="px-3 py-1 bg-amber-600 text-white rounded">Set</button>
                </div>
            </form>

            <form class="mb-4" method="post" action="{{ route('finance.capital.inject') }}">
                @csrf
                <div class="text-sm text-gray-500 mb-1">Capital Injection</div>
                <div class="grid grid-cols-2 gap-2 mb-2">
                    <input type="number" step="0.01" name="amount" class="border rounded px-2 py-1" placeholder="Amount" required>
                    <input type="date" name="date" class="border rounded px-2 py-1" value="{{ now()->toDateString() }}" required>
                    <input type="text" name="account_id" class="border rounded px-2 py-1" placeholder="Account ID (optional)">
                    <input type="text" name="remarks" class="border rounded px-2 py-1" placeholder="Remarks (optional)">
                </div>
                <button class="px-3 py-1 bg-emerald-600 text-white rounded">Record Injection</button>
            </form>

            <form method="post" action="{{ route('finance.capital.withdraw') }}">
                @csrf
                <div class="text-sm text-gray-500 mb-1">Capital Withdrawal</div>
                <div class="grid grid-cols-2 gap-2 mb-2">
                    <input type="number" step="0.01" name="amount" class="border rounded px-2 py-1" placeholder="Amount" required>
                    <input type="date" name="date" class="border rounded px-2 py-1" value="{{ now()->toDateString() }}" required>
                    <input type="text" name="account_id" class="border rounded px-2 py-1" placeholder="Account ID (optional)">
                    <input type="text" name="remarks" class="border rounded px-2 py-1" placeholder="Remarks (optional)">
                </div>
                <button class="px-3 py-1 bg-rose-600 text-white rounded">Record Withdrawal</button>
            </form>
        </div>
    </div>

    <div class="mt-6 p-4 bg-white rounded-lg shadow">
        <h2 class="font-semibold mb-3">Recent Cash Flow</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="text-left border-b">
                        <th class="py-2 pr-4">Date</th>
                        <th class="py-2 pr-4">Type</th>
                        <th class="py-2 pr-4">Source</th>
                        <th class="py-2 pr-4 text-right">Amount</th>
                        <th class="py-2 pr-4">Description</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($recent as $row)
                    <tr class="border-b last:border-0">
                        <td class="py-2 pr-4">{{ \Illuminate\Support\Carbon::parse($row->transaction_date)->format('Y-m-d') }}</td>
                        <td class="py-2 pr-4">
                            <span class="px-2 py-0.5 rounded text-white text-xs {{ $row->transaction_type === 'Inflow' ? 'bg-emerald-600' : 'bg-rose-600' }}">{{ $row->transaction_type }}</span>
                        </td>
                        <td class="py-2 pr-4">{{ $row->source_type }}</td>
                        <td class="py-2 pr-4 text-right">₱ {{ number_format((float)$row->amount, 2) }}</td>
                        <td class="py-2 pr-4">{{ $row->description }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6 grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="p-4 bg-white rounded-lg shadow">
            <h2 class="font-semibold mb-3">Expense Breakdown (YTD)</h2>
            <div class="w-full max-w-xl mx-auto">
                <canvas id="categoryChart" class="w-full h-64"></canvas>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('cashChart').getContext('2d');
    const series = @json($series);
    const profitSeries = @json($profitSeries);
    const labels = series.map(r => r.ym);
    const inflows = series.map(r => parseFloat(r.inflow ?? 0));
    const outflows = series.map(r => parseFloat(r.outflow ?? 0));
    const profits = profitSeries.map(r => parseFloat(r.profit ?? 0));

    new Chart(ctx, {
        type: 'line',
        data: {
            labels,
            datasets: [
                { label: 'Inflows', data: inflows, borderColor: '#10b981', backgroundColor: 'rgba(16,185,129,0.15)', fill: true, tension: .3 },
                { label: 'Outflows', data: outflows, borderColor: '#ef4444', backgroundColor: 'rgba(239,68,68,0.15)', fill: true, tension: .3 },
                { label: 'Profit', data: profits, borderColor: '#0ea5e9', backgroundColor: 'rgba(14,165,233,0.15)', fill: true, tension: .3 },
            ]
        },
        options: { responsive: true, maintainAspectRatio: false }
    });

    const cctx = document.getElementById('categoryChart').getContext('2d');
    const catData = @json($categoryBreakdown);
    const catLabels = Object.keys(catData);
    const catValues = Object.values(catData).map(v => parseFloat(v ?? 0));
    new Chart(cctx, {
        type: 'doughnut',
        data: {
            labels: catLabels,
            datasets: [{
                data: catValues,
                backgroundColor: ['#60a5fa','#34d399','#fbbf24','#f472b6','#94a3b8'],
            }]
        },
        options: { responsive: false, maintainAspectRatio: true }
    });
</script>
@endpush
@endsection


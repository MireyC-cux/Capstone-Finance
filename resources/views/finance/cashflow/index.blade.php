@extends('layouts.finance_app')

@section('title', 'Cash Flow Dashboard')

@section('content')
<div class="container-fluid px-3 px-md-4 py-4">


    @if(session('success'))
        <div class="alert alert-success" style="margin-bottom: 1rem;">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger" style="margin-bottom: 1rem;">{{ session('error') }}</div>
    @endif

    <!-- Metrics Cards -->
    <div class="row g-4 mb-4">
        <div class="col-12 col-md-6 col-xl">
            <div class="card h-100 shadow-sm border-0 p-3" style="min-height: 96px;">
                <div class="d-flex justify-content-between align-items-center gap-3">
                    <div style="flex: 1;">
                        <div style="font-size: 0.85rem; color: var(--text-secondary); font-weight: 600; text-transform: uppercase;">Total Inflows</div>
                        <div style="font-size: 24px; font-weight: 700; color: #10B981; margin-top: 0.25rem;">₱ {{ number_format($totalInflows, 2) }}</div>
                    </div>
                    <div style="width: 48px; height: 48px; border-radius: 8px; background: #10B981; color: white; display: flex; align-items: center; justify-content: center;">
                        <i class="fa fa-arrow-trend-up" style="font-size: 20px;"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl">
            <div class="card h-100 shadow-sm border-0 p-3" style="min-height: 96px;">
                <div class="d-flex justify-content-between align-items-center gap-3">
                    <div style="flex: 1;">
                        <div style="font-size: 0.85rem; color: var(--text-secondary); font-weight: 600; text-transform: uppercase;">Total Outflows</div>
                        <div style="font-size: 24px; font-weight: 700; color: #EF4444; margin-top: 0.25rem;">₱ {{ number_format($totalOutflows, 2) }}</div>
                    </div>
                    <div style="width: 48px; height: 48px; border-radius: 8px; background: #EF4444; color: white; display: flex; align-items: center; justify-content: center;">
                        <i class="fa fa-arrow-trend-down" style="font-size: 20px;"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl">
            <div class="card h-100 shadow-sm border-0 p-3" style="min-height: 96px;">
                <div class="d-flex justify-content-between align-items-center gap-3">
                    <div style="flex: 1;">
                        <div style="font-size: 0.85rem; color: var(--text-secondary); font-weight: 600; text-transform: uppercase;">Current Balance</div>
                        <div style="font-size: 24px; font-weight: 700; color: #2563EB; margin-top: 0.25rem;">₱ {{ number_format($currentBalance, 2) }}</div>
                    </div>
                    <div style="width: 48px; height: 48px; border-radius: 8px; background: #2563EB; color: white; display: flex; align-items: center; justify-content: center;">
                        <i class="fa fa-wallet" style="font-size: 20px;"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl">
            <div class="card h-100 shadow-sm border-0 p-3" style="min-height: 96px;">
                <div class="d-flex justify-content-between align-items-center gap-3">
                    <div style="flex: 1;">
                        <div style="font-size: 0.85rem; color: var(--text-secondary); font-weight: 600; text-transform: uppercase;">Profit</div>
                        <div style="font-size: 24px; font-weight: 700; color: {{ $profit >= 0 ? '#10B981' : '#EF4444' }}; margin-top: 0.25rem;">₱ {{ number_format($profit, 2) }}</div>
                    </div>
                    <div style="width: 48px; height: 48px; border-radius: 8px; background: {{ $profit >= 0 ? '#10B981' : '#EF4444' }}; color: white; display: flex; align-items: center; justify-content: center;">
                        <i class="fa fa-chart-line" style="font-size: 20px;"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 col-xl">
            <div class="card h-100 shadow-sm border-0 p-3" style="min-height: 96px;">
                <div class="d-flex justify-content-between align-items-center gap-3">
                    <div style="flex: 1;">
                        <div style="font-size: 0.85rem; color: var(--text-secondary); font-weight: 600; text-transform: uppercase;">Capital</div>
                        <div style="font-size: 24px; font-weight: 700; color: #F59E0B; margin-top: 0.25rem;">₱ {{ number_format((float)($bf->capital ?? 0), 2) }}</div>
                    </div>
                    <div style="width: 48px; height: 48px; border-radius: 8px; background: #F59E0B; color: white; display: flex; align-items: center; justify-content: center;">
                        <i class="fa fa-coins" style="font-size: 20px;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="row g-4 mb-4">
        <div class="col-12 col-lg-6">
            <div class="card shadow-sm border-0 p-4">
                <div class="d-flex justify-content-between align-items-center mb-3 mb-md-4">
                    <h2 style="font-size: 20px; font-weight: 600; margin: 0;">Monthly Inflows vs Outflows</h2>
                    <div class="d-flex gap-2">
                        <a class="btn btn-sm" style="border: 1px solid var(--border-card); background: white; font-size: 13px;" href="{{ route('finance.cashflow.export.csv') }}">CSV</a>
                        <a class="btn btn-sm" style="border: 1px solid var(--border-card); background: white; font-size: 13px;" href="{{ route('finance.cashflow.export.pdf') }}">PDF</a>
                        <a href="{{ route('finance.expenses') }}" class="btn btn-sm btn-warning">Expenses</a>
                    </div>
                </div>
                <div class="position-relative" style="height: 360px;">
                    <canvas id="cashChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-6">
            <div class="card shadow-sm border-0 p-4">
                <h2 style="font-size: 20px; font-weight: 600; margin-bottom: 1rem;">Expense Breakdown (YTD)</h2>
                <div class="position-relative" style="height: 360px; display: flex; align-items: center; justify-content: center;">
                    <canvas id="categoryChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Cash Flow Table -->
    <div class="card shadow-sm border-0 overflow-hidden p-0 mt-4 rounded-3">
        <div class="px-4 py-3" style="border-bottom: 1px solid var(--border-card);">
            <h2 style="font-size: 20px; font-weight: 600; margin: 0;">Recent Cash Flow</h2>
        </div>
        <div class="table-responsive" style="max-height: 420px; overflow: auto;">
            <table class="table table-hover table-striped align-middle mb-0">
                <thead class="table-white" style="position: sticky; top: 0; z-index: 1; background: #fff;">
                    <tr>
                        <th style="padding: 8px;">Date</th>
                        <th style="padding: 8px;">Type</th>
                        <th style="padding: 8px;">Source</th>
                        <th class="text-end" style="padding: 8px;">Amount</th>
                        <th style="padding: 8px;">Description</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($recent as $row)
                    <tr>
                        <td style="padding: 8px;">{{ \Illuminate\Support\Carbon::parse($row->transaction_date)->format('Y-m-d') }}</td>
                        <td style="padding: 8px;">
                            @if($row->transaction_type === 'Inflow')
                                <span class="badge" style="background: #D1FAE5; color: #065F46; padding: 0.25rem 0.5rem; border-radius: 6px; font-weight: 600;">{{ $row->transaction_type }}</span>
                            @else
                                <span class="badge" style="background: #FEE2E2; color: #991B1B; padding: 0.25rem 0.5rem; border-radius: 6px; font-weight: 600;">{{ $row->transaction_type }}</span>
                            @endif
                        </td>
                        <td style="padding: 8px;">{{ $row->source_type }}</td>
                        <td class="text-end" style="padding: 8px; font-weight: 600;">₱ {{ number_format((float)$row->amount, 2) }}</td>
                        <td style="padding: 8px;">{{ $row->description }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const fmtCurrency = (n) => "₱ " + Number(n || 0).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    const ctx = document.getElementById('cashChart').getContext('2d');
    const series = @json($series);
    const labels = series.map(r => r.ym);
    const inflows = series.map(r => parseFloat(r.inflow ?? 0));
    const outflows = series.map(r => parseFloat(r.outflow ?? 0));
    const profitSeries = @json($profitSeries);
    const profits = profitSeries.map(r => parseFloat(r.profit ?? 0));

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels,
            datasets: [
                {
                    label: 'Inflows',
                    data: inflows,
                    backgroundColor: 'rgba(16,185,129,0.6)',
                    borderColor: '#10b981',
                    borderWidth: 1,
                    borderRadius: 6
                },
                {
                    label: 'Outflows',
                    data: outflows,
                    backgroundColor: 'rgba(239,68,68,0.6)',
                    borderColor: '#ef4444',
                    borderWidth: 1,
                    borderRadius: 6
                },
                {
                    label: 'Profit',
                    data: profits,
                    backgroundColor: 'rgba(14,165,233,0.6)',
                    borderColor: '#0ea5e9',
                    borderWidth: 1,
                    borderRadius: 6
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { intersect: false, mode: 'index' },
            layout: { padding: 8 },
            plugins: {
                legend: { position: 'bottom', labels: { padding: 16 } },
                tooltip: {
                    callbacks: {
                        label: (ctx) => `${ctx.dataset.label}: ${fmtCurrency(ctx.parsed.y)}`
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { callback: (value) => fmtCurrency(value) },
                    grid: { color: 'rgba(0,0,0,0.06)' }
                },
                x: { grid: { display: false } }
            }
        }
    });

    const cctx = document.getElementById('categoryChart').getContext('2d');
    const catData = @json($categoryBreakdown);
    const catLabels = Object.keys(catData);
    const catValues = Object.values(catData).map(v => parseFloat(v ?? 0));
    const palette = ['#60a5fa','#34d399','#fbbf24','#f472b6','#94a3b8','#f87171','#22d3ee','#a78bfa','#fb7185','#4ade80','#c084fc','#f59e0b'];
    new Chart(cctx, {
        type: 'pie',
        data: {
            labels: catLabels,
            datasets: [{
                data: catValues,
                backgroundColor: catLabels.map((_, i) => palette[i % palette.length]),
                borderColor: '#ffffff',
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { padding: 16 } },
                tooltip: {
                    callbacks: {
                        label: (ctx) => {
                            const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                            const val = ctx.parsed;
                            const pct = total ? ((val / total) * 100).toFixed(1) : 0;
                            return `${ctx.label}: ${fmtCurrency(val)} (${pct}%)`;
                        }
                    }
                }
            },
            animation: { animateScale: true }
        }
    });
</script>
@endpush
@endsection


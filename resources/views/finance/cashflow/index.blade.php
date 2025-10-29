@extends('layouts.finance_app')

@section('title', 'Cash Flow Dashboard')

@section('content')
<div style="padding: 20px;">


    @if(session('success'))
        <div class="alert alert-success" style="margin-bottom: 1rem;">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger" style="margin-bottom: 1rem;">{{ session('error') }}</div>
    @endif

    <!-- Metrics Cards -->
    <div class="row g-4" style="margin-bottom: 2rem;">
        <div class="col-12 col-md-6 col-xl">
            <div class="card" style="min-height: 96px; padding: 1rem 1.25rem;">
                <div class="d-flex justify-content-between align-items-center">
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
            <div class="card" style="min-height: 96px; padding: 1rem 1.25rem;">
                <div class="d-flex justify-content-between align-items-center">
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
            <div class="card" style="min-height: 96px; padding: 1rem 1.25rem;">
                <div class="d-flex justify-content-between align-items-center">
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
            <div class="card" style="min-height: 96px; padding: 1rem 1.25rem;">
                <div class="d-flex justify-content-between align-items-center">
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
            <div class="card" style="min-height: 96px; padding: 1rem 1.25rem;">
                <div class="d-flex justify-content-between align-items-center">
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
    <div class="row g-4">
        <div class="col-12 col-lg-8">
            <div class="card" style="padding: 1.25rem;">
                <div class="d-flex justify-content-between align-items-center" style="margin-bottom: 1.5rem;">
                    <h2 style="font-size: 20px; font-weight: 600; margin: 0;">Monthly Income vs Expenses</h2>
                    <div class="d-flex gap-2">
                        <a class="btn btn-sm" style="border: 1px solid var(--border-card); background: white; font-size: 13px;" href="{{ route('finance.cashflow.export.csv') }}">CSV</a>
                        <a class="btn btn-sm" style="border: 1px solid var(--border-card); background: white; font-size: 13px;" href="{{ route('finance.cashflow.export.pdf') }}">PDF</a>
                        <a href="{{ route('finance.expenses') }}" class="btn btn-sm btn-warning">Expenses</a>
                    </div>
                </div>
                <div style="height: 300px;">
                    <canvas id="cashChart" height="300"></canvas>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-4">
            <div class="card" style="padding: 1.25rem;">
                <h2 style="font-size: 20px; font-weight: 600; margin-bottom: 1rem;">Capital Management</h2>
                
                <form style="margin-bottom: 1.5rem;" method="post" action="{{ route('finance.capital.set') }}">
                    @csrf
                    <label class="form-label" style="font-weight: 600;">Set Capital</label>
                    <div class="mb-2">
                        <input type="number" step="0.01" name="amount" class="form-control" placeholder="Amount" required>
                    </div>
                    <div class="mb-2">
                        <input type="text" name="remarks" class="form-control" placeholder="Remarks (optional)">
                    </div>
                    <button class="btn btn-warning w-100">Set Capital</button>
                </form>

                <form style="margin-bottom: 1.5rem;" method="post" action="{{ route('finance.capital.inject') }}">
                    @csrf
                    <label class="form-label" style="font-weight: 600;">Capital Injection</label>
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <input type="number" step="0.01" name="amount" class="form-control" placeholder="Amount" required>
                        </div>
                        <div class="col-6">
                            <input type="date" name="date" class="form-control" value="{{ now()->toDateString() }}" required>
                        </div>
                        <div class="col-6">
                            <input type="text" name="account_id" class="form-control" placeholder="Account ID">
                        </div>
                        <div class="col-6">
                            <input type="text" name="remarks" class="form-control" placeholder="Remarks">
                        </div>
                    </div>
                    <button class="btn btn-success w-100">Record Injection</button>
                </form>

                <form method="post" action="{{ route('finance.capital.withdraw') }}">
                    @csrf
                    <label class="form-label" style="font-weight: 600;">Capital Withdrawal</label>
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <input type="number" step="0.01" name="amount" class="form-control" placeholder="Amount" required>
                        </div>
                        <div class="col-6">
                            <input type="date" name="date" class="form-control" value="{{ now()->toDateString() }}" required>
                        </div>
                        <div class="col-6">
                            <input type="text" name="account_id" class="form-control" placeholder="Account ID">
                        </div>
                        <div class="col-6">
                            <input type="text" name="remarks" class="form-control" placeholder="Remarks">
                        </div>
                    </div>
                    <button class="btn btn-danger w-100">Record Withdrawal</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Recent Cash Flow Table -->
    <div class="card" style="padding: 0; overflow: hidden; margin-top: 2rem;">
        <div style="padding: 1.25rem; border-bottom: 1px solid var(--border-card);">
            <h2 style="font-size: 20px; font-weight: 600; margin: 0;">Recent Cash Flow</h2>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle" style="margin-bottom: 0;">
                <thead class="table-dark">
                    <tr>
                        <th style="padding: 8px;">Date</th>
                        <th style="padding: 8px;">Type</th>
                        <th style="padding: 8px;">Source</th>
                        <th style="padding: 8px; text-align: right;">Amount</th>
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
                        <td style="padding: 8px; text-align: right; font-weight: 600;">₱ {{ number_format((float)$row->amount, 2) }}</td>
                        <td style="padding: 8px;">{{ $row->description }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Expense Breakdown -->
    <div class="row g-4" style="margin-top: 2rem;">
        <div class="col-12 col-lg-6">
            <div class="card" style="padding: 1.25rem;">
                <h2 style="font-size: 20px; font-weight: 600; margin-bottom: 1rem;">Expense Breakdown (YTD)</h2>
                <div style="height: 300px; display: flex; align-items: center; justify-content: center;">
                    <canvas id="categoryChart"></canvas>
                </div>
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


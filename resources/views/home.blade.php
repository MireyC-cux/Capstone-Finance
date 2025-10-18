@extends('layouts.finance_app')

@section('title', 'Finance Dashboard - Home')

@push('styles')
    <link href="{{ asset('css/finance_dashboard.css') }}" rel="stylesheet">
@endpush

@section('content')
@if(!empty($reportsMode))
    <div class="max-w-7xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1>Finance Reporting</h1>
                <p class="text-gray-600">Generate insights from Revenue, Expenses, AR, AP, Cash Flow, Payroll, and P&L.</p>
            </div>
            <form method="get" action="{{ route('finance.reports') }}" class="flex items-end gap-2">
                <div>
                    <label class="block text-sm text-gray-600">Start</label>
                    <input type="date" name="start" value="{{ $start ?? now()->subMonths(11)->startOfMonth()->toDateString() }}" class="border rounded px-2 py-1">
                </div>
                <div>
                    <label class="block text-sm text-gray-600">End</label>
                    <input type="date" name="end" value="{{ $end ?? now()->toDateString() }}" class="border rounded px-2 py-1">
                </div>
                <button class="px-3 py-2 bg-amber-600 text-white rounded">Apply</button>
            </form>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
            <div class="p-4 bg-white rounded-lg shadow">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="font-semibold">Revenue Report</h2>
                    <div class="space-x-2">
                        <a class="text-sm px-2 py-1 rounded bg-slate-100 hover:bg-slate-200" href="{{ route('finance.reports', ['export'=>'csv','type'=>'revenue','start'=>$start,'end'=>$end]) }}">Export CSV</a>
                        <a class="text-sm px-2 py-1 rounded bg-slate-100 hover:bg-slate-200" href="{{ route('finance.reports', ['export'=>'pdf','type'=>'revenue','start'=>$start,'end'=>$end]) }}">Export PDF</a>
                    </div>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-3">
                    <div><div class="text-xs text-gray-500">Sales</div><div class="font-semibold">₱ {{ number_format((float)($revenue['totals']['sales_total'] ?? 0),2) }}</div></div>
                    <div><div class="text-xs text-gray-500">Paid</div><div class="font-semibold text-emerald-600">₱ {{ number_format((float)($revenue['totals']['paid_total'] ?? 0),2) }}</div></div>
                    <div><div class="text-xs text-gray-500">Outstanding</div><div class="font-semibold text-sky-600">₱ {{ number_format((float)($revenue['totals']['outstanding_total'] ?? 0),2) }}</div></div>
                    <div><div class="text-xs text-gray-500">Overdue</div><div class="font-semibold text-rose-600">₱ {{ number_format((float)($revenue['totals']['overdue_total'] ?? 0),2) }}</div></div>
                </div>
                <div class="w-full max-w-3xl mx-auto">
                    <canvas id="revenueReportChart" class="w-full h-64"></canvas>
                </div>
                <div class="mt-3 overflow-x-auto"><table class="min-w-full text-sm"><thead><tr class="text-left border-b"><th class="py-2 pr-4">Month</th><th class="py-2 pr-4 text-right">Sales</th><th class="py-2 pr-4 text-right">Paid</th><th class="py-2 pr-4 text-right">Unpaid</th><th class="py-2 pr-4 text-right">Overdue</th></tr></thead><tbody>@foreach(($revenue['table'] ?? []) as $r)<tr class="border-b last:border-0"><td class="py-2 pr-4">{{ $r['month'] }}</td><td class="py-2 pr-4 text-right">₱ {{ number_format($r['sales'],2) }}</td><td class="py-2 pr-4 text-right">₱ {{ number_format($r['paid'],2) }}</td><td class="py-2 pr-4 text-right">₱ {{ number_format($r['unpaid'],2) }}</td><td class="py-2 pr-4 text-right">₱ {{ number_format($r['overdue'],2) }}</td></tr>@endforeach</tbody></table></div>
            </div>

            <div class="p-4 bg-white rounded-lg shadow">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="font-semibold">Expense Report</h2>
                    <div class="space-x-2">
                        <a class="text-sm px-2 py-1 rounded bg-slate-100 hover:bg-slate-200" href="{{ route('finance.reports', ['export'=>'csv','type'=>'expenses','start'=>$start,'end'=>$end]) }}">Export CSV</a>
                        <a class="text-sm px-2 py-1 rounded bg-slate-100 hover:bg-slate-200" href="{{ route('finance.reports', ['export'=>'pdf','type'=>'expenses','start'=>$start,'end'=>$end]) }}">Export PDF</a>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="w-full max-w-2xl mx-auto"><canvas id="expenseCategoryChart" class="w-full h-64"></canvas></div>
                    <div class="w-full max-w-2xl mx-auto"><canvas id="expenseTrendChart" class="w-full h-64"></canvas></div>
                </div>
            </div>

            <div class="p-4 bg-white rounded-lg shadow">
                <div class="flex items-center justify-between mb-3"><h2 class="font-semibold">Accounts Receivable (Aging)</h2>
                    <div class="space-x-2"><a class="text-sm px-2 py-1 rounded bg-slate-100 hover:bg-slate-200" href="{{ route('finance.reports', ['export'=>'csv','type'=>'ar','start'=>$start,'end'=>$end]) }}">Export CSV</a><a class="text-sm px-2 py-1 rounded bg-slate-100 hover:bg-slate-200" href="{{ route('finance.reports', ['export'=>'pdf','type'=>'ar','start'=>$start,'end'=>$end]) }}">Export PDF</a></div>
                </div>
                <div class="overflow-x-auto"><table class="min-w-full text-sm"><thead><tr class="text-left border-b"><th class="py-2 pr-4">Customer</th><th class="py-2 pr-4 text-right">Total</th><th class="py-2 pr-4 text-right">Paid</th><th class="py-2 pr-4 text-right">Balance</th></tr></thead><tbody>@foreach(($ar['rows'] ?? []) as $row)<tr class="border-b last:border-0"><td class="py-2 pr-4">{{ $row['customer'] }}</td><td class="py-2 pr-4 text-right">₱ {{ number_format($row['total'],2) }}</td><td class="py-2 pr-4 text-right">₱ {{ number_format($row['paid'],2) }}</td><td class="py-2 pr-4 text-right">₱ {{ number_format($row['balance'],2) }}</td></tr>@endforeach</tbody></table></div>
            </div>

            <div class="p-4 bg-white rounded-lg shadow">
                <div class="flex items-center justify-between mb-3"><h2 class="font-semibold">Accounts Payable (Aging)</h2>
                    <div class="space-x-2"><a class="text-sm px-2 py-1 rounded bg-slate-100 hover:bg-slate-200" href="{{ route('finance.reports', ['export'=>'csv','type'=>'ap','start'=>$start,'end'=>$end]) }}">Export CSV</a><a class="text-sm px-2 py-1 rounded bg-slate-100 hover:bg-slate-200" href="{{ route('finance.reports', ['export'=>'pdf','type'=>'ap','start'=>$start,'end'=>$end]) }}">Export PDF</a></div>
                </div>
                <div class="overflow-x-auto"><table class="min-w-full text-sm"><thead><tr class="text-left border-b"><th class="py-2 pr-4">Supplier</th><th class="py-2 pr-4 text-right">Total</th><th class="py-2 pr-4 text-right">Paid</th><th class="py-2 pr-4 text-right">Balance</th></tr></thead><tbody>@foreach(($ap['rows'] ?? []) as $row)<tr class="border-b last:border-0"><td class="py-2 pr-4">{{ $row['supplier'] }}</td><td class="py-2 pr-4 text-right">₱ {{ number_format($row['total'],2) }}</td><td class="py-2 pr-4 text-right">₱ {{ number_format($row['paid'],2) }}</td><td class="py-2 pr-4 text-right">₱ {{ number_format($row['balance'],2) }}</td></tr>@endforeach</tbody></table></div>
            </div>

            <div class="p-4 bg-white rounded-lg shadow">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="font-semibold">Cash Flow</h2>
                    <div class="space-x-2">
                        <a class="text-sm px-2 py-1 rounded bg-slate-100 hover:bg-slate-200" href="{{ route('finance.reports', ['export'=>'csv','type'=>'cashflow','start'=>$start,'end'=>$end]) }}">Export CSV</a>
                        <a class="text-sm px-2 py-1 rounded bg-slate-100 hover:bg-slate-200" href="{{ route('finance.reports', ['export'=>'pdf','type'=>'cashflow','start'=>$start,'end'=>$end]) }}">Export PDF</a>
                    </div>
                </div>
                <div class="w-full max-w-3xl mx-auto">
                    <canvas id="cashflowChart" class="w-full h-64"></canvas>
                </div>
            </div>

            <div class="p-4 bg-white rounded-lg shadow">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="font-semibold">Payroll & Salary Expense</h2>
                    <div class="space-x-2">
                        <a class="text-sm px-2 py-1 rounded bg-slate-100 hover:bg-slate-200" href="{{ route('finance.reports', ['export'=>'csv','type'=>'payroll','start'=>$start,'end'=>$end]) }}">Export CSV</a>
                        <a class="text-sm px-2 py-1 rounded bg-slate-100 hover:bg-slate-200" href="{{ route('finance.reports', ['export'=>'pdf','type'=>'payroll','start'=>$start,'end'=>$end]) }}">Export PDF</a>
                    </div>
                </div>
                <div class="overflow-x-auto"><table class="min-w-full text-sm"><thead><tr class="text-left border-b"><th class="py-2 pr-4">Pay Period</th><th class="py-2 pr-4 text-right">Total</th><th class="py-2 pr-4 text-right">Paid</th><th class="py-2 pr-4 text-right">Pending</th></tr></thead><tbody>@foreach(($payroll['rows'] ?? []) as $r)<tr class="border-b last:border-0"><td class="py-2 pr-4">{{ $r['period'] }}</td><td class="py-2 pr-4 text-right">₱ {{ number_format($r['total'],2) }}</td><td class="py-2 pr-4 text-right">₱ {{ number_format($r['paid'],2) }}</td><td class="py-2 pr-4 text-right">₱ {{ number_format($r['pending'],2) }}</td></tr>@endforeach</tbody></table></div>
            </div>

            <div class="p-4 bg-white rounded-lg shadow xl:col-span-2">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="font-semibold">Profit & Loss</h2>
                    <div class="space-x-2">
                        <a class="text-sm px-2 py-1 rounded bg-slate-100 hover:bg-slate-200" href="{{ route('finance.reports', ['export'=>'csv','type'=>'pnl','start'=>$start,'end'=>$end]) }}">Export CSV</a>
                        <a class="text-sm px-2 py-1 rounded bg-slate-100 hover:bg-slate-200" href="{{ route('finance.reports', ['export'=>'pdf','type'=>'pnl','start'=>$start,'end'=>$end]) }}">Export PDF</a>
                    </div>
                </div>
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                    <div class="lg:col-span-2 w-full max-w-4xl mx-auto"><canvas id="pnlChart" class="w-full h-64"></canvas></div>
                    <div class="overflow-x-auto"><table class="min-w-full text-sm"><thead><tr class="text-left border-b"><th class="py-2 pr-4">Month</th><th class="py-2 pr-4 text-right">Income</th><th class="py-2 pr-4 text-right">Expense</th><th class="py-2 pr-4 text-right">Payroll</th><th class="py-2 pr-4 text-right">AP Pay</th><th class="py-2 pr-4 text-right">Net</th></tr></thead><tbody>@foreach(($pnl['table'] ?? []) as $r)<tr class="border-b last:border-0"><td class="py-2 pr-4">{{ $r['month'] }}</td><td class="py-2 pr-4 text-right">₱ {{ number_format($r['income'],2) }}</td><td class="py-2 pr-4 text-right">₱ {{ number_format($r['expense'],2) }}</td><td class="py-2 pr-4 text-right">₱ {{ number_format($r['payroll'],2) }}</td><td class="py-2 pr-4 text-right">₱ {{ number_format($r['ap_payments'],2) }}</td><td class="py-2 pr-4 text-right font-semibold {{ $r['net']>=0 ? 'text-emerald-600':'text-rose-600' }}">₱ {{ number_format($r['net'],2) }}</td></tr>@endforeach</tbody></table></div>
                </div>
            </div>
        </div>
@else
    <div class="dashboard-header">
        <h1>Welcome Back! 👋</h1>
        <p>Here's what's happening with your finance today.</p>
    </div>

    <div class="dashboard-grid">
        <!-- Stats Cards -->
        <div class="card stats-card">
            <div class="card-icon" style="background: linear-gradient(135deg, #dc2626, #ea580c);">
                <i class="fas fa-peso-sign"></i>
            </div>
            <div class="card-content">
                <h3>Total Revenue</h3>
                <p class="stats-value">₱{{ $totalRevenue }}</p>
                <span class="stats-change {{ $revenueChangeDirection }}">{{ $revenueChangePct > 0 ? '+' : '' }}{{ $revenueChangePct }}% from last month</span>
            </div>
        </div>

        <div class="card stats-card">
            <div class="card-icon" style="background: linear-gradient(135deg, #ea580c, #f97316);">
                <i class="fas fa-receipt"></i>
            </div>
            <div class="card-content">
                <h3>Total Expenses</h3>
                <p class="stats-value">₱{{ $totalExpenses }}</p>
                <span class="stats-change {{ $expensesChangeDirection }}">{{ $expensesChangePct > 0 ? '+' : '' }}{{ $expensesChangePct }}% from last month</span>
            </div>
        </div>

        <div class="card stats-card">
            <div class="card-icon" style="background: linear-gradient(135deg, #f97316, #fb923c);">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="card-content">
                <h3>Net Profit</h3>
                <p class="stats-value">₱{{ $netProfit }}</p>
                <span class="stats-change {{ $profitChangeDirection }}">{{ $profitChangePct > 0 ? '+' : '' }}{{ $profitChangePct }}% from last month</span>
            </div>
        </div>

        <div class="card stats-card">
            <div class="card-icon" style="background: linear-gradient(135deg, #fb923c, #fdba74);">
                <i class="fas fa-users"></i>
            </div>
            <div class="card-content">
                <h3>Active Employees</h3>
                <p class="stats-value">{{ $activeEmployees }}</p>
                <span class="stats-change positive">+{{ $newEmployeesThisMonth }} new this month</span>
            </div>
        </div>
    </div>

    <div class="dashboard-row">
        <div class="card chart-card">
            <h2><i class="fas fa-chart-area"></i> Revenue Overview</h2>
            <p>Track your monthly revenue performance</p>
            <div class="chart-placeholder" style="height:auto; background:none;">
                <canvas id="revenueChart" height="300" aria-label="Monthly Revenue Chart" role="img"></canvas>
            </div>
        </div>

        <div class="card activity-card">
            <h2><i class="fas fa-clock"></i> Recent Activity</h2>
            <div class="activity-list">
                @forelse($activities as $activity)
                    <div class="activity-item">
                        <div class="activity-icon" style="background: {{ $activity['bg'] }};">
                            <i class="{{ $activity['icon'] }}" style="color: {{ $activity['color'] }};"></i>
                        </div>
                        <div class="activity-content">
                            <p class="activity-title">{{ $activity['title'] }}</p>
                            <span class="activity-time">{{ optional($activity['when'])->diffForHumans() }}</span>
                        </div>
                    </div>
                @empty
                    <div class="activity-item">
                        <div class="activity-icon" style="background: #fef3c7;">
                            <i class="fas fa-info-circle" style="color: #9ca3af;"></i>
                        </div>
                        <div class="activity-content">
                            <p class="activity-title">No recent activity</p>
                            <span class="activity-time">—</span>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
@endif
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        (function() {
            const reportsMode = {!! json_encode(isset($reportsMode) && $reportsMode) !!};
            if (!reportsMode) {
                const labels = {!! json_encode($revenueChartLabels ?? []) !!};
                const data = {!! json_encode($revenueChartData ?? []) !!};
                const ctx = document.getElementById('revenueChart');
                if (!ctx || !labels.length) return;
                const gradient = ctx.getContext('2d').createLinearGradient(0, 0, 0, 300);
                gradient.addColorStop(0, 'rgba(234, 88, 12, 0.25)');
                gradient.addColorStop(1, 'rgba(253, 186, 116, 0)');
                new Chart(ctx, {
                    type: 'line',
                    data: { labels, datasets: [{ label: 'Revenue (₱)', data, borderColor: '#ea580c', backgroundColor: gradient, borderWidth: 2, fill: true, pointRadius: 3, pointBackgroundColor: '#ea580c', tension: 0.35 }] },
                    options: { responsive: true, maintainAspectRatio: false }
                });
                return;
            }

            const revenueSeries = {!! json_encode($revenue['series'] ?? []) !!};
            const expenseSeries = {!! json_encode($expenses['series'] ?? []) !!};
            const expenseByCat = {!! json_encode($expenses['by_category'] ?? []) !!};
            const cashSeries = {!! json_encode($cash['series'] ?? []) !!};
            const pnlTable = {!! json_encode($pnl['table'] ?? []) !!};

            const by = (arr, key) => arr.map(r => r[key] ?? 0);

            const revCtx = document.getElementById('revenueReportChart');
            if (revCtx && revenueSeries.length) {
                new Chart(revCtx.getContext('2d'), {
                    type: 'line',
                    data: { labels: by(revenueSeries,'ym'), datasets: [
                        { label: 'Sales', data: by(revenueSeries,'sales'), borderColor: '#0ea5e9', backgroundColor: 'rgba(14,165,233,.15)', fill:true, tension:.3 },
                        { label: 'Paid', data: by(revenueSeries,'paid'), borderColor: '#10b981', backgroundColor: 'rgba(16,185,129,.15)', fill:true, tension:.3 },
                    ]},
                    options: { responsive: true, maintainAspectRatio: false }
                });
            }

            const expTrendCtx = document.getElementById('expenseTrendChart');
            if (expTrendCtx && expenseSeries.length) {
                new Chart(expTrendCtx.getContext('2d'), {
                    type: 'line',
                    data: { labels: by(expenseSeries,'ym'), datasets: [ { label:'Expenses', data: by(expenseSeries,'expense'), borderColor:'#ef4444', backgroundColor:'rgba(239,68,68,.15)', fill:true, tension:.3 } ] },
                    options: { responsive: true, maintainAspectRatio: false }
                });
            }

            const expCatCtx = document.getElementById('expenseCategoryChart');
            if (expCatCtx && Object.keys(expenseByCat).length) {
                const catLabels = Object.keys(expenseByCat);
                const catValues = Object.values(expenseByCat).map(v => parseFloat(v ?? 0));
                new Chart(expCatCtx.getContext('2d'), {
                    type: 'doughnut', data: { labels: catLabels, datasets: [{ data: catValues, backgroundColor: ['#60a5fa','#34d399','#fbbf24','#f472b6','#94a3b8'] }] },
                    options: { responsive: true, maintainAspectRatio: false }
                });
            }

            const cashCtx = document.getElementById('cashflowChart');
            if (cashCtx && cashSeries.length) {
                new Chart(cashCtx.getContext('2d'), {
                    type: 'line', data: { labels: by(cashSeries,'ym'), datasets: [
                        { label:'Inflow', data: by(cashSeries,'inflow'), borderColor:'#10b981', backgroundColor:'rgba(16,185,129,.15)', fill:true, tension:.3 },
                        { label:'Outflow', data: by(cashSeries,'outflow'), borderColor:'#ef4444', backgroundColor:'rgba(239,68,68,.15)', fill:true, tension:.3 },
                        { label:'Net', data: by(cashSeries,'net'), borderColor:'#0ea5e9', backgroundColor:'rgba(14,165,233,.15)', fill:true, tension:.3 },
                    ]}, options:{ responsive:true, maintainAspectRatio:false }
                });
            }

            const pnlCtx = document.getElementById('pnlChart');
            if (pnlCtx && pnlTable.length) {
                new Chart(pnlCtx.getContext('2d'), {
                    type: 'bar', data: { labels: by(pnlTable,'month'), datasets: [
                        { label:'Income', data: by(pnlTable,'income'), backgroundColor:'#10b981' },
                        { label:'Expenses+Payroll+AP', data: pnlTable.map(r => (r.expense||0)+(r.payroll||0)+(r.ap_payments||0)), backgroundColor:'#f59e0b' },
                        { label:'Net', data: by(pnlTable,'net'), backgroundColor:'#0ea5e9' }
                    ] }, options: { responsive:true, maintainAspectRatio:false, scales:{ x:{ stacked:false }, y:{ beginAtZero:true } } }
                });
            }
        })();
    </script>
@endpush

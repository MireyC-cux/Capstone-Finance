@extends('layouts.finance_app')

@section('title', 'Finance Dashboard - Home')

@push('styles')
    <link href="{{ asset('css/finance_dashboard.css') }}" rel="stylesheet">
@endpush

@section('content')
@if(!empty($reportsMode))
    <div style="padding: 20px;">
        <div class="d-flex justify-content-between align-items-start" style="margin-bottom: 2rem;">
            <form method="get" action="{{ route('finance.reports') }}" class="d-flex gap-2 align-items-end">
                <div>
                    <label class="form-label" style="margin-bottom: 0.25rem;">Start</label>
                    <input type="date" name="start" value="{{ $start ?? now()->subMonths(11)->startOfMonth()->toDateString() }}" class="form-control form-control-sm">
                </div>
                <div>
                    <label class="form-label" style="margin-bottom: 0.25rem;">End</label>
                    <input type="date" name="end" value="{{ $end ?? now()->toDateString() }}" class="form-control form-control-sm">
                </div>
                <button class="btn btn-primary btn-sm">Apply</button>
            </form>
        </div>

        <div class="row g-4">
            <div class="col-12 col-xl-6">
                <div class="card" style="padding: 1.25rem;">
                    <div class="d-flex justify-content-between align-items-center" style="margin-bottom: 1rem;">
                        <h2 style="font-size: 20px; font-weight: 600; margin: 0;">Revenue Report</h2>
                        <div class="d-flex gap-2">
                            <a class="btn btn-sm" style="border: 1px solid var(--border-card); background: white; font-size: 13px;" href="{{ route('finance.reports', ['export'=>'csv','type'=>'revenue','start'=>$start,'end'=>$end]) }}">CSV</a>
                            <a class="btn btn-sm" style="border: 1px solid var(--border-card); background: white; font-size: 13px;" href="{{ route('finance.reports', ['export'=>'pdf','type'=>'revenue','start'=>$start,'end'=>$end]) }}">PDF</a>
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
    <div style="padding: 20px; background: #F5F7FA;">
        <!-- Top Metric Cards -->
        <div class="row g-3" style="margin-bottom: 1.5rem;">
            <div class="col-6 col-lg-3">
                <div class="card" style="background: white; border: 1px solid #E5E7EB; padding: 1rem; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                    <div class="d-flex align-items-center gap-3">
                        <div style="width: 42px; height: 42px; border-radius: 50%; background: #3B82F6; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <i class="fas fa-wallet" style="font-size: 18px; color: white;"></i>
                        </div>
                        <div style="flex: 1; min-width: 0;">
                            <div style="font-size: 12px; color: #6B7280; margin-bottom: 2px;">Monthly Revenue</div>
                            <div style="font-size: 20px; font-weight: 700; color: #3B82F6;">₱{{ $totalRevenue }}</div>
                            <div style="font-size: 11px; color: #9CA3AF; margin-top: 2px;">{{ $revenueChangePct > 0 ? '+' : '' }}{{ $revenueChangePct }}%</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-lg-3">
                <div class="card" style="background: white; border: 1px solid #E5E7EB; padding: 1rem; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                    <div class="d-flex align-items-center gap-3">
                        <div style="width: 42px; height: 42px; border-radius: 50%; background: #3B82F6; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <i class="fas fa-receipt" style="font-size: 18px; color: white;"></i>
                        </div>
                        <div style="flex: 1; min-width: 0;">
                            <div style="font-size: 12px; color: #6B7280; margin-bottom: 2px;">Monthly Expenses</div>
                            <div style="font-size: 20px; font-weight: 700; color: #3B82F6;">₱{{ $totalExpenses }}</div>
                            <div style="font-size: 11px; color: #9CA3AF; margin-top: 2px;">{{ $expensesChangePct > 0 ? '+' : '' }}{{ $expensesChangePct }}%</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-lg-3">
                <div class="card" style="background: white; border: 1px solid #E5E7EB; padding: 1rem; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                    <div class="d-flex align-items-center gap-3">
                        <div style="width: 42px; height: 42px; border-radius: 50%; background: #0EA5E9; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <i class="fas fa-hand-holding-usd" style="font-size: 18px; color: white;"></i>
                        </div>
                        <div style="flex: 1; min-width: 0;">
                            <div style="font-size: 12px; color: #6B7280; margin-bottom: 2px;">AR Outstanding</div>
                            <div style="font-size: 20px; font-weight: 700; color: #0EA5E9;">₱{{ number_format((float)($arOutstanding ?? 0), 2) }}</div>
                            <div style="font-size: 11px; color: #9CA3AF; margin-top: 2px;">Overdue: {{ (int)($overdueInvoicesCount ?? 0) }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-lg-3">
                <div class="card" style="background: white; border: 1px solid #E5E7EB; padding: 1rem; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                    <div class="d-flex align-items-center gap-3">
                        <div style="width: 42px; height: 42px; border-radius: 50%; background: #EF4444; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <i class="fas fa-file-invoice-dollar" style="font-size: 18px; color: white;"></i>
                        </div>
                        <div style="flex: 1; min-width: 0;">
                            <div style="font-size: 12px; color: #6B7280; margin-bottom: 2px;">AP Outstanding</div>
                            <div style="font-size: 20px; font-weight: 700; color: #EF4444;">₱{{ number_format((float)($apOutstanding ?? 0), 2) }}</div>
                            <div style="font-size: 11px; color: #9CA3AF; margin-top: 2px;">Due soon: {{ (int)($apDueSoonCount ?? 0) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="row g-3">
            <!-- Left: Activity Overview Chart -->
            <div class="col-12 col-lg-8">
                <div class="card" style="background: white; border: 1px solid #E5E7EB; padding: 1.5rem; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                    <div class="d-flex justify-content-between align-items-center" style="margin-bottom: 1rem;">
                        <div>
                            <h2 style="font-size: 18px; font-weight: 600; color: #111827; margin: 0;">Activity Overview</h2>
                            <div style="font-size: 12px; color: #6B7280; margin-top: 4px;">
                                <span class="badge" style="background: #10B981; color: white; padding: 2px 8px; font-size: 11px; margin-right: 8px;">Completed: 9</span>
                                <span style="color: #9CA3AF;">Avg Duration: <strong>100%</strong></span>
                                <span style="color: #9CA3AF; margin-left: 12px;">Payroll Processed: <strong>₱0.00</strong></span>
                            </div>
                        </div>
                        <select class="form-select form-select-sm" style="width: auto; font-size: 13px; border-color: #E5E7EB;">
                            <option>All</option>
                            <option>This Week</option>
                            <option>This Month</option>
                        </select>
                    </div>
                    <div style="height: 350px;">
                        <canvas id="revenueChart" height="350" aria-label="Activity Overview Chart" role="img"></canvas>
                    </div>
                </div>
            </div>

            <!-- Right: Finance Info Cards Sidebar -->
            <div class="col-12 col-lg-4">
                <div class="d-flex flex-column gap-3">
                    <!-- Today Collections -->
                    <div class="card" style="background: white; border: 1px solid #E5E7EB; padding: 1rem; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div style="font-size: 12px; color: #6B7280;">Today Collections</div>
                                <div style="font-size: 24px; font-weight: 700; color: #10B981;">₱{{ number_format((float)($todayCollections ?? 0), 2) }}</div>
                            </div>
                            <div style="width: 36px; height: 36px; border-radius: 6px; background: #D1FAE5; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-coins" style="font-size: 16px; color: #10B981;"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Today Disbursements -->
                    <div class="card" style="background: white; border: 1px solid #E5E7EB; padding: 1rem; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div style="font-size: 12px; color: #6B7280;">Today Disbursements</div>
                                <div style="font-size: 24px; font-weight: 700; color: #EF4444;">₱{{ number_format((float)($todayDisbursements ?? 0), 2) }}</div>
                            </div>
                            <div style="width: 36px; height: 36px; border-radius: 6px; background: #FEE2E2; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-money-bill-wave" style="font-size: 16px; color: #EF4444;"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Pending Approvals -->
                    <div class="card" style="background: white; border: 1px solid #E5E7EB; padding: 1rem; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div style="font-size: 12px; color: #6B7280;">Pending Approvals</div>
                                <div style="font-size: 24px; font-weight: 700; color: #F59E0B;">{{ (int)($pendingApprovalsCount ?? 0) }}</div>
                            </div>
                            <div style="width: 36px; height: 36px; border-radius: 6px; background: #FEF3C7; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-clipboard-check" style="font-size: 16px; color: #F59E0B;"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Unbilled Completed SRs -->
                    <div class="card" style="background: white; border: 1px solid #E5E7EB; padding: 1rem; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div style="font-size: 12px; color: #6B7280;">Unbilled Completed SRs</div>
                                <div style="font-size: 14px; color: #6B7280;">Count</div>
                                <div style="font-size: 20px; font-weight: 700; color: #111827;">{{ (int)($unbilledSrCount ?? 0) }}</div>
                            </div>
                            <div class="text-end">
                                <div style="font-size: 14px; color: #6B7280;">Est. Total</div>
                                <div style="font-size: 20px; font-weight: 700; color: #111827;">₱{{ number_format((float)($unbilledSrTotal ?? 0), 2) }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Finance Actions -->
                    <div class="d-flex flex-column gap-2">
                        <a href="{{ route('finance.billing.index') }}" class="btn" style="background: #3B82F6; color: white; font-size: 13px; font-weight: 600; padding: 0.625rem 1rem; border: none; border-radius: 6px;">
                            <i class="fas fa-file-invoice"></i> Create/Generate Invoice
                        </a>
                        <a href="{{ route('finance.accounts-receivable') }}" class="btn" style="background: #10B981; color: white; font-size: 13px; font-weight: 600; padding: 0.625rem 1rem; border: none; border-radius: 6px;">
                            <i class="fas fa-cash-register"></i> Record Customer Payment
                        </a>
                        <a href="{{ route('finance.accounts-payable') }}" class="btn" style="background: #F59E0B; color: #111827; font-size: 13px; font-weight: 700; padding: 0.625rem 1rem; border: none; border-radius: 6px;">
                            <i class="fas fa-file-invoice-dollar"></i> Record Supplier Bill
                        </a>
                        <a href="{{ route('finance.payroll') }}" class="btn" style="background: #334155; color: white; font-size: 13px; font-weight: 600; padding: 0.625rem 1rem; border: none; border-radius: 6px;">
                            <i class="fas fa-money-check-alt"></i> Run Payroll
                        </a>
                    </div>

                    <!-- Removed extra quick links to keep only four finance actions above -->
                </div>
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
                const revenueData = {!! json_encode($revenueChartData ?? []) !!};
                const expensesData = revenueData.map(v => v * 0.6); // Mock expenses data
                const ctx = document.getElementById('revenueChart');
                if (!ctx || !labels.length) return;
                
                const ctxGradient = ctx.getContext('2d');
                const revenueGradient = ctxGradient.createLinearGradient(0, 0, 0, 350);
                revenueGradient.addColorStop(0, 'rgba(59, 130, 246, 0.4)');
                revenueGradient.addColorStop(1, 'rgba(59, 130, 246, 0.05)');
                
                const expensesGradient = ctxGradient.createLinearGradient(0, 0, 0, 350);
                expensesGradient.addColorStop(0, 'rgba(16, 185, 129, 0.3)');
                expensesGradient.addColorStop(1, 'rgba(16, 185, 129, 0.05)');
                
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels,
                        datasets: [
                            {
                                label: 'Revenue',
                                data: revenueData,
                                borderColor: '#3B82F6',
                                backgroundColor: revenueGradient,
                                borderWidth: 2.5,
                                fill: true,
                                pointRadius: 0,
                                pointHoverRadius: 5,
                                pointBackgroundColor: '#3B82F6',
                                tension: 0.4
                            },
                            {
                                label: 'Expenses',
                                data: expensesData,
                                borderColor: '#10B981',
                                backgroundColor: expensesGradient,
                                borderWidth: 2.5,
                                fill: true,
                                pointRadius: 0,
                                pointHoverRadius: 5,
                                pointBackgroundColor: '#10B981',
                                tension: 0.4
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top',
                                align: 'end',
                                labels: {
                                    boxWidth: 12,
                                    boxHeight: 12,
                                    usePointStyle: true,
                                    pointStyle: 'circle',
                                    padding: 15,
                                    font: { size: 12, weight: '500' }
                                }
                            },
                            tooltip: {
                                mode: 'index',
                                intersect: false,
                                backgroundColor: 'rgba(255, 255, 255, 0.95)',
                                titleColor: '#111827',
                                bodyColor: '#6B7280',
                                borderColor: '#E5E7EB',
                                borderWidth: 1,
                                padding: 12,
                                displayColors: true,
                                callbacks: {
                                    label: function(context) {
                                        return context.dataset.label + ': ₱' + context.parsed.y.toLocaleString('en-PH', {minimumFractionDigits: 2});
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: { display: false, drawBorder: false },
                                ticks: { color: '#9CA3AF', font: { size: 11 } }
                            },
                            y: {
                                grid: { color: '#F3F4F6', drawBorder: false },
                                ticks: {
                                    color: '#9CA3AF',
                                    font: { size: 11 },
                                    callback: function(value) {
                                        return '₱' + value.toLocaleString();
                                    }
                                }
                            }
                        },
                        interaction: {
                            mode: 'nearest',
                            axis: 'x',
                            intersect: false
                        }
                    }
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

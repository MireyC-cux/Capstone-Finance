@extends('layouts.finance_app')

@section('title', 'Finance Reporting')

@section('content')
<!-- Page Header -->
<div class="container-xxl py-3">
    <div class="reports-hero p-4 p-md-5 mb-4 position-relative overflow-hidden">
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
            <div>
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="badge bg-light text-primary border border-primary">Reports</span>
                    <span class="badge bg-light text-secondary border">Overview</span>
                </div>
                <h1 class="mb-1">Finance Reporting</h1>
                <p class="text-muted mb-0">Generate comprehensive financial reports and analytics for business insights.</p>
            </div>
            
        </div>
        <div class="hero-shape"></div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3 p-md-4">
            <form class="row g-2 g-md-3 align-items-center">
                <div class="col-12 col-md">
                    <div class="input-group input-group-lg">
                        <span class="input-group-text bg-white"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                        <input type="search" class="form-control" placeholder="Search reports, e.g. 'Aging', 'Payroll'">
                    </div>
                </div>
                <div class="col-6 col-md-auto">
                    <select class="form-select form-select-lg">
                        <option selected>Period: This Month</option>
                        <option>Last Month</option>
                        <option>Quarter to Date</option>
                        <option>Year to Date</option>
                        <option>Custom...</option>
                    </select>
                </div>
               
                <div class="col-12 col-md-auto">
                    <button type="button" class="btn btn-primary btn-lg rounded-3">
                        <i class="fa-solid fa-sliders me-2"></i>
                        Apply
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-12 col-xl-6">
            <div class="card" style="padding: 1.25rem;">
                <div class="d-flex justify-content-between align-items-center" style="margin-bottom: 1rem;">
                    <h2 style="font-size: 20px; font-weight: 600; margin: 0;">Revenue Report</h2>
                    <div class="d-flex gap-2">
                        <a class="btn btn-sm btn-outline-secondary" href="{{ route('finance.reports', ['export'=>'csv','type'=>'revenue','start'=>$start ?? null,'end'=>$end ?? null]) }}">CSV</a>
                        <a class="btn btn-sm btn-outline-secondary" href="{{ route('finance.reports', ['export'=>'pdf','type'=>'revenue','start'=>$start ?? null,'end'=>$end ?? null]) }}">PDF</a>
                    </div>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-6 col-md-3"><div class="small text-muted">Sales</div><div class="fw-semibold">₱ {{ number_format((float)($revenue['totals']['sales_total'] ?? 0),2) }}</div></div>
                    <div class="col-6 col-md-3"><div class="small text-muted">Paid</div><div class="fw-semibold text-success">₱ {{ number_format((float)($revenue['totals']['paid_total'] ?? 0),2) }}</div></div>
                    <div class="col-6 col-md-3"><div class="small text-muted">Outstanding</div><div class="fw-semibold text-primary">₱ {{ number_format((float)($revenue['totals']['outstanding_total'] ?? 0),2) }}</div></div>
                    
                </div>
                <div class="w-100 mx-auto"><canvas id="revenueReportChart" class="w-100" style="height:260px"></canvas></div>
                <div class="mt-3 table-responsive">
                    <table class="table table-sm align-middle">
                        <thead class="table-light"><tr><th>Month</th><th class="text-end">Sales</th><th class="text-end">Paid</th><th class="text-end">Unpaid</th><th class="text-end">Overdue</th></tr></thead>
                        <tbody>
                            @foreach(($revenue['table'] ?? []) as $r)
                                <tr>
                                    <td>{{ $r['month'] }}</td>
                                    <td class="text-end">₱ {{ number_format($r['sales'],2) }}</td>
                                    <td class="text-end">₱ {{ number_format($r['paid'],2) }}</td>
                                    <td class="text-end">₱ {{ number_format($r['unpaid'],2) }}</td>
                                    
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card p-4">
                <div class="d-flex justify-content-between align-items-center mb-3"><h2 class="h6 mb-0">Payroll & Salary Expense</h2>
                    <div class="d-flex gap-2"><a class="btn btn-sm btn-outline-secondary" href="{{ route('finance.reports', ['export'=>'csv','type'=>'payroll','start'=>$start ?? null,'end'=>$end ?? null]) }}">Export CSV</a><a class="btn btn-sm btn-outline-secondary" href="{{ route('finance.reports', ['export'=>'pdf','type'=>'payroll','start'=>$start ?? null,'end'=>$end ?? null]) }}">Export PDF</a></div>
                </div>
                <div class="table-responsive"><table class="table table-sm align-middle"><thead class="table-light"><tr><th>Pay Period</th><th class="text-end">Total</th><th class="text-end">Paid</th><th class="text-end">Pending</th></tr></thead><tbody>@foreach(($payroll['rows'] ?? []) as $r)<tr><td>{{ $r['period'] }}</td><td class="text-end">₱ {{ number_format($r['total'],2) }}</td><td class="text-end">₱ {{ number_format($r['paid'],2) }}</td><td class="text-end">₱ {{ number_format($r['pending'],2) }}</td></tr>@endforeach</tbody></table></div>
            </div>
        </div>

        <div class="col-12">
            <div class="card p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2 class="h6 mb-0">Profit & Loss</h2>
                    <div class="d-flex gap-2"><a class="btn btn-sm btn-outline-secondary" href="{{ route('finance.reports', ['export'=>'csv','type'=>'pnl','start'=>$start ?? null,'end'=>$end ?? null]) }}">Export CSV</a><a class="btn btn-sm btn-outline-secondary" href="{{ route('finance.reports', ['export'=>'pdf','type'=>'pnl','start'=>$start ?? null,'end'=>$end ?? null]) }}">Export PDF</a></div>
                </div>
                <div class="row g-3">
                    <div class="col-12 col-lg-8"><canvas id="pnlChart" class="w-100" style="height:260px"></canvas></div>
                    <div class="col-12 col-lg-4">
                        <div class="table-responsive"><table class="table table-sm align-middle"><thead class="table-light"><tr><th>Month</th><th class="text-end">Income</th><th class="text-end">Expense</th><th class="text-end">Payroll</th><th class="text-end">AP Pay</th><th class="text-end">Net</th></tr></thead><tbody>@foreach(($pnl['table'] ?? []) as $r)<tr><td>{{ $r['month'] }}</td><td class="text-end">₱ {{ number_format($r['income'],2) }}</td><td class="text-end">₱ {{ number_format($r['expense'],2) }}</td><td class="text-end">₱ {{ number_format($r['payroll'],2) }}</td><td class="text-end">₱ {{ number_format($r['ap_payments'],2) }}</td><td class="text-end fw-semibold {{ ($r['net'] ?? 0)>=0 ? 'text-success' : 'text-danger' }}">₱ {{ number_format($r['net'],2) }}</td></tr>@endforeach</tbody></table></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 g-md-4 mb-4 row-cols-2 row-cols-md-4 d-none">
        <div class="col">
            <div class="stat-card card h-100 border-0">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="text-muted small">Revenue</span>
                        <span class="badge bg-light text-success border border-success">+8.2%</span>
                    </div>
                    <div class="h4 mb-0">₱1,245,300</div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="stat-card card h-100 border-0">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="text-muted small">Expenses</span>
                        <span class="badge bg-light text-danger border border-danger">-3.4%</span>
                    </div>
                    <div class="h4 mb-0">₱864,920</div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="stat-card card h-100 border-0">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="text-muted small">Outstanding AR</span>
                        <span class="badge bg-light text-warning border border-warning">12 days</span>
                    </div>
                    <div class="h4 mb-0">₱210,450</div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="stat-card card h-100 border-0">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="text-muted small">Inventory Value</span>
                        <span class="badge bg-light text-info border border-info">In stock</span>
                    </div>
                    <div class="h4 mb-0">₱512,780</div>
                </div>
            </div>
        </div>
    </div>
    <!-- Report Categories Grid (hidden) -->
    <div class="row g-4 mb-4 row-cols-1 row-cols-md-2 row-cols-lg-3 d-none">
        <!-- Financial Statements -->
        <div class="col">
            <div class="report-card card h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="icon-badge bg-blue">
                            <i class="fa-solid fa-file-invoice-dollar"></i>
                        </div>
                        <div>
                            <h5 class="card-title mb-0">Financial Statements</h5>
                            <small class="text-muted">Core financial reports</small>
                        </div>
                    </div>
                    <div class="list-group list-group-flush">
                        <a href="#" class="action-link list-group-item px-0 d-flex align-items-center justify-content-between text-decoration-none">
                            <span class="fw-semibold text-body">Income Statement</span>
                            <i class="fa-solid fa-arrow-right text-muted"></i>
                        </a>
                        <a href="#" class="action-link list-group-item px-0 d-flex align-items-center justify-content-between text-decoration-none">
                            <span class="fw-semibold text-body">Balance Sheet</span>
                            <i class="fa-solid fa-arrow-right text-muted"></i>
                        </a>
                        <a href="#" class="action-link list-group-item px-0 d-flex align-items-center justify-content-between text-decoration-none">
                            <span class="fw-semibold text-body">Cash Flow Statement</span>
                            <i class="fa-solid fa-arrow-right text-muted"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Accounts Reports -->
        <div class="col">
            <div class="report-card card h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="icon-badge bg-emerald">
                            <i class="fa-solid fa-receipt"></i>
                        </div>
                        <div>
                            <h5 class="card-title mb-0">Accounts Reports</h5>
                            <small class="text-muted">AR & AP analytics</small>
                        </div>
                    </div>
                    <div class="list-group list-group-flush">
                        <a href="{{ route('finance.ar.aging') }}" class="action-link list-group-item px-0 d-flex align-items-center justify-content-between text-decoration-none">
                            <span class="fw-semibold text-body">AR Aging Report</span>
                            <i class="fa-solid fa-arrow-right text-muted"></i>
                        </a>
                        <a href="#" class="action-link list-group-item px-0 d-flex align-items-center justify-content-between text-decoration-none">
                            <span class="fw-semibold text-body">AP Aging Report</span>
                            <i class="fa-solid fa-arrow-right text-muted"></i>
                        </a>
                        <a href="#" class="action-link list-group-item px-0 d-flex align-items-center justify-content-between text-decoration-none">
                            <span class="fw-semibold text-body">Collection Summary</span>
                            <i class="fa-solid fa-arrow-right text-muted"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payroll Reports -->
        <div class="col">
            <div class="report-card card h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="icon-badge bg-indigo">
                            <i class="fa-solid fa-money-check-dollar"></i>
                        </div>
                        <div>
                            <h5 class="card-title mb-0">Payroll Reports</h5>
                            <small class="text-muted">Employee compensation</small>
                        </div>
                    </div>
                    <div class="list-group list-group-flush">
                        <a href="{{ route('finance.payroll.export') }}" class="action-link list-group-item px-0 d-flex align-items-center justify-content-between text-decoration-none">
                            <span class="fw-semibold text-body">Payroll Register</span>
                            <i class="fa-solid fa-arrow-right text-muted"></i>
                        </a>
                        <a href="#" class="action-link list-group-item px-0 d-flex align-items-center justify-content-between text-decoration-none">
                            <span class="fw-semibold text-body">Payroll Summary</span>
                            <i class="fa-solid fa-arrow-right text-muted"></i>
                        </a>
                        <a href="{{ route('finance.disbursement.index') }}" class="action-link list-group-item px-0 d-flex align-items-center justify-content-between text-decoration-none">
                            <span class="fw-semibold text-body">Disbursement Report</span>
                            <i class="fa-solid fa-arrow-right text-muted"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Inventory Reports -->
        <div class="col">
            <div class="report-card card h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="icon-badge bg-amber">
                            <i class="fa-solid fa-boxes-stacked"></i>
                        </div>
                        <div>
                            <h5 class="card-title mb-0">Inventory Reports</h5>
                            <small class="text-muted">Stock & valuation</small>
                        </div>
                    </div>
                    <div class="list-group list-group-flush">
                        <a href="{{ route('finance.inventory.reports.index') }}" class="action-link list-group-item px-0 d-flex align-items-center justify-content-between text-decoration-none">
                            <span class="fw-semibold text-body">Stock Valuation</span>
                            <i class="fa-solid fa-arrow-right text-muted"></i>
                        </a>
                        <a href="#" class="action-link list-group-item px-0 d-flex align-items-center justify-content-between text-decoration-none">
                            <span class="fw-semibold text-body">Stock Movement</span>
                            <i class="fa-solid fa-arrow-right text-muted"></i>
                        </a>
                        <a href="#" class="action-link list-group-item px-0 d-flex align-items-center justify-content-between text-decoration-none">
                            <span class="fw-semibold text-body">Low Stock Alert</span>
                            <i class="fa-solid fa-arrow-right text-muted"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Expense Reports -->
        <div class="col">
            <div class="report-card card h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="icon-badge bg-rose">
                            <i class="fa-solid fa-chart-pie"></i>
                        </div>
                        <div>
                            <h5 class="card-title mb-0">Expense Reports</h5>
                            <small class="text-muted">Cost analysis</small>
                        </div>
                    </div>
                    <div class="list-group list-group-flush">
                        <a href="{{ route('finance.expenses') }}" class="action-link list-group-item px-0 d-flex align-items-center justify-content-between text-decoration-none">
                            <span class="fw-semibold text-body">Expense Summary</span>
                            <i class="fa-solid fa-arrow-right text-muted"></i>
                        </a>
                        <a href="#" class="action-link list-group-item px-0 d-flex align-items-center justify-content-between text-decoration-none">
                            <span class="fw-semibold text-body">Category Breakdown</span>
                            <i class="fa-solid fa-arrow-right text-muted"></i>
                        </a>
                        <a href="#" class="action-link list-group-item px-0 d-flex align-items-center justify-content-between text-decoration-none">
                            <span class="fw-semibold text-body">Budget vs Actual</span>
                            <i class="fa-solid fa-arrow-right text-muted"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Custom Reports -->
        <div class="col">
            <div class="report-card card h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="icon-badge bg-purple">
                            <i class="fa-solid fa-chart-bar"></i>
                        </div>
                        <div>
                            <h5 class="card-title mb-0">Custom Reports</h5>
                            <small class="text-muted">Build your own</small>
                        </div>
                    </div>
                    <div class="list-group list-group-flush">
                        <a href="#" class="action-link list-group-item px-0 d-flex align-items-center justify-content-between text-decoration-none">
                            <span class="fw-semibold text-body">Report Builder</span>
                            <i class="fa-solid fa-arrow-right text-muted"></i>
                        </a>
                        <a href="#" class="action-link list-group-item px-0 d-flex align-items-center justify-content-between text-decoration-none">
                            <span class="fw-semibold text-body">Saved Reports</span>
                            <i class="fa-solid fa-arrow-right text-muted"></i>
                        </a>
                        <a href="#" class="action-link list-group-item px-0 d-flex align-items-center justify-content-between text-decoration-none">
                            <span class="fw-semibold text-body">Scheduled Reports</span>
                            <i class="fa-solid fa-arrow-right text-muted"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Export -->
    <div class="card quick-export-card border-0 shadow-sm">
        <div class="card-body p-4">
            <form method="get" action="{{ route('finance.reports') }}" class="row g-2 g-md-3 align-items-end">
                <div class="col-12 col-md-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <label class="form-label small mb-1">Report(s)</label>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-light border" id="qe-select-all">Select all</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="qe-clear">Clear</button>
                        </div>
                    </div>
                    <select name="type[]" class="form-select" multiple size="8">
                        <option value="all">All Reports</option>
                        <option value="revenue">Revenue</option>
                     
                      
                        <option value="payroll">Payroll</option>
                        <option value="pnl">Profit & Loss</option>
                        
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small mb-1">Start</label>
                    <input type="date" name="start" value="{{ $start ?? now()->subMonths(11)->startOfMonth()->toDateString() }}" class="form-control form-control-lg">
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label small mb-1">End</label>
                    <input type="date" name="end" value="{{ $end ?? now()->toDateString() }}" class="form-control form-control-lg">
                </div>
                <div class="col-12 col-md-4 d-flex gap-2 justify-content-start justify-content-md-end">
                    <button type="submit" name="export" value="pdf" class="btn btn-gradient btn-lg">
                        <i class="fa-solid fa-file-pdf me-2"></i>Export PDF
                    </button>
                    <button type="submit" name="export" value="csv" class="btn btn-light border btn-lg">
                        <i class="fa-solid fa-file-csv me-2 text-primary"></i>Export CSV
                    </button>
                    {{-- <button type="button" class="btn btn-light border btn-lg" onclick="window.print()">
                        <i class="fa-solid fa-print me-2"></i>Print
                    </button> --}}
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.report-card{border-radius:16px;background:#fff;border:1px solid #e8ecf1;transition:transform .2s ease,box-shadow .2s ease}
.report-card:hover{transform:translateY(-2px);box-shadow:0 12px 30px rgba(2,6,23,.08)}
.icon-badge{height:56px;width:56px;border-radius:14px;display:flex;align-items:center;justify-content:center;color:#fff;box-shadow:0 8px 20px rgba(0,0,0,0.12);font-size:1.25rem}
.icon-badge.bg-blue{background:linear-gradient(135deg,#3b82f6,#2563eb)}
.icon-badge.bg-emerald{background:linear-gradient(135deg,#10b981,#059669)}
.icon-badge.bg-indigo{background:linear-gradient(135deg,#6366f1,#4f46e5)}
.icon-badge.bg-amber{background:linear-gradient(135deg,#f59e0b,#d97706)}
.icon-badge.bg-rose{background:linear-gradient(135deg,#f43f5e,#e11d48)}
.icon-badge.bg-purple{background:linear-gradient(135deg,#a855f7,#7c3aed)}
.action-link{border-radius:12px;border:1px solid #e8ecf1;background:#f8fafc;padding:.75rem 1rem;margin:.35rem 0;transition:all .15s ease}
.action-link:hover{background:#eef6ff;border-color:#bfd6ff}
.btn-export{border-radius:12px}
.btn-gradient{border-radius:12px;background:linear-gradient(90deg,#e65c33,#f57c42);color:#fff;border:0;padding:.6rem 1rem;box-shadow:0 4px 14px rgba(230,92,51,.3)}
.btn-gradient:hover{filter:brightness(.95);color:#fff}
.btn-ghost{border-radius:12px;background:#fff;color:#0d6efd;border-color:#cfe2ff}
.btn-ghost:hover{background:#f1f6ff;color:#0a58ca;box-shadow:0 6px 18px rgba(13,110,253,.15)}
.reports-hero{border-radius:24px;background:radial-gradient(700px 200px at 100% 0,rgba(59,130,246,.08),transparent),linear-gradient(180deg,#ffffff,#f8fafc);border:1px solid #e8ecf1;box-shadow:0 10px 26px rgba(2,6,23,.06)}
.reports-hero .badge{border-radius:999px;padding:.35rem .65rem}
.hero-shape{position:absolute;right:-48px;top:-48px;width:220px;height:220px;background:radial-gradient(closest-side,rgba(59,130,246,.25),transparent 70%),radial-gradient(closest-side,rgba(99,102,241,.25),transparent 70%);border-radius:50%;filter:blur(4px);pointer-events:none}
.stat-card{border-radius:16px;background:linear-gradient(180deg,#ffffff,#fbfdff);border:1px solid #e8ecf1;box-shadow:0 2px 8px rgba(2,6,23,.04);transition:transform .2s ease,box-shadow .2s ease}
.stat-card:hover{transform:translateY(-2px);box-shadow:0 12px 30px rgba(2,6,23,.08)}
.input-group.input-group-lg .form-control{border-top-right-radius:.6rem;border-bottom-right-radius:.6rem}
.input-group.input-group-lg .input-group-text{border-top-left-radius:.6rem;border-bottom-left-radius:.6rem}
.form-select.form-select-lg{border-radius:.6rem}
.row.d-none {
    display: none;
}
.quick-export-card{border-radius:20px;background:linear-gradient(180deg,#ffffff,#f9fbff);border:1px solid #e6ebf2;box-shadow:0 10px 26px rgba(2,6,23,.06)}
.quick-export-card .form-control,.quick-export-card .form-select{border-radius:12px;border-color:#e1e7ef}
.quick-export-card .form-control:focus,.quick-export-card .form-select:focus{box-shadow:0 0 0 .25rem rgba(13,110,253,.08);border-color:#cfe2ff}
.quick-export-card .btn-lg{border-radius:12px}
.quick-export-card .btn-light.border{background:#fff}
</style>
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        (function() {
            const by = (arr, key) => arr.map(r => r[key] ?? 0);

            const revenueSeries = {!! json_encode($revenue['series'] ?? []) !!};
            const expenseSeries = {!! json_encode($expenses['series'] ?? []) !!};
            const expenseByCat = {!! json_encode($expenses['by_category'] ?? []) !!};
            const cashSeries = {!! json_encode($cash['series'] ?? []) !!};
            const pnlTable = {!! json_encode($pnl['table'] ?? []) !!};

            const revCtx = document.getElementById('revenueReportChart');
            if (revCtx && revenueSeries.length) {
                new Chart(revCtx.getContext('2d'), {
                    type: 'line',
                    data: { labels: by(revenueSeries,'ym'), datasets: [
                        { label: 'Sales', data: by(revenueSeries,'sales'), borderColor:'#0ea5e9', backgroundColor:'rgba(14,165,233,.15)', fill:true, tension:.3 },
                        { label: 'Paid', data: by(revenueSeries,'paid'), borderColor:'#10b981', backgroundColor:'rgba(16,185,129,.15)', fill:true, tension:.3 },
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
            // Quick Export multi-select helpers
            const qeSelect = document.querySelector('select[name="type[]"]');
            const btnAll = document.getElementById('qe-select-all');
            const btnClear = document.getElementById('qe-clear');
            if (qeSelect && btnAll && btnClear) {
                btnAll.addEventListener('click', function() {
                    for (const opt of qeSelect.options) { opt.selected = true; }
                });
                btnClear.addEventListener('click', function() {
                    for (const opt of qeSelect.options) { opt.selected = false; }
                });
            }
        })();
    </script>
@endpush

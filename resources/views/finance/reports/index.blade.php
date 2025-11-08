@extends('layouts.finance_app')

@section('title', 'Finance Reporting')

@section('content')
<!-- Page Header -->
<div class="container-xxl py-3">
    <div class="mb-4">
        <h1 class="mb-1">Finance Reporting</h1>
        <p class="text-muted mb-0">Generate comprehensive financial reports and analytics for business insights.</p>
    </div>

    <!-- Report Categories Grid -->
    <div class="row g-4 mb-4 row-cols-1 row-cols-md-2 row-cols-lg-3">
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

    <!-- Quick Actions -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <h5 class="mb-0">Quick Export</h5>
                <div class="d-flex flex-wrap gap-2">
                    <button type="button" class="btn btn-light border btn-export">
                        <i class="fa-solid fa-file-pdf text-danger"></i>
                        <span class="ms-2">Export as PDF</span>
                    </button>
                    <button type="button" class="btn btn-light border btn-export">
                        <i class="fa-solid fa-file-excel text-success"></i>
                        <span class="ms-2">Export as Excel</span>
                    </button>
                    <button type="button" class="btn btn-light border btn-export">
                        <i class="fa-solid fa-file-csv text-primary"></i>
                        <span class="ms-2">Export as CSV</span>
                    </button>
                    <button type="button" class="btn btn-gradient">
                        <i class="fa-solid fa-print me-2"></i>
                        <span>Print Report</span>
                    </button>
                </div>
            </div>
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
</style>
@endpush

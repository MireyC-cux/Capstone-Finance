@extends('layouts.finance_app')

@section('title', 'Finance Reporting')

@section('content')
<div class="max-w-7xl mx-auto px-6 py-8">
    <!-- Page Header -->
    <div class="mb-8">
        <h1 class="text-4xl font-bold bg-gradient-to-r from-red-600 via-orange-600 to-orange-500 bg-clip-text text-transparent tracking-tight mb-2">Finance Reporting</h1>
        <p class="text-slate-600 text-base">Generate comprehensive financial reports and analytics for business insights.</p>
    </div>

    <!-- Report Categories Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        
        <!-- Financial Statements -->
        <div class="rounded-2xl bg-white border-2 border-slate-200 p-6 shadow-xl hover:shadow-2xl transition-all duration-300">
            <div class="flex items-center gap-4 mb-4">
                <div class="h-16 w-16 rounded-2xl bg-gradient-to-br from-blue-500 to-blue-600 text-white flex items-center justify-center shadow-lg">
                    <i class="fa fa-file-invoice-dollar text-2xl"></i>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-slate-800">Financial Statements</h3>
                    <p class="text-sm text-slate-600">Core financial reports</p>
                </div>
            </div>
            <div class="space-y-3">
                <a href="#" class="flex items-center justify-between px-4 py-3 rounded-xl bg-slate-50 hover:bg-blue-50 border border-slate-200 hover:border-blue-300 transition-all duration-200 group">
                    <span class="font-medium text-slate-700 group-hover:text-blue-700">Income Statement</span>
                    <i class="fa fa-arrow-right text-slate-400 group-hover:text-blue-600"></i>
                </a>
                <a href="#" class="flex items-center justify-between px-4 py-3 rounded-xl bg-slate-50 hover:bg-blue-50 border border-slate-200 hover:border-blue-300 transition-all duration-200 group">
                    <span class="font-medium text-slate-700 group-hover:text-blue-700">Balance Sheet</span>
                    <i class="fa fa-arrow-right text-slate-400 group-hover:text-blue-600"></i>
                </a>
                <a href="#" class="flex items-center justify-between px-4 py-3 rounded-xl bg-slate-50 hover:bg-blue-50 border border-slate-200 hover:border-blue-300 transition-all duration-200 group">
                    <span class="font-medium text-slate-700 group-hover:text-blue-700">Cash Flow Statement</span>
                    <i class="fa fa-arrow-right text-slate-400 group-hover:text-blue-600"></i>
                </a>
            </div>
        </div>

        <!-- Accounts Reports -->
        <div class="rounded-2xl bg-white border-2 border-slate-200 p-6 shadow-xl hover:shadow-2xl transition-all duration-300">
            <div class="flex items-center gap-4 mb-4">
                <div class="h-16 w-16 rounded-2xl bg-gradient-to-br from-emerald-500 to-emerald-600 text-white flex items-center justify-center shadow-lg">
                    <i class="fa fa-receipt text-2xl"></i>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-slate-800">Accounts Reports</h3>
                    <p class="text-sm text-slate-600">AR & AP analytics</p>
                </div>
            </div>
            <div class="space-y-3">
                <a href="{{ route('finance.ar.aging') ?? '#' }}" class="flex items-center justify-between px-4 py-3 rounded-xl bg-slate-50 hover:bg-emerald-50 border border-slate-200 hover:border-emerald-300 transition-all duration-200 group">
                    <span class="font-medium text-slate-700 group-hover:text-emerald-700">AR Aging Report</span>
                    <i class="fa fa-arrow-right text-slate-400 group-hover:text-emerald-600"></i>
                </a>
                <a href="#" class="flex items-center justify-between px-4 py-3 rounded-xl bg-slate-50 hover:bg-emerald-50 border border-slate-200 hover:border-emerald-300 transition-all duration-200 group">
                    <span class="font-medium text-slate-700 group-hover:text-emerald-700">AP Aging Report</span>
                    <i class="fa fa-arrow-right text-slate-400 group-hover:text-emerald-600"></i>
                </a>
                <a href="#" class="flex items-center justify-between px-4 py-3 rounded-xl bg-slate-50 hover:bg-emerald-50 border border-slate-200 hover:border-emerald-300 transition-all duration-200 group">
                    <span class="font-medium text-slate-700 group-hover:text-emerald-700">Collection Summary</span>
                    <i class="fa fa-arrow-right text-slate-400 group-hover:text-emerald-600"></i>
                </a>
            </div>
        </div>

        <!-- Payroll Reports -->
        <div class="rounded-2xl bg-white border-2 border-slate-200 p-6 shadow-xl hover:shadow-2xl transition-all duration-300">
            <div class="flex items-center gap-4 mb-4">
                <div class="h-16 w-16 rounded-2xl bg-gradient-to-br from-indigo-500 to-indigo-600 text-white flex items-center justify-center shadow-lg">
                    <i class="fa fa-money-check-alt text-2xl"></i>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-slate-800">Payroll Reports</h3>
                    <p class="text-sm text-slate-600">Employee compensation</p>
                </div>
            </div>
            <div class="space-y-3">
                <a href="{{ route('finance.payroll.export') ?? '#' }}" class="flex items-center justify-between px-4 py-3 rounded-xl bg-slate-50 hover:bg-indigo-50 border border-slate-200 hover:border-indigo-300 transition-all duration-200 group">
                    <span class="font-medium text-slate-700 group-hover:text-indigo-700">Payroll Register</span>
                    <i class="fa fa-arrow-right text-slate-400 group-hover:text-indigo-600"></i>
                </a>
                <a href="#" class="flex items-center justify-between px-4 py-3 rounded-xl bg-slate-50 hover:bg-indigo-50 border border-slate-200 hover:border-indigo-300 transition-all duration-200 group">
                    <span class="font-medium text-slate-700 group-hover:text-indigo-700">Payroll Summary</span>
                    <i class="fa fa-arrow-right text-slate-400 group-hover:text-indigo-600"></i>
                </a>
                <a href="{{ route('finance.disbursement.index') ?? '#' }}" class="flex items-center justify-between px-4 py-3 rounded-xl bg-slate-50 hover:bg-indigo-50 border border-slate-200 hover:border-indigo-300 transition-all duration-200 group">
                    <span class="font-medium text-slate-700 group-hover:text-indigo-700">Disbursement Report</span>
                    <i class="fa fa-arrow-right text-slate-400 group-hover:text-indigo-600"></i>
                </a>
            </div>
        </div>

        <!-- Inventory Reports -->
        <div class="rounded-2xl bg-white border-2 border-slate-200 p-6 shadow-xl hover:shadow-2xl transition-all duration-300">
            <div class="flex items-center gap-4 mb-4">
                <div class="h-16 w-16 rounded-2xl bg-gradient-to-br from-amber-500 to-amber-600 text-white flex items-center justify-center shadow-lg">
                    <i class="fa fa-boxes text-2xl"></i>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-slate-800">Inventory Reports</h3>
                    <p class="text-sm text-slate-600">Stock & valuation</p>
                </div>
            </div>
            <div class="space-y-3">
                <a href="{{ route('finance.inventory.reports.index') ?? '#' }}" class="flex items-center justify-between px-4 py-3 rounded-xl bg-slate-50 hover:bg-amber-50 border border-slate-200 hover:border-amber-300 transition-all duration-200 group">
                    <span class="font-medium text-slate-700 group-hover:text-amber-700">Stock Valuation</span>
                    <i class="fa fa-arrow-right text-slate-400 group-hover:text-amber-600"></i>
                </a>
                <a href="#" class="flex items-center justify-between px-4 py-3 rounded-xl bg-slate-50 hover:bg-amber-50 border border-slate-200 hover:border-amber-300 transition-all duration-200 group">
                    <span class="font-medium text-slate-700 group-hover:text-amber-700">Stock Movement</span>
                    <i class="fa fa-arrow-right text-slate-400 group-hover:text-amber-600"></i>
                </a>
                <a href="#" class="flex items-center justify-between px-4 py-3 rounded-xl bg-slate-50 hover:bg-amber-50 border border-slate-200 hover:border-amber-300 transition-all duration-200 group">
                    <span class="font-medium text-slate-700 group-hover:text-amber-700">Low Stock Alert</span>
                    <i class="fa fa-arrow-right text-slate-400 group-hover:text-amber-600"></i>
                </a>
            </div>
        </div>

        <!-- Expense Reports -->
        <div class="rounded-2xl bg-white border-2 border-slate-200 p-6 shadow-xl hover:shadow-2xl transition-all duration-300">
            <div class="flex items-center gap-4 mb-4">
                <div class="h-16 w-16 rounded-2xl bg-gradient-to-br from-rose-500 to-rose-600 text-white flex items-center justify-center shadow-lg">
                    <i class="fa fa-chart-pie text-2xl"></i>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-slate-800">Expense Reports</h3>
                    <p class="text-sm text-slate-600">Cost analysis</p>
                </div>
            </div>
            <div class="space-y-3">
                <a href="{{ route('finance.expenses') ?? '#' }}" class="flex items-center justify-between px-4 py-3 rounded-xl bg-slate-50 hover:bg-rose-50 border border-slate-200 hover:border-rose-300 transition-all duration-200 group">
                    <span class="font-medium text-slate-700 group-hover:text-rose-700">Expense Summary</span>
                    <i class="fa fa-arrow-right text-slate-400 group-hover:text-rose-600"></i>
                </a>
                <a href="#" class="flex items-center justify-between px-4 py-3 rounded-xl bg-slate-50 hover:bg-rose-50 border border-slate-200 hover:border-rose-300 transition-all duration-200 group">
                    <span class="font-medium text-slate-700 group-hover:text-rose-700">Category Breakdown</span>
                    <i class="fa fa-arrow-right text-slate-400 group-hover:text-rose-600"></i>
                </a>
                <a href="#" class="flex items-center justify-between px-4 py-3 rounded-xl bg-slate-50 hover:bg-rose-50 border border-slate-200 hover:border-rose-300 transition-all duration-200 group">
                    <span class="font-medium text-slate-700 group-hover:text-rose-700">Budget vs Actual</span>
                    <i class="fa fa-arrow-right text-slate-400 group-hover:text-rose-600"></i>
                </a>
            </div>
        </div>

        <!-- Custom Reports -->
        <div class="rounded-2xl bg-white border-2 border-slate-200 p-6 shadow-xl hover:shadow-2xl transition-all duration-300">
            <div class="flex items-center gap-4 mb-4">
                <div class="h-16 w-16 rounded-2xl bg-gradient-to-br from-purple-500 to-purple-600 text-white flex items-center justify-center shadow-lg">
                    <i class="fa fa-chart-bar text-2xl"></i>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-slate-800">Custom Reports</h3>
                    <p class="text-sm text-slate-600">Build your own</p>
                </div>
            </div>
            <div class="space-y-3">
                <a href="#" class="flex items-center justify-between px-4 py-3 rounded-xl bg-slate-50 hover:bg-purple-50 border border-slate-200 hover:border-purple-300 transition-all duration-200 group">
                    <span class="font-medium text-slate-700 group-hover:text-purple-700">Report Builder</span>
                    <i class="fa fa-arrow-right text-slate-400 group-hover:text-purple-600"></i>
                </a>
                <a href="#" class="flex items-center justify-between px-4 py-3 rounded-xl bg-slate-50 hover:bg-purple-50 border border-slate-200 hover:border-purple-300 transition-all duration-200 group">
                    <span class="font-medium text-slate-700 group-hover:text-purple-700">Saved Reports</span>
                    <i class="fa fa-arrow-right text-slate-400 group-hover:text-purple-600"></i>
                </a>
                <a href="#" class="flex items-center justify-between px-4 py-3 rounded-xl bg-slate-50 hover:bg-purple-50 border border-slate-200 hover:border-purple-300 transition-all duration-200 group">
                    <span class="font-medium text-slate-700 group-hover:text-purple-700">Scheduled Reports</span>
                    <i class="fa fa-arrow-right text-slate-400 group-hover:text-purple-600"></i>
                </a>
            </div>
        </div>

    </div>

    <!-- Quick Actions -->
    <div class="bg-gradient-to-br from-slate-50 to-white rounded-2xl border-2 border-slate-200 p-6 shadow-xl">
        <h2 class="text-xl font-bold text-slate-800 mb-4">Quick Export</h2>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <button class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-white border-2 border-slate-300 hover:border-red-400 hover:bg-red-50 font-medium transition-all duration-200 group">
                <i class="fa fa-file-pdf text-red-600 text-lg"></i>
                <span class="text-slate-700 group-hover:text-red-700">Export as PDF</span>
            </button>
            <button class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-white border-2 border-slate-300 hover:border-emerald-400 hover:bg-emerald-50 font-medium transition-all duration-200 group">
                <i class="fa fa-file-excel text-emerald-600 text-lg"></i>
                <span class="text-slate-700 group-hover:text-emerald-700">Export as Excel</span>
            </button>
            <button class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-white border-2 border-slate-300 hover:border-blue-400 hover:bg-blue-50 font-medium transition-all duration-200 group">
                <i class="fa fa-file-csv text-blue-600 text-lg"></i>
                <span class="text-slate-700 group-hover:text-blue-700">Export as CSV</span>
            </button>
            <button class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-gradient-to-r from-red-600 to-orange-600 hover:from-red-700 hover:to-orange-700 text-white shadow-md font-medium transition-all duration-200">
                <i class="fa fa-print text-lg"></i>
                <span>Print Report</span>
            </button>
        </div>
    </div>
</div>
@endsection

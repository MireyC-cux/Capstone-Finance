<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\DisbursementController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\AccountsReceivableController;
use App\Http\Controllers\ARReportController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\AccountsPayableController;
use App\Http\Controllers\PaymentsMadeController;
use App\Http\Controllers\APReportController;
use App\Http\Controllers\Inventory\InventoryDashboardController;
use App\Http\Controllers\Inventory\InventoryItemController;
use App\Http\Controllers\Inventory\StockInController;
use App\Http\Controllers\Inventory\StockOutController;
use App\Http\Controllers\Inventory\InventoryAdjustmentController;
use App\Http\Controllers\Inventory\InventoryReportController;
use App\Http\Middleware\CheckAuth;
use App\Http\Controllers\AuthTransferController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\CashFlowController;
use App\Http\Controllers\CapitalController;

// =======================================================
// 🔐 AUTH / DEFAULT REDIRECTS
// =======================================================

// When someone opens the root URL, go directly to the Finance Home
Route::get('/', function () {
    return redirect()->route('finance.home');
});

// TEMP DEBUG: verify approve route is reachable and session is available
// (Removed) Approve debug route

// Redirect /login to Finance Home (no auth page yet)
//Route::get('/login', function () {
//    return redirect()->route('finance.home');
//})->name('login');


// =======================================================
// 💼 FINANCE MODULE (Main Section)
// =======================================================

Route::prefix('finance')->name('finance.')->group(function () {

    // -------------------- Dashboard --------------------
    Route::get('/', [FinanceController::class, 'home'])->name('home');

    // -------------------- Billing ----------------------
    Route::prefix('billing')->name('billing.')->group(function () {

        // Show all billings
        Route::get('/', [BillingController::class, 'index'])->name('index');

        // View service request items linked to a billing
        Route::get('/view-items/{id}', [BillingController::class, 'viewItems'])
            ->where('id', '[0-9]+')
            ->name('view-items');

        // Store (create) a new billing record
        Route::post('/store', [BillingController::class, 'store'])->name('store');

        // Bulk create billing records for multiple service requests
        Route::post('/bulk-store', [BillingController::class, 'bulkStore'])->name('bulk-store');

        // Show printable billing slip
        Route::get('/slip/{id}', [BillingController::class, 'showSlip'])
            ->where('id', '[0-9]+')
            ->name('slip');

        // Export billing slip as PDF
        Route::get('/slip/{id}/pdf', [BillingController::class, 'exportSlipPdf'])
            ->where('id', '[0-9]+')
            ->name('slip.pdf');

        // Optional manual invoice generation (if automation is off)
        Route::post('/generate-invoice/{billing}', [BillingController::class, 'generateInvoice'])
            ->where('billing', '[0-9]+')
            ->name('generate-invoice');
        
        // (Removed) Billing approvals routes
    });

    // -------------------- Accounts ---------------------
    Route::get('/accounts-receivable', [FinanceController::class, 'accountsReceivable'])
        ->name('accounts-receivable');
    Route::get('/accounts-payable', [AccountsPayableController::class, 'index'])
        ->name('accounts-payable');

    // -------------------- Payroll & Expenses -----------
    // Payroll Dashboard
    Route::get('/payroll', [PayrollController::class, 'dashboard'])->name('payroll');
    // Payroll actions
    Route::post('/payroll/generate', [PayrollController::class, 'generatePayroll'])->name('payroll.generate');
    Route::post('/payroll/{payroll}/approve', [PayrollController::class, 'approvePayroll'])->where('payroll','[0-9]+')->name('payroll.approve');
    Route::get('/payroll/{payroll}/payslip', [PayrollController::class, 'downloadPayslip'])->where('payroll','[0-9]+')->name('payroll.payslip');
    // Alias route following spec
    Route::get('/payroll/download-payslip/{payroll}', [PayrollController::class, 'downloadPayslip'])->where('payroll','[0-9]+')->name('payroll.download-payslip');
    // Export payroll table as PDF
    Route::get('/payroll/export', [PayrollController::class, 'exportTable'])->name('payroll.export');
    // Approvals page
    Route::get('/payroll/approvals', [PayrollController::class, 'approvals'])->name('payroll.approvals');
    // Disbursement action
    Route::get('/disbursement', [DisbursementController::class, 'index'])->name('disbursement.index');
    Route::post('/disbursement/record', [DisbursementController::class, 'disburseSalary'])->name('disbursement.record');
    Route::get('/disbursement/export', [DisbursementController::class, 'exportTable'])->name('disbursement.export');
        
    // Cash Flow Dashboard & Exports
    Route::get('/cashflow', [CashFlowController::class, 'index'])->name('cashflow');
    Route::get('/cashflow/export/pdf', [CashFlowController::class, 'exportPdf'])->name('cashflow.export.pdf');
    Route::get('/cashflow/export/csv', [CashFlowController::class, 'exportCsv'])->name('cashflow.export.csv');

    // Expenses
    Route::get('/expenses', [ExpenseController::class, 'index'])->name('expenses');
    Route::post('/expenses/store', [ExpenseController::class, 'store'])->name('expenses.store');

    // Capital Management
    Route::post('/capital/set', [CapitalController::class, 'setCapital'])->name('capital.set');
    Route::post('/capital/inject', [CapitalController::class, 'inject'])->name('capital.inject');
    Route::post('/capital/withdraw', [CapitalController::class, 'withdraw'])->name('capital.withdraw');

    // -------------------- Inventory & Reports ----------
    Route::prefix('inventory')->name('inventory.')->group(function () {
        Route::get('/', [InventoryDashboardController::class, 'index'])->name('dashboard');

        Route::resource('items', InventoryItemController::class)->except(['create','show','edit']);

        Route::get('stock-in', [StockInController::class, 'index'])->name('stock-in.index');
        Route::post('stock-in', [StockInController::class, 'store'])->name('stock-in.store');

        Route::get('stock-out', [StockOutController::class, 'index'])->name('stock-out.index');
        Route::post('stock-out', [StockOutController::class, 'store'])->name('stock-out.store');

        Route::get('adjustments', [InventoryAdjustmentController::class, 'index'])->name('adjustments.index');
        Route::post('adjustments', [InventoryAdjustmentController::class, 'store'])->name('adjustments.store');

        Route::get('reports', [InventoryReportController::class, 'index'])->name('reports.index');
    });
    Route::get('/reports', [FinanceController::class, 'reports'])->name('reports');

    // AR Aging (finance-prefixed alias)
    Route::get('/accounts-receivable/aging-report', [ARReportController::class, 'agingReport'])
        ->name('ar.aging');
});


// =======================================================
// 🧾 INVOICES MODULE
// =======================================================

Route::prefix('invoices')->name('invoices.')->group(function () {

    // Show all invoices
    Route::get('/', [InvoiceController::class, 'index'])->name('index');

    // Create invoice manually (if needed)
    Route::get('/create/{billing_id}', [InvoiceController::class, 'create'])
        ->where('billing_id', '[0-9]+')
        ->name('create');

    // Store new invoice
    Route::post('/store', [InvoiceController::class, 'store'])->name('store');

    // Show invoice details
    Route::get('/{id}', [InvoiceController::class, 'show'])
        ->where('id', '[0-9]+')
        ->name('show');

    // Export invoice as PDF
    Route::get('/{id}/pdf', [InvoiceController::class, 'exportPdf'])
        ->where('id', '[0-9]+')
        ->name('pdf');
});


// =======================================================
// 💵 PAYMENTS MODULE
// =======================================================

Route::prefix('payments')->name('payments.')->group(function () {

    // Record a new payment
    Route::post('/record', [PaymentController::class, 'store'])->name('store');

    // ✅ GCash routes
    Route::post('/gcash', [PaymentController::class, 'payWithGCash'])->name('gcash');
    Route::post('/gcash/source', [PaymentController::class, 'createGCashSource'])->name('gcash.source');
    Route::get('/gcash/success', [PaymentController::class, 'gcashSuccess'])->name('gcash.success');
    Route::get('/gcash/failed', [PaymentController::class, 'gcashFailed'])->name('gcash.failed');

    // View payment history
    Route::get('/history', [PaymentController::class, 'history'])->name('history');
});


// =======================================================
// 📘 ACCOUNTS RECEIVABLE
// =======================================================

Route::resource('accounts-receivable', AccountsReceivableController::class);
Route::get('accounts-receivable/totals', [AccountsReceivableController::class, 'totals'])->name('accounts-receivable.totals');
Route::post('accounts-receivable/{id}/payment', [PaymentController::class, 'store'])->name('payments.store');
Route::get('accounts-receivable/aging-report', [ARReportController::class, 'agingReport'])->name('ar.aging');

// =======================================================
// 📗 ACCOUNTS PAYABLE & PURCHASE ORDERS
// =======================================================

Route::resource('purchase-orders', PurchaseOrderController::class);
Route::resource('accounts-payable', AccountsPayableController::class);
Route::resource('payments-made', PaymentsMadeController::class)->only(['index','store']);
Route::get('reports/ap-aging', [APReportController::class, 'aging'])->name('reports.ap-aging');
Route::post('purchase-orders/{purchase_order}/approve', [PurchaseOrderController::class, 'approve'])->name('purchase-orders.approve');
Route::post('purchase-orders/{purchase_order}/reject', [PurchaseOrderController::class, 'reject'])->name('purchase-orders.reject');
Route::post('accounts-payable/mark-overdues', [AccountsPayableController::class, 'markOverdues'])->name('accounts-payable.mark-overdues');

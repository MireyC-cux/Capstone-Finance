<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\FinanceController;

Route::get('/', function () {
    return redirect()->route('finance.home');
});

Route::prefix('finance')->name('finance.')->group(function () {
    Route::get('/', [FinanceController::class, 'home'])->name('home');
    
    // Billing Routes
    Route::prefix('billing')->name('billing.')->group(function() {
        Route::get('/', [BillingController::class, 'index'])->name('index');
        Route::get('/view-items/{id}', [BillingController::class, 'viewItems'])
            ->where('id', '[0-9]+')
            ->name('view-items');
        Route::post('/store', [BillingController::class, 'store'])->name('store');
        Route::get('/slip/{id}', [BillingController::class, 'showSlip'])->name('slip');
    });
    
    Route::get('/accounts-receivable', [FinanceController::class, 'accountsReceivable'])->name('accounts-receivable');
    Route::get('/accounts-payable', [FinanceController::class, 'accountsPayable'])->name('accounts-payable');
    Route::get('/payroll', [FinanceController::class, 'payroll'])->name('payroll');
    Route::get('/expenses', [FinanceController::class, 'expenses'])->name('expenses');
    Route::get('/reports', [FinanceController::class, 'reports'])->name('reports');
    Route::get('/inventory', [FinanceController::class, 'inventory'])->name('inventory');
});


Route::prefix('invoices')->group(function () {
    Route::get('/', [InvoiceController::class, 'index'])->name('invoices.index');
    Route::get('/create/{billing_id}', [InvoiceController::class, 'create'])->name('invoices.create');
    Route::post('/store', [InvoiceController::class, 'store'])->name('invoices.store');
    Route::get('/{id}', [InvoiceController::class, 'show'])->name('invoices.show');
});

Route::prefix('payments')->group(function () {
    Route::post('/record', [PaymentController::class, 'store'])->name('payments.store');
});
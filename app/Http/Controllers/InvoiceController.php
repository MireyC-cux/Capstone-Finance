<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Billing;
use App\Models\ServiceRequest;
use App\Models\Customer;
use App\Models\Address;
use App\Models\Supplier;
use App\Http\Requests\StoreInvoiceRequest;
use Illuminate\Http\Request;


class InvoiceController extends Controller
{
    public function create($billing_id) {
        $billing = Billing::with('customer')->findOrFail($billing_id);
        return view('invoices.create', compact('billing'));
    }
    
    public function store(Request $request) {
        $invoice = Invoice::create([
            'billing_id' => $request->billing_id,
            'invoice_number' => 'INV-' . now()->format('YmdHis'),
            'invoice_date' => now(),
            'due_date' => $request->due_date,
            'amount' => $request->amount,
            'status' => 'Unpaid',
        ]);
    
        AccountsReceivable::create([
            'customer_id' => $request->customer_id,
            'service_request_id' => $request->service_request_id,
            'invoice_number' => $invoice->invoice_number,
            'invoice_date' => now(),
            'due_date' => $request->due_date,
            'total_amount' => $request->amount,
            'payment_terms' => $request->payment_terms,
        ]);
    
        return redirect()->route('invoices.show', $invoice->invoice_id);
    }
}

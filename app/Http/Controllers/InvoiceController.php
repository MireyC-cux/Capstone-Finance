<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\Billing;
use App\Models\Invoice;
// use App\Models\PaymentHistory; // not used in index now
use App\Models\AccountsReceivable; // still used in store()

class InvoiceController extends Controller
{
    public function index()
    {
        // Show Billings with status = Billed
        $billings = Billing::with(['serviceRequest.customer'])
            ->where('status', 'Billed')
            ->orderByDesc('billing_id')
            ->paginate(20);
        return view('finance.invoices.index', compact('billings'));
    }

    public function create($billing_id)
    {
        $billing = Billing::with('serviceRequest.customer')->findOrFail($billing_id);
        return view('finance.invoices.create', compact('billing'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'billing_id' => 'required|integer|exists:billings,billing_id',
            'invoice_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:invoice_date',
        ]);

        return DB::transaction(function () use ($data) {
            $billing = Billing::with('invoice')->findOrFail($data['billing_id']);
            if ($billing->invoice) {
                return redirect()->route('invoices.show', $billing->invoice->invoice_id);
            }

            $invoiceNumber = $this->nextInvoiceNumber();

            $ar = AccountsReceivable::create([
                'customer_id' => $billing->customer_id,
                'service_request_id' => $billing->service_request_id,
                'invoice_number' => $invoiceNumber,
                'invoice_date' => Carbon::parse($data['invoice_date'])->toDateString(),
                'due_date' => Carbon::parse($data['due_date'])->toDateString(),
                'total_amount' => $billing->total_amount,
                'amount_paid' => 0,
                'status' => 'Unpaid',
            ]);

            $invoice = Invoice::create([
                'billing_id' => $billing->billing_id,
                'ar_id' => $ar->ar_id,
                'invoice_number' => $invoiceNumber,
                'invoice_date' => Carbon::parse($data['invoice_date'])->toDateString(),
                'due_date' => Carbon::parse($data['due_date'])->toDateString(),
                'amount' => $billing->total_amount,
                'status' => 'Unpaid',
            ]);

            return redirect()->route('invoices.show', $invoice->invoice_id)
                ->with('success', 'Invoice generated successfully.');
        });
    }

    public function show($id)
    {
        $invoice = Invoice::with(['billing.serviceRequest.customer', 'accountsReceivable'])->findOrFail($id);
        return view('finance.invoices.show', compact('invoice'));
    }

    public function exportPdf($id)
    {
        $invoice = Invoice::with(['billing.serviceRequest.customer', 'accountsReceivable'])->findOrFail($id);
        if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('finance.invoices.show', compact('invoice'));
            $file = 'Invoice_'.$invoice->invoice_number.'.pdf';
            return $pdf->download($file);
        }
        return view('finance.invoices.show', compact('invoice'));
    }

    protected function nextInvoiceNumber(): string
    {
        $prefix = 'INV-'.Carbon::now()->format('Ymd').'-';
        $last = Invoice::whereDate('created_at', Carbon::today())
            ->orderByDesc('invoice_id')->first();
        $seq = $last ? ((int)substr($last->invoice_number, -4)) + 1 : 1;
        return $prefix . str_pad((string)$seq, 4, '0', STR_PAD_LEFT);
    }
}

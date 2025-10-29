<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\ServiceRequest;
use App\Models\ServiceRequestItem;
use App\Models\ServiceRequestItemExtra;
use App\Models\Billing;
use App\Models\Invoice;
use App\Models\AccountsReceivable;
use App\Models\PaymentHistory;

class BillingController extends Controller
{
    // Dashboard: list completed, unbilled SRs with filters
    public function index(Request $request)
    {
        $query = ServiceRequestItem::with(['serviceRequest.customer', 'service']);

        // SR-level filters: only SRs without any billing, order status: Pending, quotation: Approved (if column exists)
        $query->whereHas('serviceRequest', function ($q) {
            $q->where('order_status', 'Pending');
            if (Schema::hasColumn('service_requests', 'quotation_status')) {
                $q->where('quotation_status', 'Approved');
            }
            $q->doesntHave('billings');
        });

        if ($request->filled('customer')) {
            $customer = $request->string('customer');
            $query->whereHas('serviceRequest.customer', function ($q) use ($customer) {
                $q->where('full_name', 'like', "%{$customer}%");
            });
        }
        if ($request->filled('sr_number')) {
            $srn = $request->string('sr_number');
            $query->whereHas('serviceRequest', function ($q) use ($srn) {
                $q->where('service_request_number', 'like', "%{$srn}%");
            });
        }

        $items = $query->get()->groupBy('service_request_id');

        return view('finance.billing.index', [
            'groups' => $items,
            'filters' => $request->only(['customer', 'sr_number']),
        ]);
    }

    // View items for a service request (for modal)
    public function viewItems($id)
    {
        $sr = ServiceRequest::with(['customer', 'items.service'])->findOrFail($id);

        foreach ($sr->items as $item) {
            $item->extras = ServiceRequestItemExtra::where('item_id', $item->item_id)->get();
        }

        return response()->json($sr);
    }

    // Create a billing for one service request
    public function store(Request $request)
    {
        $data = $request->validate([
            'service_request_id' => 'required|integer|exists:service_requests,service_request_id',
            'billing_date' => 'required|date',
            'generate_invoice' => 'sometimes|boolean',
        ]);

        return DB::transaction(function () use ($data) {
            $sr = ServiceRequest::with(['items'])->findOrFail($data['service_request_id']);
            $items = $sr->items;
            if ($items->isEmpty()) {
                return response()->json(['message' => 'No completed items.'], 422);
            }

            $billingData = [
                'service_request_id' => $sr->service_request_id,
                'customer_id' => $sr->customer_id ?? ($sr->customer->customer_id ?? null),
                'billing_date' => $data['billing_date'],
                'status' => 'Billed',
            ];

            if (Schema::hasColumn('billings', 'generate_invoice_after_approval')) {
                $billingData['generate_invoice_after_approval'] = !empty($data['generate_invoice']);
            }
            if (Schema::hasColumn('billings', 'meta')) {
                $billingData['meta'] = null;
            }

            $billing = Billing::create($billingData);

            // Also create Accounts Receivable entry so it appears in AR and invoice history
            $subtotal = 0; $lineDiscount = 0; $lineTax = 0;
            foreach ($items as $item) {
                $qty = (int)($item->quantity ?? 1);
                $unit = (float)($item->unit_price ?? 0);
                $lineDiscount += (float)($item->discount ?? 0);
                $lineTax += (float)($item->tax ?? 0);
                $extras = ServiceRequestItemExtra::where('item_id', $item->item_id)->get();
                $extraSum = $extras->sum(fn($e) => (float)$e->qty * (float)$e->price);
                $subtotal += ($qty * $unit) + $extraSum;
            }
            $grandDiscount = Schema::hasColumn('service_requests', 'overall_discount') ? (float)($sr->overall_discount ?? 0) : 0.0;
            $grandTax = Schema::hasColumn('service_requests', 'overall_tax_amount') ? (float)($sr->overall_tax_amount ?? 0) : 0.0;
            $taxTotal = $lineTax + $grandTax;
            $totalAmount = round($subtotal - ($lineDiscount + $grandDiscount) + $taxTotal, 2);

            $invoiceNumber = $this->nextInvoiceNumber();
            $invoiceDate = Carbon::parse($data['billing_date'])->toDateString();
            $dueDate = Carbon::parse($data['billing_date'])->addDays(15)->toDateString();

            $ar = AccountsReceivable::create([
                'customer_id' => $sr->customer_id ?? ($sr->customer->customer_id ?? null),
                'service_request_id' => $sr->service_request_id,
                'invoice_number' => $invoiceNumber,
                'invoice_date' => $invoiceDate,
                'due_date' => $dueDate,
                'total_amount' => $totalAmount,
                'amount_paid' => 0,
                'status' => 'Unpaid',
            ]);

            // Also create an Invoice record with 15-day due date
            $invDue = Carbon::parse($data['billing_date'])->addDays(15)->toDateString();
            $invoice = Invoice::create([
                'billing_id' => $billing->billing_id,
                'ar_id' => $ar->ar_id,
                'invoice_number' => $invoiceNumber,
                'invoice_date' => $invoiceDate,
                'due_date' => $invDue,
                'amount' => $totalAmount,
                'status' => 'Unpaid',
            ]);

            // Record an entry in payment_history to reflect this billed SR
            PaymentHistory::create([
                'billing_id' => $billing->billing_id,
                'service_request_id' => $sr->service_request_id,
                'payment_date' => $invoiceDate,
                'due_date' => $invDue,
                'type_of_payment' => 'Billed',
                'amount' => $totalAmount,
                'status' => 'Unpaid',
            ]);

            return response()->json([
                'message' => 'Billing created.',
                'billing_id' => $billing->billing_id,
            ]);
        });
    }

    // Bulk bill generation for multiple SR IDs
    public function bulkStore(Request $request)
    {
        $data = $request->validate([
            'service_request_ids' => 'required|array',
            'service_request_ids.*' => 'integer|exists:service_requests,service_request_id',
            'billing_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:billing_date',
            'generate_invoice' => 'sometimes|boolean',
        ]);

        $results = [];
        foreach ($data['service_request_ids'] as $srId) {
            $req = new Request([
                'service_request_id' => $srId,
                'billing_date' => $data['billing_date'],
                'due_date' => $data['due_date'],
                'generate_invoice' => $data['generate_invoice'] ?? false,
            ]);
            $resp = $this->store($req);
            $results[] = json_decode($resp->getContent(), true);
        }
        return response()->json(['results' => $results]);
    }

    // Manual invoice generation endpoint for a billing
    public function generateInvoice(Billing $billing)
    {
        $invoice = $this->createInvoiceFromBilling($billing);
        return response()->json([
            'message' => 'Invoice generated successfully.',
            'invoice_id' => $invoice->invoice_id,
        ]);
    }

    // Printable billing slip (placeholder)
    public function showSlip($id)
    {
        $billing = Billing::with(['serviceRequest.customer', 'invoice'])->findOrFail($id);
        $items = ServiceRequestItem::with('service')
            ->where('service_request_id', $billing->service_request_id)
            ->get()
            ->map(function($it){
                $it->extras = ServiceRequestItemExtra::where('item_id', $it->item_id)->get();
                return $it;
            });
        return view('finance.billing.slip', compact('billing','items'));
    }

    // Export billing slip as PDF
    public function exportSlipPdf($id)
    {
        $billing = Billing::with(['serviceRequest.customer', 'invoice'])->findOrFail($id);
        $items = ServiceRequestItem::with('service')
            ->where('service_request_id', $billing->service_request_id)
            ->get()
            ->map(function($it){
                $it->extras = ServiceRequestItemExtra::where('item_id', $it->item_id)->get();
                return $it;
            });
        $forPdf = true;
        $signatureDataUrl = null;
        $sigPath = storage_path('app/public/esignature.png');
        if (is_file($sigPath)) {
            $signatureDataUrl = 'data:image/png;base64,'.base64_encode(file_get_contents($sigPath));
        }
        // Use DomPDF if available
        if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
            ])->loadView('finance.billing.slip', compact('billing','items','forPdf','signatureDataUrl'))
              ->setPaper('A4', 'portrait');
            $file = 'BillingSlip_'.$billing->billing_id.'.pdf';
            return $pdf->stream($file);
        }
        // Fallback: simple HTML (printable)
        return view('finance.billing.slip', compact('billing','items','forPdf','signatureDataUrl'));
    }

    // Approval form page (separate screen)
    // (Removed) Billing approval screens and actions

    // Helper: create invoice + AR from billing
    protected function createInvoiceFromBilling(Billing $billing)
    {
        // Avoid duplicate invoices
        if ($billing->invoice) {
            return $billing->invoice;
        }

        // Compute totals from items + extras for this service request
        $items = ServiceRequestItem::where('service_request_id', $billing->service_request_id)->get();
        $subtotal = 0; $lineDiscount = 0; $lineTax = 0;
        foreach ($items as $item) {
            $qty = (int)($item->quantity ?? 1);
            $unit = (float)($item->unit_price ?? 0);
            $lineDiscount += (float)($item->discount ?? 0);
            $lineTax += (float)($item->tax ?? 0);
            $extras = ServiceRequestItemExtra::where('item_id', $item->item_id)->get();
            $extraSum = $extras->sum(fn($e) => (float)$e->qty * (float)$e->price);
            $subtotal += ($qty * $unit) + $extraSum;
        }
        $sr = ServiceRequest::find($billing->service_request_id);
        $grandDiscount = Schema::hasColumn('service_requests', 'overall_discount') ? (float)($sr->overall_discount ?? 0) : 0.0;
        $grandTax = Schema::hasColumn('service_requests', 'overall_tax_amount') ? (float)($sr->overall_tax_amount ?? 0) : 0.0;
        $taxTotal = $lineTax + $grandTax;
        $totalAmount = round($subtotal - ($lineDiscount + $grandDiscount) + $taxTotal, 2);

        $invoiceNumber = $this->nextInvoiceNumber();
        $invoiceDate = Carbon::parse($billing->billing_date)->toDateString();
        $dueDate = Carbon::parse($billing->billing_date)->addDays(15)->toDateString();

        $ar = AccountsReceivable::create([
            'customer_id' => $billing->customer_id,
            'service_request_id' => $billing->service_request_id,
            'invoice_number' => $invoiceNumber,
            'invoice_date' => $invoiceDate,
            'due_date' => $dueDate,
            'total_amount' => $totalAmount,
            'amount_paid' => 0,
            'status' => 'Unpaid',
        ]);

        $invoice = Invoice::create([
            'billing_id' => $billing->billing_id,
            'ar_id' => $ar->ar_id,
            'invoice_number' => $invoiceNumber,
            'invoice_date' => $invoiceDate,
            'due_date' => $dueDate,
            'amount' => $totalAmount,
            'status' => 'Unpaid',
        ]);

        return $invoice;
    }

    // Very simple sequential number; consider using your invoice_sequence table if needed
    protected function nextInvoiceNumber(): string
    {
        $today = Carbon::today();
        $prefix = 'INV-'.$today->format('Ymd').'-';
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('invoice_sequences')) {
                $year = (int)$today->format('Y');
                // Use a short transaction lock to safely increment the yearly counter
                $row = DB::table('invoice_sequences')->where('year', $year)->lockForUpdate()->first();
                if ($row) {
                    DB::table('invoice_sequences')->where('id', $row->id)->update([
                        'counter' => $row->counter + 1,
                        'updated_at' => now(),
                    ]);
                    $seq = (int)$row->counter + 1;
                } else {
                    DB::table('invoice_sequences')->insert([
                        'year' => $year,
                        'counter' => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $seq = 1;
                }
                return $prefix . str_pad((string)$seq, 4, '0', STR_PAD_LEFT);
            }
        } catch (\Throwable $e) {
            // Fallback below
        }
        static $fallbackSeq = 0; $fallbackSeq++;
        return $prefix . str_pad((string)$fallbackSeq, 4, '0', STR_PAD_LEFT);
    }
}

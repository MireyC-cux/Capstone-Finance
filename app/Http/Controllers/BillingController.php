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

class BillingController extends Controller
{
    // Dashboard: list completed, unbilled SRs with filters
    public function index(Request $request)
    {
        $query = ServiceRequestItem::with(['serviceRequest.customer', 'service'])
            ->where('status', 'Completed');

        if (Schema::hasColumn('service_request_items', 'billed')) {
            $query->where(function ($q) {
                $q->whereNull('billed')->orWhere('billed', false);
            });
        }

        // Only include service requests with Approved quotation
        $query->whereHas('serviceRequest', function ($q) {
            if (Schema::hasColumn('service_requests', 'quotation_status')) {
                $q->where('quotation_status', 'Approved');
            }
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
        // Removed date range filtering as requested

        $items = $query->get()->groupBy('service_request_id');

        return view('finance.billing.index', [
            'groups' => $items,
            'filters' => $request->only(['customer', 'sr_number']),
        ]);
    }

    // View items for a service request (for modal)
    public function viewItems($id)
    {
        $sr = ServiceRequest::with(['customer', 'items.service', 'items' => function ($q) {
            $q->where('status', 'Completed')->where(function ($qq) {
                $qq->whereNull('billed')->orWhere('billed', false);
            });
        }])->findOrFail($id);

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
            'due_date' => 'required|date|after_or_equal:billing_date',
            'generate_invoice' => 'sometimes|boolean',
        ]);

        return DB::transaction(function () use ($data) {
            $sr = ServiceRequest::with(['items' => function($q){ $q->where('status','Completed'); }])->findOrFail($data['service_request_id']);
            $items = $sr->items()->where(function($q){ $q->whereNull('billed')->orWhere('billed', false); })->get();
            if ($items->isEmpty()) {
                return response()->json(['message' => 'No completed unbilled items.'], 422);
            }

            $subtotal = 0; $discount = 0; $tax = 0;
            foreach ($items as $item) {
                $line = ($item->quantity ?? 1) * (float)$item->unit_price;
                $lineDiscount = (float)($item->discount ?? 0);
                $lineTax = (float)($item->tax ?? 0);
                $extras = ServiceRequestItemExtra::where('item_id', $item->item_id)->get();
                $extraSum = $extras->sum(fn($e) => (float)$e->qty * (float)$e->price);
                $subtotal += $line + $extraSum;
                $discount += $lineDiscount;
                $tax += $lineTax;
            }

            $total = round($subtotal - $discount + $tax, 2);

            $billingData = [
                'service_request_id' => $sr->service_request_id,
                'customer_id' => $sr->customer_id ?? ($sr->customer->customer_id ?? null),
                'billing_date' => $data['billing_date'],
                'due_date' => $data['due_date'],
                'discount' => 0,
                'tax' => 0,
                'total_amount' => $total,
            ];

            if (Schema::hasColumn('billings', 'approval_status')) {
                $billingData['approval_status'] = 'Pending';
            }
            if (Schema::hasColumn('billings', 'generate_invoice_after_approval')) {
                $billingData['generate_invoice_after_approval'] = !empty($data['generate_invoice']);
            }

            $billing = Billing::create($billingData);

            $message = Schema::hasColumn('billings','approval_status') ? 'Billing submitted for admin approval.' : 'Billing created.';
            return response()->json([
                'message' => $message,
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

        $now = Carbon::now();
        $invoiceNumber = $this->nextInvoiceNumber();

        $ar = AccountsReceivable::create([
            'customer_id' => $billing->customer_id,
            'service_request_id' => $billing->service_request_id,
            'invoice_number' => $invoiceNumber,
            'invoice_date' => $now->toDateString(),
            'due_date' => Carbon::parse($billing->due_date)->toDateString(),
            'total_amount' => $billing->total_amount,
            'amount_paid' => 0,
            'status' => 'Unpaid',
        ]);

        $invoice = Invoice::create([
            'billing_id' => $billing->billing_id,
            'ar_id' => $ar->ar_id,
            'invoice_number' => $invoiceNumber,
            'invoice_date' => $now->toDateString(),
            'due_date' => Carbon::parse($billing->due_date)->toDateString(),
            'amount' => $billing->total_amount,
            'status' => 'Unpaid',
        ]);

        return $invoice;
    }

    // Very simple sequential number; consider using your invoice_sequence table if needed
    protected function nextInvoiceNumber(): string
    {
        $prefix = 'INV-'.Carbon::now()->format('Ymd').'-';
        $last = Invoice::whereDate('created_at', Carbon::today())
            ->orderByDesc('invoice_id')->first();
        $seq = $last ? ((int)substr($last->invoice_number, -4)) + 1 : 1;
        return $prefix . str_pad((string)$seq, 4, '0', STR_PAD_LEFT);
    }
}

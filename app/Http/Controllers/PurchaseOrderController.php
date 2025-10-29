<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\ServiceRequest;
use App\Models\Supplier;
use App\Models\AccountsPayable;
use App\Models\CashFlow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\ActivityLog;

class PurchaseOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = PurchaseOrder::with(['supplier', 'serviceRequest','accountsPayable'])->orderByDesc('purchase_order_id');

        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }
        if ($request->filled('po_number')) {
            $query->where('po_number', 'like', '%'.$request->get('po_number').'%');
        }
        if ($request->filled('from')) {
            $query->whereDate('po_date', '>=', $request->get('from'));
        }
        if ($request->filled('to')) {
            $query->whereDate('po_date', '<=', $request->get('to'));
        }
        if ($request->filled('supplier')) {
            $supplier = $request->get('supplier');
            $query->whereHas('supplier', function($q) use ($supplier) {
                $q->where('supplier_name', 'like', '%'.$supplier.'%');
            });
        }

        $pos = $query->paginate(25)->appends($request->query());
        return view('finance.purchase_orders.index', compact('pos'));
    }

    public function create(Request $request)
    {
        $suppliers = Supplier::orderBy('supplier_name')->get();
        $serviceRequest = null;
        if ($request->filled('service_request_id')) {
            $serviceRequest = ServiceRequest::with('items')->find($request->get('service_request_id'));
        }

        // Installation SR items with aircon type for selection
        $installationItems = \App\Models\ServiceRequestItem::with(['serviceRequest', 'airconType', 'service'])
            ->whereHas('service', function ($q) {
                $q->where('service_type', 'Installation');
            })
            ->orderByDesc('item_id')
            ->paginate(10);

        return view('finance.purchase_orders.create', compact('suppliers', 'serviceRequest', 'installationItems'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'supplier_id' => 'required|exists:suppliers,supplier_id',
            'service_request_id' => 'nullable|exists:service_requests,service_request_id',
            'po_date' => 'required|date',
            'remarks' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.description' => 'nullable|string',
            'items.*.item_id' => 'nullable|exists:service_request_items,item_id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        return DB::transaction(function () use ($data) {
            $poNumber = $this->generatePoNumber();
            $total = 0;
            foreach ($data['items'] as $i) {
                $total += ((int)$i['quantity']) * ((float)$i['unit_price']);
            }
            $po = PurchaseOrder::create([
                'supplier_id' => $data['supplier_id'],
                'service_request_id' => $data['service_request_id'] ?? null,
                'po_number' => $poNumber,
                'po_date' => Carbon::parse($data['po_date'])->toDateString(),
                'status' => 'Pending',
                'total_amount' => number_format($total, 2, '.', ''),
                'created_by' => Auth::id() ?? null,
                'remarks' => $data['remarks'] ?? null,
            ]);

            foreach ($data['items'] as $i) {
                PurchaseOrderItem::create([
                    'purchase_order_id' => $po->purchase_order_id,
                    'item_id' => $i['item_id'] ?? null,
                    'description' => $i['description'] ?? null,
                    'quantity' => (int)$i['quantity'],
                    'unit_price' => number_format((float)$i['unit_price'], 2, '.', ''),
                ]);
            }

            return redirect()->route('purchase-orders.show', $po->purchase_order_id)->with('success', 'PO created and submitted for approval.');
        });
    }

    public function show(PurchaseOrder $purchase_order)
    {
        $purchase_order->load(['supplier', 'serviceRequest', 'items']);
        return view('finance.purchase_orders.show', ['po' => $purchase_order]);
    }

    public function summary($id)
    {
        $po = PurchaseOrder::with(['supplier','accountsPayable'])->findOrFail($id);
        $ap = $po->accountsPayable;
        $total = (float)$po->total_amount;
        $paid = $ap ? (float)$ap->amount_paid : 0.0;
        $out = max(0.0, $total - $paid);
        return response()->json([
            'id' => $po->purchase_order_id,
            'po_number' => $po->po_number,
            'supplier' => $po->supplier->supplier_name ?? '—',
            'po_date' => Carbon::parse($po->po_date)->format('Y-m-d'),
            'payment_status' => $ap ? $ap->status : 'Unpaid',
            'total' => $total,
            'paid' => $paid,
            'outstanding' => $out,
        ]);
    }

    public function recordPayment(Request $request, $id)
    {
        $data = $request->validate([
            'payment_date' => 'required|date',
            'payment_method' => 'required|string|in:Cash,GCash,Bank Transfer,Check',
            'payment_type' => 'required|string|in:Full,Partial',
            'reference_number' => 'nullable|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'or_file' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp,pdf',
        ]);

        return DB::transaction(function () use ($data, $id, $request) {
            $po = PurchaseOrder::with('accountsPayable')->lockForUpdate()->findOrFail($id);
            $ap = $po->accountsPayable; // may be null if not approved

            $total = (float)$po->total_amount;
            $paid = $ap ? (float)$ap->amount_paid : 0.0;
            $outstanding = max(0.0, $total - $paid);

            // Enforce full/partial constraints
            if (($data['payment_type'] ?? 'Full') === 'Full') {
                if (abs(((float)$data['amount']) - $outstanding) > 0.009) {
                    return back()->with('error', 'Full payment must match the outstanding balance.');
                }
            } else {
                if (!(((float)$data['amount']) > 0 && ((float)$data['amount']) < $outstanding)) {
                    return back()->with('error', 'Partial payment must be greater than 0 and less than the outstanding balance.');
                }
            }

            // Require reference + OR for non-cash
            $isCash = strtolower($data['payment_method']) === 'cash';
            if (!$isCash && empty($data['reference_number'])) {
                return back()->with('error', 'Reference number is required for non-cash payments.');
            }

            $orPath = null;
            if (!$isCash) {
                if (!$request->hasFile('or_file')) {
                    return back()->with('error', 'Official Receipt (image/PDF) is required for non-cash payments.');
                }
                $orPath = $request->file('or_file')->store('or_uploads', 'public');
            }

            // Update Accounts Payable if exists
            if ($ap) {
                $ap->amount_paid = number_format(((float)$ap->amount_paid + (float)$data['amount']), 2, '.', '');
                // derive AP status similar to existing PaymentsMadeController
                $ap->status = $this->deriveApStatus($ap->total_amount, $ap->amount_paid, $ap->due_date);
                $ap->save();
            }

            // Write to Cash Flow as Outflow (source_id = PO id)
            $cf = [
                'transaction_type' => 'Outflow',
                'source_type' => 'Supplier Payment',
                'source_id' => (int)$po->purchase_order_id,
                'amount' => number_format((float)$data['amount'], 2, '.', ''),
                'transaction_date' => Carbon::parse($data['payment_date'])->toDateString(),
                'description' => 'Payment to supplier for PO #'.$po->po_number.(!empty($data['reference_number']) ? (' (Ref: '.$data['reference_number'].')') : ''),
            ];
            if ($orPath && Schema::hasColumn('cash_flow', 'or_file_path')) {
                $cf['or_file_path'] = $orPath;
            }
            CashFlow::create($cf);

            return redirect()->route('purchase-orders.index')->with('success', 'Supplier payment recorded successfully.');
        });
    }

    protected function deriveApStatus($total, $paid, $dueDate): string
    {
        $total = (float)$total; $paid = (float)$paid; $dueDate = Carbon::parse($dueDate);
        if ($paid <= 0) return 'Unpaid';
        if ($paid + 0.0001 >= $total) return 'Paid';
        return $dueDate->isPast() ? 'Overdue' : 'Partially Paid';
    }

    public function approve(PurchaseOrder $purchase_order)
    {
        if ($purchase_order->status !== 'Pending') {
            return back()->with('error', 'Only pending POs can be approved.');
        }
        return DB::transaction(function () use ($purchase_order) {
            $purchase_order->status = 'Approved';
            $purchase_order->approved_by = Auth::id() ?? null;
            $purchase_order->save();

            $ap = AccountsPayable::create([
                'supplier_id' => $purchase_order->supplier_id,
                'purchase_order_id' => $purchase_order->purchase_order_id,
                'invoice_number' => $this->generateInvoiceNumber(),
                'invoice_date' => now()->toDateString(),
                'due_date' => now()->addDays(30)->toDateString(),
                'total_amount' => $purchase_order->total_amount,
                'amount_paid' => 0,
                'status' => 'Unpaid',
            ]);

            $purchase_order->ap_id = $ap->ap_id;
            $purchase_order->save();

            ActivityLog::create([
                'event_type' => 'po_approved',
                'title' => 'Purchase Order '.$purchase_order->po_number.' approved (₱'.number_format((float)$purchase_order->total_amount, 2).')',
                'context_type' => 'PurchaseOrder',
                'context_id' => $purchase_order->purchase_order_id,
                'amount' => $purchase_order->total_amount,
                'meta' => [
                    'ap_id' => $ap->ap_id,
                    'supplier_id' => $purchase_order->supplier_id,
                ],
            ]);

            return redirect()->route('accounts-payable.show', $ap->ap_id)->with('success', 'PO approved and AP created.');
        });
    }

    public function reject(PurchaseOrder $purchase_order)
    {
        if ($purchase_order->status !== 'Pending') {
            return back()->with('error', 'Only pending POs can be rejected.');
        }
        $purchase_order->status = 'Rejected';
        $purchase_order->approved_by = auth()->id() ?? null;
        $purchase_order->save();
        return back()->with('success', 'PO rejected.');
    }

    protected function generatePoNumber(): string
    {
        $date = now()->format('Ymd');
        $count = PurchaseOrder::whereDate('created_at', now()->toDateString())->count() + 1;
        return sprintf('PO-%s-%04d', $date, $count);
    }

    protected function generateInvoiceNumber(): string
    {
        $date = now()->format('Ymd');
        $rand = random_int(1, 9999);
        return sprintf('INV-%s-%04d', $date, $rand);
    }
}

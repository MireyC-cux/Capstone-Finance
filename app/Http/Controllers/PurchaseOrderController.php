<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\ServiceRequest;
use App\Models\Supplier;
use App\Models\AccountsPayable;
use App\Models\CashFlow;
use App\Models\InventoryStockIn;
use App\Models\PaymentMade;
use App\Models\Expenses;
use App\Services\Inventory\BalanceService;
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
    $query = PurchaseOrder::with(['supplier', 'serviceRequest','accountsPayable'])
                ->whereIn('payment_status', ['Paid', 'Unpaid']) // only Paid or Unpaid
                ->orderByDesc('purchase_order_id');

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
    $po = PurchaseOrder::with(['supplier', 'accountsPayable'])->findOrFail($id);

    // Get paid amount
    $ap = $po->accountsPayable;
    $total = (float) $po->total_amount;
    $paid = 0.0;

    if ($ap) {
        $paid = (float) $ap->amount_paid;
        $status = $ap->status;
    } else {
        // Check Expenses for full payment of this PO
        $exp = Expenses::where('supplier_id', $po->supplier_id)
                       ->where('description', 'like', '%PO #'.$po->po_number.'%')
                       ->sum('amount');

        $paid = (float) $exp;

        $status = match(true) {
            $paid <= 0 => 'Unpaid',
            $paid >= $total => 'Paid',
            default => 'Partially Paid',
        };
    }

    $outstanding = max(0.0, $total - $paid);

    return response()->json([
        'id' => $po->purchase_order_id,
        'po_number' => $po->po_number,
        'supplier' => $po->supplier->supplier_name ?? '—',
        'po_date' => Carbon::parse($po->po_date)->format('Y-m-d'),
        'payment_status' => $status,
        'total' => $total,
        'paid' => $paid,
        'outstanding' => $outstanding,
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
        $po = PurchaseOrder::with(['accountsPayable', 'supplier'])->lockForUpdate()->findOrFail($id);

        $total = (float) $po->total_amount;
        $ap = $po->accountsPayable;

        // --- Validate amount correctness ---
        if ($data['payment_type'] === 'Full' && $data['amount'] != $total) {
            return back()->with('error', 'Full payment must equal the total PO amount.');
        }
        if ($data['payment_type'] === 'Partial' && $data['amount'] >= $total) {
            return back()->with('error', 'Partial payment must be less than the total amount.');
        }

        $isCash = strtolower($data['payment_method']) === 'cash';
        $orPath = null;
        if (!$isCash) {
            if (empty($data['reference_number'])) {
                return back()->with('error', 'Reference number is required for non-cash payments.');
            }
            if (!$request->hasFile('or_file')) {
                return back()->with('error', 'Official Receipt (image/PDF) is required for non-cash payments.');
            }
            $orPath = $request->file('or_file')->store('or_uploads', 'public');
        }

        // --- Handle FULL PAYMENT ---
       if ($data['payment_type'] === 'Full') {
    // Directly store in Expenses, skip Accounts Payable
    $expense = Expenses::create([
        'supplier_id' => $po->supplier_id,
        'expense_name' => 'Purchase Order Payment',
        'category' => 'Office Supplies',
        'description' => 'Full payment for PO #' . $po->po_number,
        'amount' => number_format($data['amount'], 2, '.', ''),
        'expense_date' => Carbon::parse($data['payment_date'])->toDateString(),
        'paid_to' => optional($po->supplier)->supplier_name ?? 'Unknown Supplier',
        'created_by' => Auth::id(),
        'status' => 'Paid',
    ]);

    // ✅ Record in Cash Flow as Outflow
    CashFlow::create([
        'transaction_type' => 'Outflow',
        'source_type' => 'Purchase Order Payment',
        'source_id' => $po->purchase_order_id,
        'amount' => number_format($data['amount'], 2, '.', ''),
        'transaction_date' => Carbon::parse($data['payment_date'])->toDateString(),
        'description' => 'Full payment for PO #' . $po->po_number . 
                         ' (Supplier: ' . (optional($po->supplier)->supplier_name ?? 'Unknown') . ')',
    ]);

    // Update PO directly
    $po->payment_status = 'Paid';
    $po->save();

    return redirect()
        ->route('purchase-orders.index')
        ->with('success', 'Full payment recorded directly in Expenses and added to Cash Flow. PO marked as Paid.');
}

        // --- Handle PARTIAL PAYMENT ---
        if (!$ap) {
            $invNo = $this->generateInvoiceNumber();
            $ap = AccountsPayable::create([
                'supplier_id' => $po->supplier_id,
                'purchase_order_id' => $po->purchase_order_id,
                'invoice_number' => $invNo,
                'invoice_date' => Carbon::parse($po->po_date)->toDateString(),
                'due_date' => Carbon::parse($data['payment_date'])->addDays(30)->toDateString(),
                'total_amount' => $po->total_amount,
                'amount_paid' => 0,
                'status' => 'Unpaid',
            ]);
            $po->ap_id = $ap->ap_id;
            $po->save();
        }

        // Update Accounts Payable as partial
        $ap->amount_paid = number_format($ap->amount_paid + $data['amount'], 2, '.', '');
        $ap->status = 'Partially Paid';
        $ap->save();

        // Record Payment
        PaymentMade::create([
            'ap_id' => $ap->ap_id,
            'payment_date' => Carbon::parse($data['payment_date'])->toDateString(),
            'amount' => number_format($data['amount'], 2, '.', ''),
            'payment_method' => $data['payment_method'],
            'reference_number' => $data['reference_number'] ?? null,
            'receipt_path' => $orPath,
        ]);

        // Log partial expense (optional)
        Expenses::create([
            'supplier_id' => $po->supplier_id,
            'expense_name' => 'Partial Payment - Purchase Order',
            'category' => 'Office Supplies',
            'description' => 'Partial payment for PO #' . $po->po_number,
            'amount' => number_format($data['amount'], 2, '.', ''),
            'expense_date' => Carbon::parse($data['payment_date'])->toDateString(),
            'paid_to' => optional($po->supplier)->supplier_name ?? 'Unknown Supplier',
            'created_by' => Auth::id(),
            'status' => 'Paid'
        ]);

        // Update PO status
        $po->payment_status = 'Partial';
        $po->save();

        return redirect()
            ->route('purchase-orders.index')
            ->with('success', 'Partial payment recorded and stored in Accounts Payable.');
    });
}


    protected function deriveApStatus($total, $paid, $dueDate): string
    {
        $total = (float)$total; $paid = (float)$paid; $dueDate = Carbon::parse($dueDate);
        if ($paid <= 0) return 'Unpaid';
        if ($paid + 0.0001 >= $total) return 'Paid';
        return $dueDate->isPast() ? 'Overdue' : 'Partial';
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
        $purchase_order->approved_by = Auth::id() ?? null;
        $purchase_order->save();
        return back()->with('success', 'PO rejected.');
    }

    public function deliver(Request $request, PurchaseOrder $purchase_order, BalanceService $balance)
    {
        if (!in_array($purchase_order->status, ['Approved', 'approved'], true)) {
            return back()->with('error', 'Only approved POs can be marked as delivered.');
        }

        $data = $request->validate([
            'delivered_date' => 'nullable|date',
        ]);

        return DB::transaction(function () use ($purchase_order, $balance, $data) {
            $po = $purchase_order->load('items');

            $delivDate = !empty($data['delivered_date'])
                ? \Carbon\Carbon::parse($data['delivered_date'])->toDateString()
                : now()->toDateString();

            foreach ($po->items as $it) {
                $invId = null;
                if (!empty($it->item_id)) {
                    $maybeInv = \App\Models\InventoryItem::find($it->item_id);
                    if ($maybeInv) { $invId = $maybeInv->item_id; }
                }
                if (!$invId && !empty($it->description)) {
                    $name = trim(mb_strtolower($it->description));
                    $match = \App\Models\InventoryItem::whereRaw('LOWER(item_name) = ?', [$name])->first();
                    if ($match) { $invId = $match->item_id; }
                }
                if (!$invId) { continue; }

                InventoryStockIn::create([
                    'purchase_order_id' => $po->purchase_order_id,
                    'item_id' => $invId,
                    'quantity' => (int)$it->quantity,
                    'unit_cost' => (float)($it->unit_price ?? 0),
                    'received_date' => $delivDate,
                    'received_by' => Auth::id() ?? null,
                    'remarks' => 'Auto stock-in from PO '.$po->po_number,
                ]);

                $balance->adjust((int)$invId, (int)$it->quantity);
            }

            // Mark PO as delivered and set delivery timestamps/audit fields if present
            $po->status = 'Completed';
            if (Schema::hasColumn('purchase_orders', 'delivered_at')) {
                $po->delivered_at = now();
            }
            if (Schema::hasColumn('purchase_orders', 'delivery_date')) {
                $po->delivery_date = $delivDate;
            }
            if (Schema::hasColumn('purchase_orders', 'delivered_by')) {
                $po->delivered_by = Auth::id() ?? null;
            }
            $po->save();

            return redirect()->route('finance.inventory.stock-in.index')
                ->with('success', 'PO marked delivered and stock-in entries recorded.');
        });
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

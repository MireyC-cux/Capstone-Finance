<?php

namespace App\Http\Controllers;

use App\Models\AccountsPayable;
use App\Models\ServiceRequest;
use App\Models\Supplier;
use App\Models\Expenses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Pagination\LengthAwarePaginator;
use Carbon\Carbon;

class AccountsPayableController extends Controller
{
   public function index(Request $request)
{
    if (!Schema::hasTable('accounts_payable')) {
        $payables = new LengthAwarePaginator([], 0, 25);
        $suppliers = Supplier::orderBy('supplier_name')->get();
        $stats = [
            'total' => 0,
            'paid' => 0,
            'overdue' => 0,
            'unpaid' => 0,
            'partial' => 0,
        ];
        $unpaidExpenses = new LengthAwarePaginator([], 0, 10);
        return view('finance.ap.index', compact('payables', 'suppliers', 'stats', 'unpaidExpenses'));
    }

    // ✅ Display only Partially Paid records
    $query = AccountsPayable::with(['supplier', 'purchaseOrder'])
        ->where('status', 'Partially Paid');

    // Optional filters
    if ($request->filled('supplier_id')) $query->where('supplier_id', $request->get('supplier_id'));
    if ($request->filled('po_number')) $query->whereHas('purchaseOrder', fn($q) => $q->where('po_number', 'like', '%'.$request->get('po_number').'%'));
    if ($request->filled('from')) $query->whereDate('invoice_date', '>=', $request->get('from'));
    if ($request->filled('to')) $query->whereDate('invoice_date', '<=', $request->get('to'));

    // Compute stats for context (optional)
    $stats = [
        'total' => (clone $query)->sum('total_amount'),
        'paid' => (clone $query)->sum('amount_paid'),
        'overdue' => (clone $query)->where('status', 'Overdue')->count(),
        'unpaid' => (clone $query)->where('status', 'Unpaid')->count(),
        'partial' => (clone $query)->count(), // All are partials anyway
    ];

    // ✅ Fetch only partials
    $payables = $query->orderByDesc('ap_id')->paginate(25)->withQueryString();

    $suppliers = Supplier::orderBy('supplier_name')->get();
    $unpaidExpenses = Expenses::where('status','Unpaid')->orderByDesc('expense_date')->paginate(10)->withQueryString();

    return view('finance.ap.index', compact('payables', 'suppliers', 'stats', 'unpaidExpenses'));
}

    public function show(AccountsPayable $accounts_payable)
    {
        $accounts_payable->load(['supplier', 'payments', 'purchaseOrder.items']);
        return view('finance.ap.show', ['ap' => $accounts_payable]);
    }

    public function eligibleServiceRequests()
    {
        $srs = ServiceRequest::with('items')
            ->where('order_status', 'Completed')
            ->whereDoesntHave('accountsReceivable')
            ->orderByDesc('service_request_id')
            ->paginate(25);
        return view('finance.ap.eligible', compact('srs'));
    }

    public function markOverdues()
    {
        $query = AccountsPayable::whereIn('status', ['Unpaid', 'Partially Paid'])
            ->whereDate('due_date', '<', Carbon::today()->toDateString());

        $candidates = (clone $query)->count();
        $updated = $query->update(['status' => 'Overdue']);

        $msg = $candidates === 0
            ? 'No AP records eligible for overdue marking.'
            : ("$updated of $candidates AP records marked overdue.");

        return back()->with('success', $msg);
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
        $ap = AccountsPayable::with(['supplier', 'purchaseOrder'])->lockForUpdate()->findOrFail($id);
        $po = $ap->purchaseOrder;
        $supplier = $ap->supplier;

        $total = (float) $ap->total_amount;
        $currentPaid = (float) $ap->amount_paid;
        $newTotalPaid = $currentPaid + $data['amount'];

        // --- VALIDATION ---
        if ($data['payment_type'] === 'Full' && $newTotalPaid != $total) {
            return back()->with('error', 'Full payment must equal the remaining payable amount.');
        }
        if ($data['payment_type'] === 'Partial' && $data['amount'] >= ($total - $currentPaid)) {
            return back()->with('error', 'Partial payment cannot exceed or equal remaining balance.');
        }

        // --- FILE HANDLING ---
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

        // --- UPDATE ACCOUNTS PAYABLE ---
        $ap->amount_paid = number_format($newTotalPaid, 2, '.', '');
        if ($newTotalPaid >= $total) {
            $ap->status = 'Paid';
        } else {
            $ap->status = 'Partially Paid';
        }
        $ap->save();

        // --- RECORD PAYMENT ---
        \App\Models\PaymentMade::create([
            'ap_id' => $ap->ap_id,
            'payment_date' => Carbon::parse($data['payment_date'])->toDateString(),
            'amount' => number_format($data['amount'], 2, '.', ''),
            'payment_method' => $data['payment_method'],
            'reference_number' => $data['reference_number'] ?? null,
            'receipt_path' => $orPath,
        ]);

        // --- RECORD EXPENSE ---
        \App\Models\Expenses::create([
            'supplier_id' => $ap->supplier_id,
            'expense_name' => $ap->status === 'Paid' ? 'Full Payment - Purchase Order' : 'Partial Payment - Purchase Order',
            'category' => 'Purchase Orders Balance Payment',
            'description' => ucfirst($ap->status) . ' for PO #' . optional($po)->po_number,
            'amount' => number_format($data['amount'], 2, '.', ''),
            'expense_date' => Carbon::parse($data['payment_date'])->toDateString(),
            'paid_to' => optional($supplier)->supplier_name ?? 'Unknown Supplier',
            'created_by' => session('user_email') ?? ('user@system'),
            'status' => 'Paid',
        ]);
        

        // --- RECORD IN CASH FLOW ---
        \App\Models\CashFlow::create([
            'transaction_type' => 'Outflow',
            'source_type' => 'Payable Balance from Purchase Order',
            'source_id' => $ap->ap_id,
            'amount' => number_format($data['amount'], 2, '.', ''),
            'transaction_date' => Carbon::parse($data['payment_date'])->toDateString(),
            'description' => ucfirst($ap->status) . ' payment for PO #' . (optional($po)->po_number ?? 'N/A') .
                            ' (Supplier: ' . (optional($supplier)->supplier_name ?? 'Unknown') . ')',
        ]);

        // --- UPDATE PO PAYMENT STATUS ---
        if ($po) {
            $po->payment_status = $ap->status === 'Paid' ? 'Paid' : 'Partial';
            $po->save();
        }

        // --- RETURN MESSAGE ---
        $message = $ap->status === 'Paid'
            ? 'Full payment recorded, Accounts Payable marked as Paid.'
            : 'Partial payment recorded successfully.';

        return redirect()
            ->route('accounts-payable.index')
            ->with('success', $message);
    });
}



}

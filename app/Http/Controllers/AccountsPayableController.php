<?php

namespace App\Http\Controllers;

use App\Models\AccountsPayable;
use App\Models\ServiceRequest;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AccountsPayableController extends Controller
{
    public function index(Request $request)
    {
        $query = AccountsPayable::with(['supplier', 'purchaseOrder'])
            ->whereHas('purchaseOrder', function($q){
                $q->where('status', 'Approved');
            });

        if ($request->filled('status')) $query->where('status', $request->get('status'));
        if ($request->filled('supplier_id')) $query->where('supplier_id', $request->get('supplier_id'));
        if ($request->filled('po_number')) $query->whereHas('purchaseOrder', fn($q) => $q->where('po_number', 'like', '%'.$request->get('po_number').'%'));
        if ($request->filled('from')) $query->whereDate('invoice_date', '>=', $request->get('from'));
        if ($request->filled('to')) $query->whereDate('invoice_date', '<=', $request->get('to'));

        $stats = [
            'total' => (clone $query)->sum('total_amount'),
            'paid' => (clone $query)->sum('amount_paid'),
            'overdue' => (clone $query)->where('status', 'Overdue')->count(),
            'unpaid' => (clone $query)->where('status', 'Unpaid')->count(),
            'partial' => (clone $query)->where('status', 'Partially Paid')->count(),
        ];

        $payables = $query->orderByDesc('ap_id')->paginate(25)->withQueryString();
        $suppliers = Supplier::orderBy('supplier_name')->get();
        return view('finance.ap.index', compact('payables', 'suppliers', 'stats'));
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
}

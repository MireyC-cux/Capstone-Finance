<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\AccountsReceivable;
use App\Models\Customer;

class AccountsReceivableController extends Controller
{
    public function index(Request $request)
    {
        $query = AccountsReceivable::with(['customer'])
            ->when($request->input('q'), function ($q, $term) {
                $q->whereHas('customer', function ($cq) use ($term) {
                    $cq->where('full_name', 'like', "%$term%");
                })->orWhere('invoice_number', 'like', "%$term%");
            })
            ->when($request->input('status'), fn($q, $s) => $q->where('status', $s))
            ->when($request->input('from'), fn($q, $from) => $q->whereDate('invoice_date', '>=', $from))
            ->when($request->input('to'), fn($q, $to) => $q->whereDate('invoice_date', '<=', $to))
            ->orderByDesc('ar_id');

        $ars = $query->paginate(20)->withQueryString();

        $totals = [
            'total_outstanding' => AccountsReceivable::sum(DB::raw('total_amount - amount_paid')),
            'total_paid' => AccountsReceivable::sum('amount_paid'),
            'total_overdue' => AccountsReceivable::where('status', 'Overdue')->sum(DB::raw('total_amount - amount_paid')),
        ];

        return view('accounts_receivable.index', compact('ars', 'totals'));
    }

    public function show($id)
    {
        $ar = AccountsReceivable::with(['customer', 'payments'])->findOrFail($id);
        return response()->json($ar);
    }

    public function updateStatus()
    {
        $affected = AccountsReceivable::whereDate('due_date', '<', Carbon::today())
            ->whereIn('status', ['Unpaid', 'Partially Paid'])
            ->update(['status' => 'Overdue']);

        return back()->with('success', "{$affected} ARs marked as Overdue");
    }

    public function export(Request $request)
    {
        // Placeholder: integrate DomPDF or Excel later
        return back()->with('info', 'Export coming soon');
    }
}

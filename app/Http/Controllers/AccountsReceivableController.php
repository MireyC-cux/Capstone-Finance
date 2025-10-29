<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\AccountsReceivable;
use App\Models\Customer;
use App\Models\PaymentReceived;

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
            // Exclude Paid by default unless explicitly filtered to Paid
            ->when(!$request->filled('status'), fn($q) => $q->where('status', '<>', 'Paid'))
            ->when($request->input('status'), fn($q, $s) => $q->where('status', $s))
            ->when($request->input('from'), fn($q, $from) => $q->whereDate('invoice_date', '>=', $from))
            ->when($request->input('to'), fn($q, $to) => $q->whereDate('invoice_date', '<=', $to))
            // Prioritize Unpaid, Overdue, then Partially Paid; then by latest ar_id
            ->orderByRaw("FIELD(status, 'Unpaid','Overdue','Partially Paid') ASC")
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

    public function totals(Request $request)
    {
        $metric = strtolower($request->get('metric', ''));
        if (!in_array($metric, ['outstanding','paid','overdue'], true)) {
            return response()->json(['error' => 'Invalid metric'], 422);
        }

        if ($metric === 'paid') {
            $rows = PaymentReceived::query()
                ->with(['accountsReceivable.customer'])
                ->orderByDesc('payment_date')
                ->limit(50)
                ->get()
                ->map(function ($p) {
                    $ar = $p->accountsReceivable;
                    $cust = $ar && $ar->customer ? ($ar->customer->business_name ?: $ar->customer->full_name) : '—';
                    return [
                        'when' => Carbon::parse($p->payment_date)->format('Y-m-d'),
                        'amount' => (float)$p->amount,
                        'method' => $p->payment_method,
                        'reference' => $p->reference_number,
                        'ar_id' => $p->ar_id,
                        'customer' => $cust,
                    ];
                });
            $total = (float) PaymentReceived::sum('amount');
            return response()->json([
                'title' => 'Total Paid Breakdown',
                'metric' => 'paid',
                'total' => $total,
                'rows' => $rows,
                'columns' => ['when','customer','method','reference','amount']
            ]);
        }

        $base = AccountsReceivable::query()->with(['customer']);
        if ($metric === 'overdue') {
            $base->where('status', 'Overdue');
        } else {
            $base->whereIn('status', ['Unpaid','Partially Paid','Overdue']);
        }

        $ars = $base->get();
        $rows = $ars->map(function ($ar) {
            $customer = $ar->customer ? ($ar->customer->business_name ?: $ar->customer->full_name) : '—';
            $balance = max(0.0, (float)$ar->total_amount - (float)$ar->amount_paid);
            return [
                'invoice' => $ar->invoice_number ?? '—',
                'customer' => $customer,
                'due' => Carbon::parse($ar->due_date)->format('Y-m-d'),
                'status' => $ar->status,
                'total' => (float)$ar->total_amount,
                'paid' => (float)$ar->amount_paid,
                'balance' => $balance,
            ];
        })->filter(function ($r) use ($metric) {
            if ($metric === 'outstanding' || $metric === 'overdue') {
                return $r['balance'] > 0;
            }
            return true;
        })->values();

        $total = $rows->sum(function ($r) use ($metric) {
            if ($metric === 'outstanding' || $metric === 'overdue') { return $r['balance']; }
            return $r['total'];
        });

        return response()->json([
            'title' => $metric === 'overdue' ? 'Total Overdue Breakdown' : 'Total Outstanding Breakdown',
            'metric' => $metric,
            'total' => (float)$total,
            'rows' => $rows,
            'columns' => ['invoice','customer','due','status','total','paid','balance']
        ]);
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

<?php

namespace App\Http\Controllers;

use App\Models\AccountsPayable;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class APReportController extends Controller
{
    public function aging(Request $request)
    {
        $query = AccountsPayable::with('supplier');

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->get('supplier_id'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }
        if ($request->filled('from')) {
            $query->whereDate('invoice_date', '>=', $request->get('from'));
        }
        if ($request->filled('to')) {
            $query->whereDate('invoice_date', '<=', $request->get('to'));
        }

        $today = Carbon::today();
        $rows = $query->get()->groupBy('supplier_id')->map(function ($list, $supplierId) use ($today) {
            $supplier = optional($list->first()->supplier);
            $buckets = [
                'current' => 0,
                'd1_30' => 0,
                'd31_60' => 0,
                'd61_90' => 0,
                'd91p' => 0,
                'total' => 0,
            ];
            foreach ($list as $ap) {
                $balance = max(0, (float)$ap->total_amount - (float)$ap->amount_paid);
                $days = Carbon::parse($ap->due_date)->diffInDays($today, false);
                if ($days <= 0) $buckets['current'] += $balance;
                elseif ($days <= 30) $buckets['d1_30'] += $balance;
                elseif ($days <= 60) $buckets['d31_60'] += $balance;
                elseif ($days <= 90) $buckets['d61_90'] += $balance;
                else $buckets['d91p'] += $balance;
                $buckets['total'] += $balance;
            }
            return [
                'supplier_id' => $supplierId,
                'supplier_name' => $supplier->supplier_name ?? 'Unknown',
                'buckets' => $buckets,
            ];
        })->values();

        $suppliers = Supplier::orderBy('supplier_name')->get();
        return view('finance.ap.reports.aging', [
            'rows' => $rows,
            'suppliers' => $suppliers,
            'filters' => $request->only(['supplier_id', 'status', 'from', 'to'])
        ]);
    }
}

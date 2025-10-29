<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryReportController extends Controller
{
    public function index(Request $request)
    {
        $start = $request->filled('start') ? $request->date('start') : now()->subMonths(5)->startOfMonth()->toDateString();
        $end = $request->filled('end') ? $request->date('end') : now()->endOfDay()->toDateString();

        $summary = DB::table('inventory_items as i')
            ->leftJoin('inventory_balances as b','b.item_id','=','i.item_id')
            ->select('i.item_id','i.item_name','i.category','i.brand','i.model','i.unit','i.reorder_level','i.unit_cost', DB::raw('COALESCE(b.current_stock,0) as stock'))
            ->orderBy('i.item_name')->get();

        $usage = DB::table('inventory_stock_out as o')
            ->join('inventory_items as i','i.item_id','=','o.item_id')
            ->whereBetween('o.issued_date', [$start, $end])
            ->select('i.category','i.item_name', DB::raw('SUM(o.quantity) as qty'))
            ->groupBy('i.category','i.item_name')
            ->orderBy('i.category')->orderBy('i.item_name')->get();

        $purchases = DB::table('inventory_stock_in as si')
            ->leftJoin('purchase_orders as po','po.purchase_order_id','=','si.purchase_order_id')
            ->leftJoin('suppliers as s','s.supplier_id','=','po.supplier_id')
            ->whereBetween('si.received_date', [$start, $end])
            ->select('s.supplier_name', DB::raw('SUM(si.total_cost) as total'), DB::raw('COUNT(si.stock_in_id) as entries'))
            ->groupBy('s.supplier_name')
            ->orderByDesc(DB::raw('SUM(si.total_cost)'))->get();

        $latestCosts = DB::table('inventory_stock_in')
            ->select('item_id', DB::raw('MAX(received_date) as d'))
            ->groupBy('item_id');
        $costMap = DB::table('inventory_stock_in as a')
            ->joinSub($latestCosts, 'lc', function($j){ $j->on('a.item_id','=','lc.item_id')->on('a.received_date','=','lc.d'); })
            ->pluck('unit_cost','a.item_id');

        $valuation = $summary->map(function($r) use ($costMap) {
            $uc = $costMap[$r->item_id] ?? $r->unit_cost ?? 0;
            $r->valuation = $uc * (int)$r->stock;
            return $r;
        });

        return view('finance.inventory.reports.index', compact('start','end','summary','usage','purchases','valuation'));
    }
}

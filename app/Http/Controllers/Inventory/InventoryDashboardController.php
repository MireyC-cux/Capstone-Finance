<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use Illuminate\Support\Facades\DB;

class InventoryDashboardController extends Controller
{
    public function index()
    {
        $items = InventoryItem::with('balance')->orderBy('item_name')->paginate(20);

        $lowStock = DB::table('inventory_items as i')
            ->leftJoin('inventory_balances as b', 'b.item_id', '=', 'i.item_id')
            ->select('i.item_id','i.item_name','i.category','i.brand','i.model','i.reorder_level', DB::raw('COALESCE(b.current_stock,0) as stock'))
            ->where('i.status', 'active')
            ->whereRaw('COALESCE(b.current_stock,0) <= i.reorder_level')
            ->orderBy('i.item_name')
            ->get();

        return view('finance.inventory.dashboard', compact('items','lowStock'));
    }
}

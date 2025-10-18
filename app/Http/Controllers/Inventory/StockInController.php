<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\InventoryStockIn;
use App\Models\InventoryItem;
use App\Services\Inventory\BalanceService;
use Illuminate\Http\Request;

class StockInController extends Controller
{
    public function index()
    {
        $rows = InventoryStockIn::with('item')->orderByDesc('received_date')->paginate(20);
        $items = InventoryItem::where('status','active')->orderBy('item_name')->get(['item_id','item_name']);
        return view('finance.inventory.stock_in.index', compact('rows','items'));
    }

    public function store(Request $request, BalanceService $balance)
    {
        $data = $request->validate([
            'po_id'=>'nullable|integer|exists:purchase_orders,po_id',
            'item_id'=>'required|integer|exists:inventory_items,item_id',
            'quantity'=>'required|integer|min:1',
            'unit_cost'=>'required|numeric|min:0',
            'received_date'=>'required|date',
            'received_by'=>'nullable|integer',
            'remarks'=>'nullable|string',
        ]);

        $row = InventoryStockIn::create($data);
        $balance->adjust((int)$data['item_id'], (int)$data['quantity']);

        return redirect()->route('finance.inventory.stock-in.index')->with('success','Stock-in recorded.');
    }
}

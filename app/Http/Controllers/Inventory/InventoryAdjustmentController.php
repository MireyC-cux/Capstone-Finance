<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\InventoryAdjustment;
use App\Models\InventoryItem;
use App\Services\Inventory\BalanceService;
use Illuminate\Http\Request;

class InventoryAdjustmentController extends Controller
{
    public function index()
    {
        $rows = InventoryAdjustment::with('item')->orderByDesc('adjustment_date')->paginate(20);
        $items = InventoryItem::where('status','active')->orderBy('item_name')->get(['item_id','item_name']);
        return view('finance.inventory.adjustments.index', compact('rows','items'));
    }

    public function store(Request $request, BalanceService $balance)
    {
        $data = $request->validate([
            'item_id'=>'required|integer|exists:inventory_items,item_id',
            'adjustment_type'=>'required|in:Increase,Decrease',
            'quantity'=>'required|integer|min:1',
            'reason'=>'nullable|string',
            'adjusted_by'=>'nullable|integer',
            'adjustment_date'=>'required|date',
        ]);

        InventoryAdjustment::create($data);
        $delta = $data['adjustment_type']==='Increase' ? (int)$data['quantity'] : -(int)$data['quantity'];
        $balance->adjust((int)$data['item_id'], $delta);

        return redirect()->route('finance.inventory.adjustments.index')->with('success','Adjustment recorded.');
    }
}

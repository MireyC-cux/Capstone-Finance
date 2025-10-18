<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use Illuminate\Http\Request;

class InventoryItemController extends Controller
{
    public function index()
    {
        $items = InventoryItem::with('balance')->orderBy('item_name')->paginate(20);
        return view('finance.inventory.items.index', compact('items'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'item_name'=>'required|string|max:255',
            'category'=>'required|in:Aircon Unit,Spare Part,Material,Consumable',
            'brand'=>'nullable|string|max:255',
            'model'=>'nullable|string|max:255',
            'unit'=>'required|string|max:50',
            'reorder_level'=>'required|integer|min:0',
            'unit_cost'=>'nullable|numeric|min:0',
            'selling_price'=>'nullable|numeric|min:0',
            'status'=>'required|in:active,inactive',
        ]);
        InventoryItem::create($data);
        return redirect()->route('finance.inventory.items.index')->with('success','Item created.');
    }

    public function update(Request $request, InventoryItem $item)
    {
        $data = $request->validate([
            'item_name'=>'required|string|max:255',
            'category'=>'required|in:Aircon Unit,Spare Part,Material,Consumable',
            'brand'=>'nullable|string|max:255',
            'model'=>'nullable|string|max:255',
            'unit'=>'required|string|max:50',
            'reorder_level'=>'required|integer|min:0',
            'unit_cost'=>'nullable|numeric|min:0',
            'selling_price'=>'nullable|numeric|min:0',
            'status'=>'required|in:active,inactive',
        ]);
        $item->update($data);
        return redirect()->route('finance.inventory.items.index')->with('success','Item updated.');
    }

    public function destroy(InventoryItem $item)
    {
        $item->delete();
        return back()->with('success','Item deleted.');
    }
}

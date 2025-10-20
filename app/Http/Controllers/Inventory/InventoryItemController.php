<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use Illuminate\Http\Request;
use App\Models\ActivityLog;

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
        $item = InventoryItem::create($data);

        ActivityLog::create([
            'event_type' => 'inventory_item_created',
            'title' => 'New inventory item added: '.$item->item_name,
            'context_type' => 'InventoryItem',
            'context_id' => $item->item_id ?? $item->getKey(),
            'amount' => $item->unit_cost ?? null,
            'meta' => [
                'category' => $item->category,
                'unit' => $item->unit,
                'reorder_level' => $item->reorder_level,
            ],
        ]);
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

        ActivityLog::create([
            'event_type' => 'inventory_item_updated',
            'title' => 'Inventory item updated: '.$item->item_name,
            'context_type' => 'InventoryItem',
            'context_id' => $item->item_id ?? $item->getKey(),
            'amount' => $item->unit_cost ?? null,
            'meta' => [
                'category' => $item->category,
                'unit' => $item->unit,
                'reorder_level' => $item->reorder_level,
            ],
        ]);
        return redirect()->route('finance.inventory.items.index')->with('success','Item updated.');
    }

    public function destroy(InventoryItem $item)
    {
        $name = $item->item_name;
        $id = $item->item_id ?? $item->getKey();
        $item->delete();

        ActivityLog::create([
            'event_type' => 'inventory_item_deleted',
            'title' => 'Inventory item deleted: '.$name,
            'context_type' => 'InventoryItem',
            'context_id' => $id,
            'amount' => null,
            'meta' => null,
        ]);
        return back()->with('success','Item deleted.');
    }
}

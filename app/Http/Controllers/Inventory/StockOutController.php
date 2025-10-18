<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\InventoryStockOut;
use App\Models\InventoryItem;
use App\Models\InventoryBalance;
use App\Services\Inventory\BalanceService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class StockOutController extends Controller
{
    public function index()
    {
        $rows = InventoryStockOut::with('item')->orderByDesc('issued_date')->paginate(20);
        $items = InventoryItem::where('status','active')->orderBy('item_name')->get(['item_id','item_name']);
        return view('finance.inventory.stock_out.index', compact('rows','items'));
    }

    public function store(Request $request, BalanceService $balance)
    {
        $data = $request->validate([
            'service_request_id'=>'nullable|integer|exists:service_requests,service_request_id',
            'item_id'=>'required|integer|exists:inventory_items,item_id',
            'quantity'=>'required|integer|min:1',
            'issued_to'=>'nullable|integer|exists:employeeprofiles,employeeprofiles_id',
            'issued_date'=>'required|date',
            'purpose'=>'nullable|string|max:255',
            'remarks'=>'nullable|string',
        ]);

        $avail = optional(InventoryBalance::find((int)$data['item_id']))->current_stock ?? 0;
        if ($avail < (int)$data['quantity']) {
            throw ValidationException::withMessages(['quantity' => "Not enough stock. Available: {$avail}."]);
        }

        $row = InventoryStockOut::create($data);
        $balance->adjust((int)$data['item_id'], -(int)$data['quantity']);

        return redirect()->route('finance.inventory.stock-out.index')->with('success','Stock-out recorded.');
    }
}

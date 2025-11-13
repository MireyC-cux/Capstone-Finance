<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\InventoryStockOut;
use App\Models\ServiceRequestItem;
use App\Models\AirconType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockOutController extends Controller
{
    public function index()
    {
        $rows = InventoryStockOut::with(['item', 'airconType'])
            ->orderByDesc('issued_date')
            ->paginate(10);

        $items = ServiceRequestItem::orderBy('service_type')
            ->get(['item_id', 'service_type', 'aircon_type_id']);

        $airconTypes = AirconType::orderBy('name')->get(['aircon_type_id', 'name as aircon_type']);

        return view('finance.inventory.stock_out.index', compact('rows', 'items', 'airconTypes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'item_id' => 'required|exists:service_request_items,item_id',
            'item_name' => 'nullable|string|max:255',
            'aircon_type_id' => 'nullable|exists:aircon_types,aircon_type_id',
            'quantity' => 'required|integer|min:1',
            'issued_date' => 'required|date',
            'status' => 'required|in:Needed Request,Requested,Approve,Shipped',
        ]);

        $item = ServiceRequestItem::findOrFail($data['item_id']);
        $data['service_type'] = $item->service_type;

        InventoryStockOut::create($data);

        return redirect()->route('finance.inventory.stock-out.index')
            ->with('success', 'Stock-out record added successfully.');
    }

    public function update(Request $request, $id)
    {
        $row = InventoryStockOut::findOrFail($id);

        $data = $request->validate([
            'item_id' => 'required|exists:service_request_items,item_id',
            'item_name' => 'nullable|string|max:255',
            'aircon_type_id' => 'nullable|exists:aircon_types,aircon_type_id',
            'quantity' => 'required|integer|min:1',
            'issued_date' => 'required|date',
            'status' => 'required|in:Needed Request,Requested,Approve, Shipped',
        ]);

        $item = ServiceRequestItem::findOrFail($data['item_id']);
        $data['service_type'] = $item->service_type;

        $row->update($data);

        return redirect()->route('finance.inventory.stock-out.index')
            ->with('success', 'Stock-out record updated successfully.');
    }

    public function requestByItem(Request $request)
    {
        $data = $request->validate([
            'item_name' => 'required|string',
            'status' => 'nullable|in:Requested',
        ]);

        $updated = DB::table('inventory_stock_out')
            ->where('item_name', $data['item_name'])
            ->where('status', 'Needed Request')
            ->update(['status' => 'Requested']);

        return redirect()->back()
            ->with('success', $updated ? 'Status updated to Requested.' : 'No pending records to update.');
    }

    /**
     * Fetch all Aircon Types that belong to the same service type.
     */
    public function getAirconTypes(Request $request)
    {
        $item_id = $request->query('item_id');

        if (!$item_id) {
            return response()->json([]);
        }

        $item = ServiceRequestItem::find($item_id);
        if (!$item) {
            return response()->json([]);
        }

        $aircons = DB::table('service_request_items as sri')
            ->join('aircon_types as at', 'at.aircon_type_id', '=', 'sri.aircon_type_id')
            ->where('sri.service_type', $item->service_type)
            ->distinct()
            ->select('at.aircon_type_id', 'at.name as aircon_type')
            ->orderBy('at.name')
            ->get();

        return response()->json($aircons);
    }
}

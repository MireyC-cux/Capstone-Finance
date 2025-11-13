<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\InventoryStockIn;
use App\Models\InventoryItem;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Services\Inventory\BalanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockInController extends Controller
{
  public function index()
{
    // ✅ Automatically record delivered purchase orders as stock-ins
    $this->recordDeliveredPurchaseOrders();

    // ✅ Fetch Stock-In entries (new structure)
    $rows = InventoryStockIn::with('supplier', 'purchaseOrder')
        ->orderByDesc('delivered_date')
        ->paginate(20);

    return view('finance.inventory.stock_in.index', compact('rows'));
}


    public function store(Request $request, BalanceService $balance)
    {
        $data = $request->validate([
            'purchase_order_id' => 'nullable|integer|exists:purchase_orders,purchase_order_id',
            'item_id' => 'required|integer|exists:inventory_items,item_id',
            'quantity' => 'required|integer|min:1',
            'unit_cost' => 'required|numeric|min:0',
            'received_date' => 'required|date',
            'received_by' => 'nullable|integer',
            'remarks' => 'nullable|string',
        ]);

        $row = InventoryStockIn::create($data);
        $balance->adjust((int)$data['item_id'], (int)$data['quantity']);

        return redirect()
            ->route('finance.inventory.stock-in.index')
            ->with('success', 'Stock-in recorded.');
    }

    /**
     * ✅ Record all delivered purchase orders into inventory_stock_in.
     */
    private function recordDeliveredPurchaseOrders()
{
    $deliveredPOs = PurchaseOrder::where('status', 'delivered')->get();

    foreach ($deliveredPOs as $po) {
        $exists = InventoryStockIn::where('purchase_order_id', $po->purchase_order_id)->exists();

        if (!$exists) {
            InventoryStockIn::create([
                'po_number' => $po->po_number,
                'purchase_order_id' => $po->purchase_order_id,
                'supplier_id' => $po->supplier_id,
                'delivered_date' => $po->delivered_date ?? now(),
                'status' => $po->status,
                'items' => $po->items,
                'payment_status' => $po->payment_status,
                'remarks' => 'Auto-recorded from delivered PO#' . $po->purchase_order_id,
            ]);
        }
    }
}

    }


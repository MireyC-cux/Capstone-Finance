<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use Illuminate\Support\Facades\DB;

class InventoryDashboardController extends Controller
{
    public function index()
    {
        // 🔹 Fetch paginated inventory items with their balances
        $items = InventoryItem::with('balance')
            ->where('status', 'active')
            ->orderBy('item_name')
            ->paginate(20);

        // 🔹 Identify low-stock items and join summed stock-out quantities per item_name
        $stockOutSums = DB::table('inventory_stock_out')
            ->select('item_name', DB::raw('SUM(quantity) as total_stock_out'))
            ->groupBy('item_name');

        $lowStock = DB::table('inventory_items as i')
            ->leftJoin('inventory_balances as b', 'b.item_id', '=', 'i.item_id')
            ->leftJoinSub($stockOutSums, 'so', function ($join) {
                $join->on('so.item_name', '=', 'i.item_name');
            })
            ->select(
                'i.item_id',
                'i.item_name',
                'i.category',
                'i.brand',
                'i.model',
                'i.reorder_level',
                DB::raw('COALESCE(b.current_stock,0) as stock'),
                DB::raw('COALESCE(so.total_stock_out,0) as stock_out_qty')
            )
            ->where('i.status', 'active')
            ->whereRaw('COALESCE(b.current_stock,0) <= i.reorder_level')
            ->orderBy('i.item_name')
            ->get();

            $totalLowStockQty = $lowStock->sum('stock');
        // 🔹 Calculate total active stock count (sum of all qty from delivered stock-ins)
        $stockInRecords = DB::table('inventory_stock_in')
            ->where('status', 'delivered')
            ->pluck('items'); // get all items only

        $activeStockCount = 0;

        foreach ($stockInRecords as $rawItems) {
            if (empty($rawItems)) continue;

            // Ensure we pass a string to json_decode: if it's not a string, encode it to JSON first
            if (!is_string($rawItems)) {
                $rawItems = json_encode($rawItems);
            }

            // Decode once
            $decoded = json_decode($rawItems, true);

            // Handle if still a JSON string (double encoded)
            if (is_string($decoded)) {
                $decoded = json_decode($decoded, true);
            }

            // Now sum all quantities safely
            if (is_array($decoded)) {
                foreach ($decoded as $item) {
                    $activeStockCount += (float)($item['qty'] ?? 0);
                }
            }
        }

        $totalStockOutQty = DB::table('inventory_stock_out')->sum('quantity');

        $stockOutRows = DB::table('inventory_stock_out as so')
            ->leftJoin('aircon_types as at', 'at.aircon_type_id', '=', 'so.aircon_type_id')
            ->orderByDesc('issued_date')
            ->select(
                'so.stock_out_id',
                'so.item_name',
                'so.service_type',
                DB::raw('at.name as aircon_type'),
                'so.quantity',
                'so.status',
                'so.issued_date'
            )
            ->get();

        // 🔹 Return dashboard view
        return view('finance.inventory.dashboard', compact(
            'items',
            'lowStock',
            'activeStockCount',
            'totalLowStockQty',
            'totalStockOutQty',
            'stockOutRows'
        ));
    }
}

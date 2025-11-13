<?php

namespace App\Observers;

use App\Models\PurchaseOrder;
use App\Models\InventoryItem;
use App\Models\InventoryStockIn;
use App\Services\Inventory\BalanceService;
use Illuminate\Support\Facades\DB;

class PurchaseOrderObserver
{
    public function updated(PurchaseOrder $po): void
    {
        try {
            if (!$po->isDirty('status')) { return; }
            $s = strtolower((string)$po->status);
            if (!in_array($s, ['delivered','completed'], true)) { return; }

            $po->loadMissing('items');
            if ($po->items->isEmpty()) { return; }

            DB::transaction(function () use ($po) {
                $receivedDate = optional($po->delivery_date)->toDateString()
                    ?? optional($po->delivered_at)->toDateString()
                    ?? now()->toDateString();
                $balance = new BalanceService();

                foreach ($po->items as $it) {
                    $invId = $it->item_id ? (int)$it->item_id : null;
                    if (!$invId && !empty($it->description)) {
                        $name = trim(mb_strtolower((string)$it->description));
                        $inv = InventoryItem::whereRaw('LOWER(item_name) = ?', [$name])->first();
                        if ($inv) { $invId = (int)$inv->item_id; }
                    }
                    if (!$invId) { continue; }

                    $exists = InventoryStockIn::where('purchase_order_id', $po->purchase_order_id)
                        ->where('item_id', $invId)->exists();
                    if ($exists) { continue; }

                    InventoryStockIn::create([
                        'purchase_order_id' => $po->purchase_order_id,
                        'item_id' => $invId,
                        'quantity' => (int)$it->quantity,
                        'unit_cost' => (float)($it->unit_price ?? 0),
                        'received_date' => $receivedDate,
                        'received_by' => session('user_email') ?? 'user@example.com',
                        'remarks' => 'Auto stock-in from PO '.$po->po_number,
                    ]);
                    $balance->adjust((int)$invId, (int)$it->quantity);
                }
            });
        } catch (\Throwable $e) {
            // swallow
        }
    }
}

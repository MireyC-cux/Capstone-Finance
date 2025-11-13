<?php

namespace App\Observers;

use App\Models\ServiceRequest;
use App\Models\PurchaseOrder;
use App\Models\InventoryItem;
use App\Models\InventoryStockOut;
use App\Models\InventoryBalance;
use App\Services\Inventory\BalanceService;
use Illuminate\Support\Facades\DB;

class ServiceRequestObserver
{
    public function updated(ServiceRequest $sr): void
    {
        try {
            if (!$sr->isDirty('order_status')) { return; }
            $status = strtolower((string)$sr->order_status);
            if ($status !== 'Completed') { return; }

            $sr->loadMissing('items');
            $hasBuy = $sr->items->contains(function($i){
                $st = strtolower(trim((string)($i->service_type ?? '')));
                return in_array($st, ['buy and install','buy & install','buy only','buy-only','buy&install'], true);
            });
            if (!$hasBuy) { return; }

            DB::transaction(function () use ($sr) {
                $issueDate = optional($sr->accomplishment_date)->toDateString() ?? now()->toDateString();
                $purpose = 'Auto issue for SR#'.$sr->service_request_id;

                $pos = PurchaseOrder::with('items')
                    ->where('service_request_id', $sr->service_request_id)
                    ->whereIn('status', ['Completed','completed','Delivered','delivered','Approved','approved'])
                    ->get();

                if ($pos->isEmpty()) { return; }

                $wanted = [];
                foreach ($pos as $po) {
                    foreach ($po->items as $it) {
                        $invId = $it->item_id ? (int)$it->item_id : null;
                        if (!$invId && !empty($it->description)) {
                            $name = trim(mb_strtolower((string)$it->description));
                            $inv = InventoryItem::whereRaw('LOWER(item_name) = ?', [$name])->first();
                            if ($inv) { $invId = (int)$inv->item_id; }
                        }
                        if (!$invId) { continue; }
                        $wanted[$invId] = ($wanted[$invId] ?? 0) + (int)$it->quantity;
                    }
                }

                if (empty($wanted)) { return; }

                $balance = new BalanceService();

                foreach ($wanted as $invId => $qty) {
                    if ($qty <= 0) { continue; }
                    $exists = InventoryStockOut::where('service_request_id', $sr->service_request_id)
                        ->where('item_id', $invId)->exists();
                    if ($exists) { continue; }

                    $avail = optional(InventoryBalance::find($invId))->current_stock ?? 0;
                    $toIssue = min((int)$avail, (int)$qty);
                    if ($toIssue <= 0) { continue; }

                    InventoryStockOut::create([
                        'service_request_id' => $sr->service_request_id,
                        'item_id' => $invId,
                        'quantity' => $toIssue,
                        'issued_to' => null,
                        'issued_date' => $issueDate,
                        'purpose' => $purpose,
                        'remarks' => $toIssue < $qty ? ('Partial issue from available stock (requested '.$qty.', issued '.$toIssue.')') : null,
                    ]);
                    $balance->adjust((int)$invId, -$toIssue);
                }
            });
        } catch (\Throwable $e) {
            // Optional: log the error without breaking SR updates
        }
    }
}

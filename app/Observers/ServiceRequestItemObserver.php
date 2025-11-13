<?php

// app/Observers/ServiceRequestItemObserver.php
namespace App\Observers;
use App\Models\ServiceRequestItem;
use App\Models\ServiceRequestItemExtra;
use App\Models\InventoryItem;
use App\Models\InventoryStockOut;
use App\Models\InventoryBalance;
use App\Services\Inventory\BalanceService;
use App\Models\Billing;
use App\Events\BillingCreated;
use Illuminate\Support\Facades\DB;

class ServiceRequestItemObserver {
    public function updated(ServiceRequestItem $item){
        // Sync parent ServiceRequest.order_status with aggregate item statuses
        if ($item->isDirty('status')) {
            $sr = $item->serviceRequest()->with('items')->first();
            if ($sr) {
                $statuses = collect($sr->items)->pluck('status')->map(fn($s) => strtolower((string)$s));
                $new = 'Pending';
                if ($statuses->every(fn($s) => $s === 'completed')) {
                    $new = 'Completed';
                } elseif ($statuses->contains(fn($s) => in_array($s, ['in progress','in_progress','ongoing','processing'], true))) {
                    $new = 'In Progress';
                } elseif ($statuses->contains(fn($s) => in_array($s, ['cancelled','canceled'], true))) {
                    $new = 'Cancelled';
                } else {
                    $new = 'Pending';
                }
                if (strcasecmp((string)$sr->order_status, $new) !== 0) {
                    $sr->order_status = $new;
                    $sr->save();
                }
            }
        }
        if ($item->isDirty('status') && $item->status === 'Completed') {
            $sr = isset($sr) && $sr ? $sr : $item->serviceRequest()->with('items')->first();
            // check all items completed
            if ($sr && $sr->items->every(fn($i)=> $i->status === 'Completed')) {
                DB::transaction(function () use ($sr) {
                    $completedItemIds = $sr->items->pluck('item_id')->all();
                    $extras = ServiceRequestItemExtra::whereIn('item_id', $completedItemIds)->get();
                    $byName = $extras->groupBy(fn($e) => trim(mb_strtolower($e->name)));
                    $issueDate = optional($sr->accomplishment_date)->toDateString() ?? now()->toDateString();
                    $purpose = 'Auto issue for SR#'.$sr->service_request_id;
                    $balance = new BalanceService();

                    foreach ($byName as $nameKey => $group) {
                        $qty = (int) $group->sum('qty');
                        if ($qty <= 0) { continue; }
                        $inv = InventoryItem::whereRaw('LOWER(item_name) = ?', [$nameKey])->first();
                        if (!$inv) { continue; }

                        $exists = InventoryStockOut::where('service_request_id', $sr->service_request_id)
                            ->where('item_id', $inv->item_id)
                            ->exists();
                        if ($exists) { continue; }

                        $avail = optional(InventoryBalance::find($inv->item_id))->current_stock ?? 0;
                        $toIssue = min((int)$avail, (int)$qty);
                        if ($toIssue <= 0) { continue; }

                        InventoryStockOut::create([
                            'service_request_id' => $sr->service_request_id,
                            'item_id' => $inv->item_id,
                            'quantity' => $toIssue,
                            'issued_to' => null,
                            'issued_date' => $issueDate,
                            'purpose' => $purpose,
                            'remarks' => $toIssue < $qty ? ('Partial issue from available stock (requested '.$qty.', issued '.$toIssue.')') : null,
                        ]);
                        $balance->adjust((int)$inv->item_id, -$toIssue);
                    }
                });
                // compute totals
                $subtotal = $sr->items->sum(fn($i)=> $i->line_total ?? ($i->unit_price * $i->quantity));
                $discount = $sr->overall_discount ?? 0;
                $tax = round($subtotal * 0.12, 2); // example fixed 12% tax — replace with tax logic
                $total = round($subtotal - $discount + $tax, 2);

                $billing = Billing::create([
                    'service_request_id' => $sr->service_request_id,
                    'customer_id' => $sr->customer_id,
                    'subtotal' => $subtotal,
                    'discount' => $discount,
                    'tax' => $tax,
                    'total' => $total,
                    'status' => 'For Invoice',
                    'meta' => ['generated_from' => 'observer', 'items' => $sr->items->pluck('item_id')]
                ]);

                event(new BillingCreated($billing));
            }
        }
    }
}

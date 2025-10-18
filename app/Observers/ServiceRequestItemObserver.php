<?php

// app/Observers/ServiceRequestItemObserver.php
namespace App\Observers;
use App\Models\ServiceRequestItem;
use App\Models\Billing;
use App\Events\BillingCreated;
use Illuminate\Support\Facades\DB;

class ServiceRequestItemObserver {
    public function updated(ServiceRequestItem $item){
        if ($item->isDirty('status') && $item->status === 'Completed') {
            $sr = $item->serviceRequest()->with('items')->first();
            // check all items completed
            if ($sr->items->every(fn($i)=> $i->status === 'Completed')) {
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

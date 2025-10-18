<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class BillingSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        // Create billings for first 3 completed Service Requests
        $serviceRequestIds = DB::table('service_requests')
            ->where('order_status', 'Completed')
            ->orderBy('service_request_id')
            ->limit(3)
            ->pluck('service_request_id');

        foreach ($serviceRequestIds as $srId) {
            $sr = DB::table('service_requests')->where('service_request_id', $srId)->first();
            if (!$sr) continue;

            $items = DB::table('service_request_items')
                ->where('service_request_id', $srId)
                ->where('status', 'Completed')
                ->get();

            if ($items->isEmpty()) continue;

            $subtotal = 0; $discount = 0; $tax = 0;
            foreach ($items as $it) {
                $line = ($it->quantity ?? 1) * (float)$it->unit_price;
                $lineDiscount = (float)($it->discount ?? 0);
                $lineTax = (float)($it->tax ?? 0);
                $extraSum = DB::table('service_request_item_extras')
                    ->where('item_id', $it->item_id)
                    ->get()
                    ->sum(fn($e) => (float)$e->qty * (float)$e->price);
                $subtotal += $line + $extraSum;
                $discount += $lineDiscount;
                $tax += $lineTax;
            }
            $total = round($subtotal - $discount + $tax, 2);

            DB::table('billings')->updateOrInsert(
                ['service_request_id' => $srId],
                [
                    'customer_id' => $sr->customer_id,
                    'billing_date' => $now->toDateString(),
                    'due_date' => $now->copy()->addDays(7)->toDateString(),
                    'total_amount' => $total,
                    'discount' => $discount,
                    'tax' => $tax,
                    'status' => 'Billed',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }
}

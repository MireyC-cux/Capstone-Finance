<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServiceRequestItemSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $items = [];
        $extras = [];
        $id = 1;
        for ($sr=1; $sr<=8; $sr++) {
            // two items each SR
            for ($i=1; $i<=2; $i++) {
                $price = 1000 + ($i*250);
                $discount = $i === 2 ? 50 : 0;
                $tax = round(($price - $discount) * 0.12, 2);
                $items[] = [
                    'item_id' => $id,
                    'service_request_id' => $sr,
                    'services_id' => ($i % 4) + 1,
                    'aircon_type_id' => ($i % 2) + 1,
                    'service_type' => null,
                    'quantity' => 1,
                    'unit_price' => $price,
                    'discount' => $discount,
                    'tax' => $tax,
                    'line_total' => ($price - $discount + $tax),
                    'start_date' => now()->subDays(15),
                    'end_date' => now()->subDays(10),
                    'start_time' => null,
                    'end_time' => null,
                    'assigned_technician_id' => null,
                    'status' => $sr <= 5 ? 'Completed' : 'Pending',
                    'service_notes' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
                // extras
                $extras[] = [
                    'item_id' => $id,
                    'name' => 'Chemical',
                    'qty' => 1,
                    'price' => 150.00,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
                $id++;
            }
        }
        DB::table('service_request_items')->upsert($items, ['item_id']);
        DB::table('service_request_item_extras')->insert($extras);
    }
}

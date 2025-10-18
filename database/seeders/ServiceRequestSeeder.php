<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ServiceRequestSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $rows = [];
        for ($i=1; $i<=8; $i++) {
            $rows[] = [
                'service_request_id' => $i,
                'service_request_number' => 'SR-'.str_pad((string)$i, 5, '0', STR_PAD_LEFT),
                'customer_id' => (($i - 1) % 10) + 1,
                'address_id' => $i, // optional, may not exist
                'order_total' => 0,
                'overall_discount' => 0,
                'type_of_payment' => 'Cash',
                'order_status' => $i <= 5 ? 'Completed' : 'Pending',
                'payment_status' => 'Unpaid',
                'accomplishment_date' => $i <= 5 ? now()->subDays(10 - $i) : null,
                'remarks' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        DB::table('service_requests')->upsert($rows, ['service_request_id']);
    }
}

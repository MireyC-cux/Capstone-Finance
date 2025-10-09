<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BillingsTableSeeder extends Seeder
{
    public function run()
    {
        $billings = [
            [
                'service_request_id' => 1,
                'customer_id' => 1,
                'billing_date' => Carbon::now()->subDays(5),
                'due_date' => Carbon::now()->addDays(15),
                'total_amount' => 5000.00,
                'discount' => 250.00,
                'tax' => 570.00,
                'status' => 'Billed'
            ],
            [
                'service_request_id' => 2,
                'customer_id' => 2,
                'billing_date' => Carbon::now()->subDays(3),
                'due_date' => Carbon::now()->addDays(10),
                'total_amount' => 3500.00,
                'discount' => 0.00,
                'tax' => 420.00,
                'status' => 'Unbilled'
            ],
            [
                'service_request_id' => 3,
                'customer_id' => 3,
                'billing_date' => Carbon::now()->subDay(),
                'due_date' => Carbon::now()->addDays(7),
                'total_amount' => 2800.00,
                'discount' => 140.00,
                'tax' => 319.20,
                'status' => 'Draft'
            ]
        ];

        DB::table('billings')->insert($billings);
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PaymentsReceivedTableSeeder extends Seeder
{
    public function run()
    {
        $payments = [
            // Full payment for AR #1
            [
                'ar_id' => 1,
                'payment_date' => Carbon::now()->subDays(8),
                'amount' => 5000.00,
                'payment_method' => 'Bank Transfer',
                'reference_number' => 'BANK' . rand(100000, 999999),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // Partial payment for AR #2
            [
                'ar_id' => 2,
                'payment_date' => Carbon::now()->subDays(5),
                'amount' => 1500.00,
                'payment_method' => 'GCash',
                'reference_number' => 'GCASH' . rand(100000, 999999),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // Another partial payment for AR #2
            [
                'ar_id' => 2,
                'payment_date' => Carbon::now()->subDays(3),
                'amount' => 1000.00,
                'payment_method' => 'Cash',
                'reference_number' => 'CASH' . rand(1000, 9999),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // Full payment for AR #3 (paid in cash)
            [
                'ar_id' => 3,
                'payment_date' => Carbon::now()->subDay(),
                'amount' => 2800.00,
                'payment_method' => 'Cash',
                'reference_number' => 'CASH' . rand(1000, 9999),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];

        DB::table('payments_received')->insert($payments);
    }
}

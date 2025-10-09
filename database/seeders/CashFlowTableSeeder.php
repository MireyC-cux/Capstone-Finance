<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CashFlowTableSeeder extends Seeder
{
    public function run()
    {
        $cashFlows = [
            // Inflows
            [
                'transaction_type' => 'Inflow',
                'source_type' => 'Invoice Payment',
                'source_id' => 1,
                'amount' => 5000.00,
                'transaction_date' => Carbon::now()->subDays(10),
                'description' => 'Payment for service request #SR-20231001-001',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'transaction_type' => 'Inflow',
                'source_type' => 'Invoice Payment',
                'source_id' => 2,
                'amount' => 3500.00,
                'transaction_date' => Carbon::now()->subDays(5),
                'description' => 'Partial payment for service request #SR-20231001-002',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // Outflows
            [
                'transaction_type' => 'Outflow',
                'source_type' => 'Expense',
                'source_id' => 1,
                'amount' => 1500.00,
                'transaction_date' => Carbon::now()->subDays(8),
                'description' => 'Office supplies and materials',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'transaction_type' => 'Outflow',
                'source_type' => 'Supplier Payment',
                'source_id' => 1,
                'amount' => 5000.00,
                'transaction_date' => Carbon::now()->subDays(3),
                'description' => 'Payment to ABC Supplies for equipment',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'transaction_type' => 'Outflow',
                'source_type' => 'Expense',
                'source_id' => 3,
                'amount' => 5000.00,
                'transaction_date' => Carbon::now()->subDay(),
                'description' => 'Vehicle fuel and maintenance',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];

        DB::table('cash_flow')->insert($cashFlows);
    }
}

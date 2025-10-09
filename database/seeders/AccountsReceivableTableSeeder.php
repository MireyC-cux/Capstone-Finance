<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AccountsReceivableTableSeeder extends Seeder
{
    public function run()
    {
        $receivables = [
            [
                'customer_id' => 1,
                'service_request_id' => 1,
                'invoice_number' => 'AR-' . date('Ymd') . '-001',
                'invoice_date' => Carbon::now()->subDays(10),
                'due_date' => Carbon::now()->addDays(5),
                'total_amount' => 5000.00,
                'amount_paid' => 5000.00,
                'status' => 'Paid',
                'payment_terms' => 'Net 15 Days'
            ],
            [
                'customer_id' => 2,
                'service_request_id' => 2,
                'invoice_number' => 'AR-' . date('Ymd') . '-002',
                'invoice_date' => Carbon::now()->subDays(8),
                'due_date' => Carbon::now()->addDays(7),
                'total_amount' => 3500.00,
                'amount_paid' => 1500.00,
                'status' => 'Partially Paid',
                'payment_terms' => 'Net 15 Days'
            ],
            [
                'customer_id' => 3,
                'service_request_id' => 3,
                'invoice_number' => 'AR-' . date('Ymd') . '-003',
                'invoice_date' => Carbon::now()->subDays(5),
                'due_date' => Carbon::now()->addDays(10),
                'total_amount' => 2800.00,
                'amount_paid' => 0.00,
                'status' => 'Unpaid',
                'payment_terms' => 'Net 15 Days'
            ]
        ];

        DB::table('accounts_receivable')->insert($receivables);
    }
}

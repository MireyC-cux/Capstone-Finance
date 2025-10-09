<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class InvoicesTableSeeder extends Seeder
{
    public function run()
    {
        $invoices = [
            [
                'billing_id' => 1,
                'ar_id' => 1,
                'invoice_number' => 'INV-' . date('Ymd') . '-001',
                'invoice_date' => Carbon::now()->subDays(5),
                'due_date' => Carbon::now()->addDays(10),
                'amount' => 5000.00,
                'status' => 'Paid'
            ],
            [
                'billing_id' => 2,
                'ar_id' => 2,
                'invoice_number' => 'INV-' . date('Ymd') . '-002',
                'invoice_date' => Carbon::now()->subDays(3),
                'due_date' => Carbon::now()->addDays(7),
                'amount' => 3500.00,
                'status' => 'Partially Paid'
            ],
            [
                'billing_id' => 3,
                'ar_id' => null,
                'invoice_number' => 'INV-' . date('Ymd') . '-003',
                'invoice_date' => Carbon::now()->subDay(),
                'due_date' => Carbon::now()->addDays(15),
                'amount' => 2800.00,
                'status' => 'Unpaid'
            ]
        ];

        DB::table('invoices')->insert($invoices);
    }
}

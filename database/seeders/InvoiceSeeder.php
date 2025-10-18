<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InvoiceSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $ars = DB::table('accounts_receivable')->orderBy('ar_id')->limit(3)->get();
        foreach ($ars as $ar) {
            DB::table('invoices')->updateOrInsert(
                ['invoice_number' => $ar->invoice_number],
                [
                    'billing_id' => DB::table('billings')->where('service_request_id', $ar->service_request_id)->value('billing_id'),
                    'ar_id' => $ar->ar_id,
                    'invoice_date' => $ar->invoice_date,
                    'due_date' => $ar->due_date,
                    'amount' => $ar->total_amount,
                    'status' => $ar->status,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }
}

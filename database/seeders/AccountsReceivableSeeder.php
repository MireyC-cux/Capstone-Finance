<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AccountsReceivableSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        // Create AR for first 3 billings
        $billings = DB::table('billings')->orderBy('billing_id')->limit(3)->get();
        foreach ($billings as $b) {
            $invNo = 'INV-SEED-'.str_pad((string)$b->billing_id, 5, '0', STR_PAD_LEFT);
            DB::table('accounts_receivable')->updateOrInsert(
                ['invoice_number' => $invNo],
                [
                    'customer_id' => $b->customer_id,
                    'service_request_id' => $b->service_request_id,
                    'invoice_date' => $now->toDateString(),
                    'due_date' => $now->copy()->addDays(7)->toDateString(),
                    'total_amount' => $b->total_amount,
                    'amount_paid' => 0,
                    'status' => 'Unpaid',
                    'payment_terms' => '7 days',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }
}

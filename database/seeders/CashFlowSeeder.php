<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PaymentReceived;
use App\Models\CashFlow;

class CashFlowSeeder extends Seeder
{
    public function run(): void
    {
        $payments = PaymentReceived::all();
        foreach ($payments as $payment) {
            $exists = CashFlow::where('source_type', 'Invoice Payment')
                ->where('source_id', $payment->payment_id)
                ->exists();
            if (!$exists) {
                CashFlow::create([
                    'transaction_type' => 'Inflow',
                    'source_type' => 'Invoice Payment',
                    'source_id' => $payment->payment_id,
                    'amount' => $payment->amount,
                    'transaction_date' => $payment->payment_date,
                    'description' => 'Seeded inflow for payment #' . $payment->payment_id,
                ]);
            }
        }
    }
}

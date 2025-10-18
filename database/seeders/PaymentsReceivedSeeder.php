<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use App\Models\AccountsReceivable;
use App\Models\PaymentReceived;

class PaymentsReceivedSeeder extends Seeder
{
    public function run(): void
    {
        $ars = AccountsReceivable::inRandomOrder()->limit(30)->get();
        foreach ($ars as $ar) {
            // Create 0-2 payments per AR
            $paymentsCount = rand(0, 2);
            $remaining = max(0, (float)$ar->total_amount - (float)$ar->amount_paid);
            for ($i = 0; $i < $paymentsCount && $remaining > 0.0; $i++) {
                $amount = round(min($remaining, rand(500, 3000)), 2);
                $payment = PaymentReceived::create([
                    'ar_id' => $ar->ar_id,
                    'payment_date' => now()->subDays(rand(0, 45))->toDateString(),
                    'amount' => $amount,
                    'payment_method' => collect(['Cash','Bank Transfer','GCash','Check'])->random(),
                    'reference_number' => 'REF-'.strtoupper(uniqid()),
                ]);
                $ar->amount_paid = round($ar->amount_paid + $amount, 2);
                $ar->status = $ar->amount_paid >= $ar->total_amount ? 'Paid' : 'Partially Paid';
                $ar->save();
                $remaining = max(0, (float)$ar->total_amount - (float)$ar->amount_paid);
            }
        }
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\AccountsPayable;
use App\Models\PaymentMade;
use App\Models\CashFlow;

class PaymentsMadeSeeder extends Seeder
{
    public function run(): void
    {
        $aps = AccountsPayable::inRandomOrder()->take(10)->get();
        foreach ($aps as $ap) {
            // create 1-2 payments per AP without exceeding total
            $paymentsCount = rand(1, 2);
            $remaining = (float)$ap->total_amount - (float)$ap->amount_paid;
            for ($p=0; $p<$paymentsCount && $remaining > 0.01; $p++) {
                $amount = min($remaining, rand(500, 5000) / 1.0);
                $payment = PaymentMade::create([
                    'ap_id' => $ap->ap_id,
                    'payment_date' => Carbon::now()->subDays(rand(0, 30))->toDateString(),
                    'amount' => number_format($amount,2,'.',''),
                    'payment_method' => 'Cash',
                    'reference_number' => null,
                ]);
                $ap->amount_paid = number_format(((float)$ap->amount_paid + $amount), 2, '.', '');
                $ap->status = $this->deriveApStatus($ap->total_amount, $ap->amount_paid, $ap->due_date);
                $ap->save();

                CashFlow::create([
                    'transaction_type' => 'Outflow',
                    'source_type' => 'Supplier Payment',
                    'source_id' => $payment->payment_id,
                    'amount' => $payment->amount,
                    'transaction_date' => $payment->payment_date,
                    'description' => 'Seeded supplier payment for AP #'.$ap->ap_id,
                ]);

                $remaining = (float)$ap->total_amount - (float)$ap->amount_paid;
            }
        }
    }

    protected function deriveApStatus($total, $paid, $dueDate): string
    {
        if ($paid <= 0) return 'Unpaid';
        if ($paid > 0 && $paid + 0.0001 < $total) return Carbon::parse($dueDate)->isPast() ? 'Overdue' : 'Partially Paid';
        if ($paid + 0.0001 >= $total) return 'Paid';
        return 'Unpaid';
    }
}

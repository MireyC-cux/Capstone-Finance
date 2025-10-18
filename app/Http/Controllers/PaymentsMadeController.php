<?php

namespace App\Http\Controllers;

use App\Models\AccountsPayable;
use App\Models\PaymentMade;
use App\Models\CashFlow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PaymentsMadeController extends Controller
{
    public function index()
    {
        $payments = PaymentMade::with('accountsPayable.supplier')->orderByDesc('payment_id')->paginate(25);
        return view('finance.ap.payments.history', compact('payments'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'ap_id' => 'required|integer|exists:accounts_payable,ap_id',
            'payment_date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string|in:Cash,Bank Transfer,Check,Other',
            'reference_number' => 'nullable|string|max:255',
            'account_id' => 'nullable|integer|exists:cash_accounts,account_id',
        ]);

        return DB::transaction(function () use ($data) {
            $ap = AccountsPayable::lockForUpdate()->findOrFail($data['ap_id']);

            $newPaid = (float)$ap->amount_paid + (float)$data['amount'];
            if ($newPaid - (float)$ap->total_amount > 0.0001) {
                return back()->with('error', 'Payment exceeds the remaining balance.');
            }

            $payment = PaymentMade::create([
                'ap_id' => $ap->ap_id,
                'payment_date' => Carbon::parse($data['payment_date'])->toDateString(),
                'amount' => number_format((float)$data['amount'], 2, '.', ''),
                'payment_method' => $data['payment_method'],
                'reference_number' => $data['reference_number'] ?? null,
            ]);

            $ap->amount_paid = number_format($newPaid, 2, '.', '');
            $ap->status = $this->deriveApStatus($ap->total_amount, $ap->amount_paid, $ap->due_date);
            $ap->save();

            CashFlow::create([
                'transaction_type' => 'Outflow',
                'source_type' => 'Supplier Payment',
                'source_id' => $payment->payment_id,
                'account_id' => $data['account_id'] ?? null,
                'amount' => $payment->amount,
                'transaction_date' => $payment->payment_date,
                'description' => 'Payment to supplier for AP #' . $ap->ap_id,
            ]);

            return redirect()->back()->with('success', 'Supplier payment recorded successfully.');
        });
    }

    protected function deriveApStatus($total, $paid, $dueDate): string
    {
        if ($paid <= 0) return 'Unpaid';
        if ($paid > 0 && $paid + 0.0001 < $total) return Carbon::parse($dueDate)->isPast() ? 'Overdue' : 'Partially Paid';
        if ($paid + 0.0001 >= $total) return 'Paid';
        return 'Unpaid';
    }
}

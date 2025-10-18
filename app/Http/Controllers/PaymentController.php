<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use App\Models\AccountsReceivable;
use App\Models\PaymentReceived;
use App\Models\Invoice;
use App\Models\CashFlow;
use App\Models\ActivityLog;

class PaymentController extends Controller
{
    /**
     * Store manual payments (Cash, Bank Transfer, Check)
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'ar_id' => 'required|integer|exists:accounts_receivable,ar_id',
            'payment_date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string|max:50',
            'reference_number' => 'nullable|string|max:100',
            'account_id' => 'nullable|integer|exists:cash_accounts,account_id',
        ]);

        // Normalize payment method to exact enum values
        $method = strtolower(trim($data['payment_method']));
        $data['payment_method'] = match ($method) {
            'gcash' => 'GCash',
            'bank transfer', 'bank_transfer', 'bank' => 'Bank Transfer',
            'check', 'cheque' => 'Check',
            default => 'Cash',
        };

        return DB::transaction(function () use ($data) {
            $ar = AccountsReceivable::findOrFail($data['ar_id']);

            $payment = PaymentReceived::create([
                'ar_id' => $ar->ar_id,
                'payment_date' => Carbon::parse($data['payment_date'])->toDateString(),
                'amount' => $data['amount'],
                'payment_method' => $data['payment_method'],
                'reference_number' => $data['reference_number'] ?? null,
            ]);

            $ar->amount_paid = round($ar->amount_paid + $payment->amount, 2);
            $ar->status = $this->deriveArStatus($ar->total_amount, $ar->amount_paid, $ar->due_date);
            $ar->save();

            // Update linked invoice if any
            $invoice = Invoice::where('ar_id', $ar->ar_id)->first();
            if ($invoice) {
                $invoice->status = $this->deriveInvoiceStatus($ar->total_amount, $ar->amount_paid, $ar->due_date);
                $invoice->save();
            }

            // Record cash inflow in cashflow
            CashFlow::create([
                'transaction_type' => 'Inflow',
                'source_type' => 'Invoice Payment',
                'source_id' => $payment->payment_id,
                'account_id' => $data['account_id'] ?? null,
                'amount' => $payment->amount,
                'transaction_date' => $payment->payment_date,
                'description' => 'Payment received for AR #' . $ar->ar_id,
            ]);

            // Activity: AR payment recorded
            ActivityLog::create([
                'event_type' => 'ar_payment',
                'title' => 'Payment of ₱'.number_format((float)$payment->amount, 2).' recorded for AR #'.$ar->ar_id,
                'context_type' => 'AccountsReceivable',
                'context_id' => $ar->ar_id,
                'amount' => $payment->amount,
                'meta' => [
                    'payment_id' => $payment->payment_id,
                    'method' => $payment->payment_method,
                    'reference' => $payment->reference_number,
                ],
            ]);

            return redirect()->back()->with('success', 'Payment recorded successfully.');
        });
    }

    /**
     * Create a GCash Source via PayMongo and return checkout URL (Test Mode)
     */
    public function createGCashSource(Request $request)
    {
        $request->validate([
            'ar_id' => 'required|integer|exists:accounts_receivable,ar_id',
            'amount' => 'required|numeric|min:0.01',
        ]);

        $ar = AccountsReceivable::findOrFail($request->ar_id);
        $secret = env('PAYMONGO_SECRET_KEY');
        $amount = (int)($request->amount * 100);

        $response = Http::withBasicAuth($secret, '')
            ->post(env('PAYMONGO_API_URL', 'https://api.paymongo.com/v1') . '/sources', [
                'data' => [
                    'attributes' => [
                        'amount' => $amount,
                        'currency' => 'PHP',
                        'type' => 'gcash',
                        'redirect' => [
                            'success' => route('payments.gcash.success', ['ar_id' => $ar->ar_id]),
                            'failed' => route('payments.gcash.failed', ['ar_id' => $ar->ar_id]),
                        ],
                    ],
                ],
            ])
            ->json();

        $checkoutUrl = $response['data']['attributes']['redirect']['checkout_url'] ?? null;

        if (!$checkoutUrl) {
            return response()->json(['error' => 'Unable to generate GCash QR link.'], 500);
        }

        return response()->json([
            'checkout_url' => $checkoutUrl,
        ]);
    }

    /**
     * Create a GCash Payment via PayMongo API (Test Mode)
     */
    public function payWithGCash(Request $request)
    {
        $request->validate([
            'ar_id' => 'required|integer|exists:accounts_receivable,ar_id',
            'amount' => 'required|numeric|min:0.01',
        ]);

        $ar = AccountsReceivable::findOrFail($request->ar_id);
        $amount = $request->amount * 100; // Convert PHP to centavos
        $secret = env('PAYMONGO_SECRET_KEY');

        // 1️⃣ Create a Payment Intent
        $intent = Http::withBasicAuth($secret, '')
            ->post(env('PAYMONGO_API_URL', 'https://api.paymongo.com/v1') . '/payment_intents', [
                'data' => [
                    'attributes' => [
                        'amount' => $amount,
                        'payment_method_allowed' => ['gcash'],
                        'currency' => 'PHP',
                        'description' => "GCash payment for AR #{$ar->ar_id}",
                    ],
                ],
            ])
            ->json();

        $intentId = $intent['data']['id'] ?? null;

        if (!$intentId) {
            return back()->with('error', 'Unable to create payment intent.');
        }

        // 2️⃣ Create GCash payment method
        $method = Http::withBasicAuth($secret, '')
            ->post(env('PAYMONGO_API_URL', 'https://api.paymongo.com/v1') . '/payment_methods', [
                'data' => [
                    'attributes' => [
                        'type' => 'gcash',
                    ],
                ],
            ])
            ->json();

        $methodId = $method['data']['id'] ?? null;

        if (!$methodId) {
            return back()->with('error', 'Unable to create GCash payment method.');
        }

        // 3️⃣ Attach payment method to payment intent
        $attach = Http::withBasicAuth($secret, '')
            ->post(env('PAYMONGO_API_URL', 'https://api.paymongo.com/v1') . "/payment_intents/{$intentId}/attach", [
                'data' => [
                    'attributes' => [
                        'payment_method' => $methodId,
                        'return_url' => route('payments.gcash.success', ['ar_id' => $ar->ar_id]),
                    ],
                ],
            ])
            ->json();

        // 4️⃣ Redirect to GCash checkout URL
        $redirectUrl = $attach['data']['attributes']['next_action']['redirect']['url'] ?? null;

        if ($redirectUrl) {
            return redirect()->away($redirectUrl);
        }

        return back()->with('error', 'Unable to redirect to GCash checkout.');
    }

    /**
     * Handle return after GCash payment (success)
     */
    public function gcashSuccess(Request $request)
    {
        $ar = AccountsReceivable::find($request->ar_id);
        if (!$ar) {
            return redirect()->route('payments.history')->with('error', 'Accounts Receivable not found.');
        }

        // Simulate marking as paid (for test mode)
        $payment = PaymentReceived::create([
            'ar_id' => $ar->ar_id,
            'payment_date' => now(),
            'amount' => $ar->total_amount - $ar->amount_paid,
            'payment_method' => 'GCash',
            'reference_number' => 'GCASH-' . strtoupper(uniqid()),
        ]);

        $ar->amount_paid += $payment->amount;
        $ar->status = $this->deriveArStatus($ar->total_amount, $ar->amount_paid, $ar->due_date);
        $ar->save();

        // Log to CashFlow
        CashFlow::create([
            'transaction_type' => 'Inflow',
            'source_type' => 'Invoice Payment',
            'source_id' => $payment->payment_id,
            'amount' => $payment->amount,
            'transaction_date' => $payment->payment_date,
            'description' => 'GCash payment for AR #' . $ar->ar_id,
        ]);

        // Activity: AR payment recorded (GCash)
        ActivityLog::create([
            'event_type' => 'ar_payment',
            'title' => 'GCash payment of ₱'.number_format((float)$payment->amount, 2).' recorded for AR #'.$ar->ar_id,
            'context_type' => 'AccountsReceivable',
            'context_id' => $ar->ar_id,
            'amount' => $payment->amount,
            'meta' => [
                'payment_id' => $payment->payment_id,
                'method' => $payment->payment_method,
                'reference' => $payment->reference_number,
            ],
        ]);

        return redirect()->route('payments.history')->with('success', 'GCash payment completed successfully (Test Mode).');
    }

    public function gcashFailed(Request $request)
    {
        return redirect()->route('payments.history')->with('error', 'GCash payment was cancelled or failed.');
    }

    /**
     * Display payment history
     */
    public function history()
    {
        $payments = PaymentReceived::with('accountsReceivable')->orderByDesc('payment_id')->paginate(25);
        return view('finance.payments.history', compact('payments'));
    }

    /**
     * Derive Accounts Receivable Status
     */
    protected function deriveArStatus($total, $paid, $dueDate): string
    {
        if ($paid <= 0) return 'Unpaid';
        if ($paid > 0 && $paid < $total) return Carbon::parse($dueDate)->isPast() ? 'Overdue' : 'Partially Paid';
        if ($paid >= $total) return 'Paid';
        return 'Unpaid';
    }

    /**
     * Derive Invoice Status
     */
    protected function deriveInvoiceStatus($total, $paid, $dueDate): string
    {
        if ($paid <= 0) return 'Unpaid';
        if ($paid > 0 && $paid < $total) return Carbon::parse($dueDate)->isPast() ? 'Overdue' : 'Partially Paid';
        if ($paid >= $total) return 'Paid';
        return 'Unpaid';
    }
}

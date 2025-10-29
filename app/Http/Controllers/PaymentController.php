<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use App\Models\AccountsReceivable;
use App\Models\PaymentReceived;
use App\Models\Invoice;
use App\Models\CashFlow;
use App\Models\ActivityLog;
use App\Models\PaymentHistory;

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
            'payment_type' => 'required|in:Full,Partial',
            'reference_number' => 'nullable|string|max:100|required_unless:payment_method,Cash',
            'or_file' => 'nullable|file|mimes:png,jpg,jpeg,gif,bmp,webp,svg,pdf|max:4096|required_unless:payment_method,Cash',
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

        return DB::transaction(function () use ($data, $request) {
            $ar = AccountsReceivable::findOrFail($data['ar_id']);

            // Determine outstanding and enforce payment_type rules
            $outstanding = max(0.0, (float)$ar->total_amount - (float)$ar->amount_paid);
            if (strcasecmp($data['payment_type'], 'Full') === 0) {
                // Force amount to outstanding to avoid mismatch
                $data['amount'] = $outstanding;
            } else {
                // Partial: must be >0 and < outstanding
                if (!($data['amount'] > 0 && $data['amount'] < $outstanding)) {
                    return back()->with('error', 'Partial payment must be greater than 0 and less than the outstanding balance.');
                }
            }

            // Handle OR upload (if provided)
            $orPath = null;
            if ($request->hasFile('or_file')) {
                $orPath = $request->file('or_file')->store('or_uploads', 'public');
            }

            $paymentData = [
                'ar_id' => $ar->ar_id,
                'payment_date' => Carbon::parse($data['payment_date'])->toDateString(),
                'amount' => $data['amount'],
                'payment_method' => $data['payment_method'],
                'reference_number' => $data['reference_number'] ?? null,
            ];
            if (Schema::hasColumn('payments_received', 'payment_type')) {
                $paymentData['payment_type'] = $data['payment_type'];
            }
            if ($orPath && Schema::hasColumn('payments_received', 'or_file_path')) {
                $paymentData['or_file_path'] = $orPath;
            }
            $payment = PaymentReceived::create($paymentData);

            $ar->amount_paid = round($ar->amount_paid + $payment->amount, 2);
            $ar->status = $this->deriveArStatus($ar->total_amount, $ar->amount_paid, $ar->due_date);
            if ($ar->status === 'Partially Paid') {
                $ar->due_date = Carbon::parse($ar->due_date)->addDays(15)->toDateString();
            }
            $ar->save();

            // Update linked invoice if any
            $invoice = Invoice::where('ar_id', $ar->ar_id)->first();
            if ($invoice) {
                $invoice->status = $this->deriveInvoiceStatus($ar->total_amount, $ar->amount_paid, $ar->due_date);
                if ($invoice->status === 'Partially Paid') {
                    $invoice->due_date = Carbon::parse($invoice->due_date)->addDays(15)->toDateString();
                }
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

            // Log to payment_history as well
            $billingId = \App\Models\Billing::where('service_request_id', $ar->service_request_id)
                ->orderByDesc('billing_id')->value('billing_id');
            $histData = [
                'billing_id' => $billingId,
                'service_request_id' => $ar->service_request_id,
                'payment_date' => $payment->payment_date,
                'due_date' => $ar->due_date,
                'type_of_payment' => $data['payment_method'],
                'amount' => $payment->amount,
                'status' => $ar->status,
            ];
            if ($orPath && Schema::hasColumn('payment_history', 'or_file_path')) {
                $histData['or_file_path'] = $orPath;
            }
            PaymentHistory::create($histData);

            // Generate PDF receipt and store it publicly
            try {
                $ar->loadMissing('customer');
                $balanceBefore = $outstanding;
                $balanceAfter = max(0.0, (float)$ar->total_amount - (float)$ar->amount_paid);
                $invoice = $invoice ?? Invoice::where('ar_id', $ar->ar_id)->first();
                $html = view('finance.payments.receipt', [
                    'payment' => $payment,
                    'ar' => $ar,
                    'invoice' => $invoice,
                    'customer' => $ar->customer ?? null,
                    'balanceBefore' => $balanceBefore,
                    'balanceAfter' => $balanceAfter,
                ])->render();
                $pdf = Pdf::setOptions(['defaultFont' => 'DejaVu Sans', 'isRemoteEnabled' => true])->loadHTML($html)->setPaper('A5', 'portrait');
                $dir = 'payment_receipts';
                if (!Storage::disk('public')->exists($dir)) {
                    Storage::disk('public')->makeDirectory($dir);
                }
                $file = $dir.'/Receipt_'.$payment->payment_id.'.pdf';
                Storage::disk('public')->put($file, $pdf->output());
                $receiptUrl = asset('storage/'.$file);
                return redirect()->back()->with('success', 'Payment recorded successfully.')->with('receipt_url', $receiptUrl);
            } catch (\Throwable $e) {
                // Fallback: if PDF generation fails, still succeed without the receipt
                return redirect()->back()->with('success', 'Payment recorded successfully.');
            }
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
        if ($ar->status === 'Partially Paid') {
            $ar->due_date = Carbon::parse($ar->due_date)->addDays(15)->toDateString();
        }
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

        // Also update invoice due date if partially paid
        $invoice = Invoice::where('ar_id', $ar->ar_id)->first();
        if ($invoice) {
            $invoice->status = $this->deriveInvoiceStatus($ar->total_amount, $ar->amount_paid, $ar->due_date);
            if ($invoice->status === 'Partially Paid') {
                $invoice->due_date = Carbon::parse($invoice->due_date)->addDays(15)->toDateString();
            }
            $invoice->save();
        }

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

<?php

// app/Jobs/GenerateInvoiceJob.php
namespace App\Jobs;
use App\Models\Billing;
use App\Models\Invoice;
use App\Models\AccountsReceivable;
use App\Models\InvoiceSequence;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use PDF; // from barryvdh/laravel-dompdf

class GenerateInvoiceJob implements ShouldQueue {
    use Queueable, SerializesModels;

    public $billing;
    public function __construct(Billing $billing){ $this->billing = $billing; }

    public function handle(){
        // 1) create invoice number safely (DB transaction)
        $invoiceNumber = null;
        \DB::transaction(function () use (&$invoiceNumber) {
            $year = now()->year;
            $seq = \DB::table('invoice_sequences')->where('year',$year)->lockForUpdate()->first();
            if (!$seq) {
                \DB::table('invoice_sequences')->insert(['year'=>$year,'counter'=>1,'created_at'=>now(),'updated_at'=>now()]);
                $counter = 1;
            } else {
                $counter = $seq->counter + 1;
                \DB::table('invoice_sequences')->where('year',$year)->update(['counter'=>$counter,'updated_at'=>now()]);
            }
            $invoiceNumber = sprintf('INV-%d-%04d', $year, $counter);
        });

        // 2) create AR (due date default 30 days)
        $dueDate = now()->addDays(30)->toDateString();

        $ar = AccountsReceivable::create([
            'customer_id' => $this->billing->customer_id,
            'service_request_id' => $this->billing->service_request_id,
            'invoice_number' => $invoiceNumber,
            'invoice_date' => now()->toDateString(),
            'due_date' => $dueDate,
            'total_amount' => $this->billing->total,
            'amount_paid' => 0,
            'payment_terms' => 'Net 30'
        ]);

        // 3) create invoice row
        $invoice = Invoice::create([
            'billing_id' => $this->billing->billing_id,
            'ar_id' => $ar->ar_id,
            'invoice_number' => $invoiceNumber,
            'invoice_date' => now()->toDateString(),
            'due_date' => $dueDate,
            'amount' => $this->billing->total,
            'status' => 'Unpaid'
        ]);

        // 4) update billing status
        $this->billing->update(['status' => 'Invoiced']);

        // 5) generate PDF
        $pdfData = view('finance.invoices.template', ['billing'=>$this->billing, 'invoice'=>$invoice])->render();
        $pdf = PDF::loadHTML($pdfData);

        $filename = "invoices/{$invoiceNumber}.pdf";
        Storage::put($filename, $pdf->output());

        // 6) optionally store file path in invoice meta (or invoices table)
        $invoice->update(['meta' => json_encode(['pdf_path'=>$filename])]);
    }
}

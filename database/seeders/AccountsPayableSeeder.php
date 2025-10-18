<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\AccountsPayable;
use App\Models\Supplier;
use App\Models\PurchaseOrder;

class AccountsPayableSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure suppliers and POs exist
        if (Supplier::count() === 0) {
            $this->call(SupplierSeeder::class);
        }
        if (PurchaseOrder::count() === 0) {
            $this->call(PurchaseOrderSeeder::class);
        }

        // Create APs from some existing POs
        $pos = PurchaseOrder::orderBy('po_id')->take(8)->get();
        foreach ($pos as $po) {
            // Avoid duplicate AP for PO if already created
            $existing = AccountsPayable::where('purchase_order_id', $po->po_id)->first();
            if ($existing) continue;

            $invNo = $this->invoiceNumber();
            $invoiceDate = Carbon::parse($po->po_date);
            $dueDate = (clone $invoiceDate)->addDays(30);

            AccountsPayable::create([
                'supplier_id' => $po->supplier_id,
                'purchase_order_id' => $po->po_id,
                'invoice_number' => $invNo,
                'invoice_date' => $invoiceDate->toDateString(),
                'due_date' => $dueDate->toDateString(),
                'total_amount' => $po->total_amount,
                'amount_paid' => 0,
                'status' => 'Unpaid',
                'payment_terms' => 'NET 30',
            ]);
        }

        // Create a few standalone APs (not linked to any PO)
        $supplierIds = Supplier::pluck('supplier_id')->all();
        for ($i=0; $i<5; $i++) {
            $supplierId = $supplierIds[array_rand($supplierIds)];
            $total = rand(2000, 20000) / 1.0;
            $invoiceDate = Carbon::now()->subDays(rand(0, 45));
            $dueDate = (clone $invoiceDate)->addDays(30);

            AccountsPayable::create([
                'supplier_id' => $supplierId,
                'purchase_order_id' => null,
                'invoice_number' => $this->invoiceNumber(),
                'invoice_date' => $invoiceDate->toDateString(),
                'due_date' => $dueDate->toDateString(),
                'total_amount' => number_format($total,2,'.',''),
                'amount_paid' => 0,
                'status' => 'Unpaid',
                'payment_terms' => 'NET 30',
            ]);
        }
    }

    protected function invoiceNumber(): string
    {
        $date = now()->format('Ymd');
        $seq = AccountsPayable::whereDate('created_at', now()->toDateString())->count() + 1;
        return sprintf('INV-%s-%04d', $date, $seq);
    }
}

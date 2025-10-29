<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Supplier;
use App\Models\ServiceRequest;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;

class PurchaseOrderSeeder extends Seeder
{
    public function run(): void
    {
        if (PurchaseOrder::count() >= 10) return;

        $suppliers = Supplier::pluck('supplier_id')->all();
        $srs = ServiceRequest::pluck('service_request_id')->all();
        if (empty($suppliers)) {
            $this->call(SupplierSeeder::class);
            $suppliers = Supplier::pluck('supplier_id')->all();
        }

        for ($i=1; $i<=10; $i++) {
            $supplierId = $suppliers[array_rand($suppliers)];
            $srId = $srs ? $srs[array_rand($srs)] : null;
            $po = PurchaseOrder::create([
                'supplier_id' => $supplierId,
                'service_request_id' => $srId,
                'po_number' => $this->poNumber(),
                'po_date' => Carbon::now()->subDays(rand(0,45))->toDateString(),
                'status' => 'Pending',
                'total_amount' => 0,
                'created_by' => null,
                'remarks' => 'Seeded PO',
            ]);

            $lines = rand(2, 4);
            $total = 0.0;
            for ($l=0; $l<$lines; $l++) {
                $qty = rand(1,3);
                $price = rand(500, 5000) / 1.0;
                $total += $qty * $price;
                PurchaseOrderItem::create([
                    'purchase_order_id' => $po->purchase_order_idase_order_id,
                    'item_id' => null,
                    'description' => 'Seeded Item '.($l+1),
                    'quantity' => $qty,
                    'unit_price' => number_format($price,2,'.',''),
                ]);
            }
            $po->total_amount = number_format($total,2,'.','');
            $po->save();
        }
    }

    protected function poNumber(): string
    {
        $date = now()->format('Ymd');
        $seq = (PurchaseOrder::whereDate('created_at', now()->toDateString())->count() + 1);
        return sprintf('PO-%s-%04d', $date, $seq);
    }
}

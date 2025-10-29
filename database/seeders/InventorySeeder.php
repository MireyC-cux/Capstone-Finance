<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
class InventorySeeder extends Seeder
{
    public function run(): void
    {
        // Items
        $items = [
            ['item_name'=>'Split-Type Aircon 1.5HP','category'=>'Aircon Unit','brand'=>'CoolWind','model'=>'CW-15S','unit'=>'unit','reorder_level'=>2,'unit_cost'=>25000,'selling_price'=>32000,'status'=>'active'],
            ['item_name'=>'Compressor (1.5HP Compatible)','category'=>'Spare Part','brand'=>'ArcticParts','model'=>'AP-C15','unit'=>'pcs','reorder_level'=>3,'unit_cost'=>8000,'selling_price'=>11000,'status'=>'active'],
            ['item_name'=>'Copper Tube 1/4"','category'=>'Material','brand'=>'TubePro','model'=>'TP-025','unit'=>'roll','reorder_level'=>5,'unit_cost'=>1500,'selling_price'=>2200,'status'=>'active'],
            ['item_name'=>'Refrigerant R410A','category'=>'Consumable','brand'=>'FreezeMax','model'=>'FM-410','unit'=>'kg','reorder_level'=>10,'unit_cost'=>600,'selling_price'=>900,'status'=>'active'],
        ];

        foreach ($items as $it) {
            $id = DB::table('inventory_items')->insertGetId(array_merge($it, [
                'created_at'=>now(),'updated_at'=>now()
            ]), 'item_id');
            // Ensure balance row exists
            DB::table('inventory_balances')->updateOrInsert(
                ['item_id'=>$id],
                ['current_stock'=>0,'last_updated'=>now()]
            );
        }

        $map = DB::table('inventory_items')->pluck('item_id','item_name');

        // Stock-In samples
        $ins = [
            ['purchase_order_id'=>null,'item_name'=>'Split-Type Aircon 1.5HP','quantity'=>3,'unit_cost'=>25000,'received_date'=>Carbon::now()->subDays(7)->toDateString(),'remarks'=>'Initial stock'],
            ['purchase_order_id'=>null,'item_name'=>'Compressor (1.5HP Compatible)','quantity'=>6,'unit_cost'=>8000,'received_date'=>Carbon::now()->subDays(6)->toDateString(),'remarks'=>'Initial stock'],
            ['purchase_order_id'=>null,'item_name'=>'Copper Tube 1/4"','quantity'=>10,'unit_cost'=>1500,'received_date'=>Carbon::now()->subDays(5)->toDateString(),'remarks'=>'Initial stock'],
            ['purchase_order_id'=>null,'item_name'=>'Refrigerant R410A','quantity'=>20,'unit_cost'=>600,'received_date'=>Carbon::now()->subDays(5)->toDateString(),'remarks'=>'Initial stock'],
        ];
        foreach ($ins as $row) {
            $itemId = $map[$row['item_name']] ?? null;
            if (!$itemId) continue;
            DB::table('inventory_stock_in')->insert([
                'purchase_order_id'=>$row['purchase_order_id'],
                'item_id'=>$itemId,
                'quantity'=>$row['quantity'],
                'unit_cost'=>$row['unit_cost'],
                'received_date'=>$row['received_date'],
                'received_by'=>null,
                'remarks'=>$row['remarks'],
                'created_at'=>now(),'updated_at'=>now()
            ]);
            DB::table('inventory_balances')->where('item_id',$itemId)->increment('current_stock', $row['quantity']);
            DB::table('inventory_balances')->where('item_id',$itemId)->update(['last_updated'=>now()]);
        }

        // Stock-Out samples
        $outs = [
            ['service_request_id'=>null,'item_name'=>'Refrigerant R410A','quantity'=>5,'issued_to'=>null,'issued_date'=>Carbon::now()->subDays(3)->toDateString(),'purpose'=>'Maintenance','remarks'=>'Refill'],
            ['service_request_id'=>null,'item_name'=>'Copper Tube 1/4"','quantity'=>2,'issued_to'=>null,'issued_date'=>Carbon::now()->subDays(2)->toDateString(),'purpose'=>'Installation','remarks'=>''],
        ];
        foreach ($outs as $row) {
            $itemId = $map[$row['item_name']] ?? null;
            if (!$itemId) continue;
            DB::table('inventory_stock_out')->insert([
                'service_request_id'=>$row['service_request_id'],
                'item_id'=>$itemId,
                'quantity'=>$row['quantity'],
                'issued_to'=>$row['issued_to'],
                'issued_date'=>$row['issued_date'],
                'purpose'=>$row['purpose'],
                'remarks'=>$row['remarks'],
                'created_at'=>now(),'updated_at'=>now()
            ]);
            DB::table('inventory_balances')->where('item_id',$itemId)->decrement('current_stock', $row['quantity']);
            DB::table('inventory_balances')->where('item_id',$itemId)->update(['last_updated'=>now()]);
        }
    }
}

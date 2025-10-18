<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AirconTypeSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $rows = [
            ['aircon_type_id'=>1,'name'=>'Window Type','brand'=>'Generic','capacity'=>'1.0 HP','model'=>null,'category'=>'Window','base_price'=>2000,'status'=>'active'],
            ['aircon_type_id'=>2,'name'=>'Split Type','brand'=>'Generic','capacity'=>'1.5 HP','model'=>null,'category'=>'Split','base_price'=>3500,'status'=>'active'],
        ];
        foreach ($rows as &$r) { $r['created_at']=$now; $r['updated_at']=$now; }
        DB::table('aircon_types')->upsert($rows, ['aircon_type_id']);
    }
}

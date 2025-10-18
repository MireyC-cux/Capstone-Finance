<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $services = [
            ['services_id'=>1,'service_type'=>'Cleaning','status'=>'active'],
            ['services_id'=>2,'service_type'=>'Repair','status'=>'active'],
            ['services_id'=>3,'service_type'=>'Installation','status'=>'active'],
            ['services_id'=>4,'service_type'=>'Maintenance','status'=>'active'],
        ];
        foreach ($services as &$s) { $s['created_at']=$now; $s['updated_at']=$now; }
        DB::table('services')->upsert($services, ['services_id']);
    }
}

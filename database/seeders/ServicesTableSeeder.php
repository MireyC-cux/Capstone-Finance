<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServicesTableSeeder extends Seeder
{
    public function run()
    {
        $services = [
            [
                'service_type' => 'Regular Maintenance',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'service_type' => 'Repair',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'service_type' => 'Installation',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('services')->insert($services);
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AirconTypesTableSeeder extends Seeder
{
    public function run()
    {
        $airconTypes = [
            [
                'name' => 'Window Type',
                'description' => 'Standard window air conditioning unit',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Split Type',
                'description' => 'Split type air conditioning system',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Inverter',
                'description' => 'Inverter type air conditioning system',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Portable',
                'description' => 'Portable air conditioning unit',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('aircon_types')->insert($airconTypes);
    }
}

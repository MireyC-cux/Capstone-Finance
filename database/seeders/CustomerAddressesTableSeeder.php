<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CustomerAddressesTableSeeder extends Seeder
{
    public function run()
    {
        $addresses = [
            [
                'customer_id' => 1,
                'label' => 'Home',
                'street_address' => '123 Rizal Street',
                'barangay' => 'Poblacion',
                'city' => 'Manila',
                'province' => 'Metro Manila',
                'zip_code' => '1000',
                'is_default' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'customer_id' => 2,
                'label' => 'Business',
                'street_address' => '456 Bonifacio Avenue',
                'barangay' => 'San Antonio',
                'city' => 'Makati',
                'province' => 'Metro Manila',
                'zip_code' => '1200',
                'is_default' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'customer_id' => 3,
                'label' => 'Branch',
                'street_address' => '789 Roxas Boulevard',
                'barangay' => 'Ermita',
                'city' => 'Manila',
                'province' => 'Metro Manila',
                'zip_code' => '1000',
                'is_default' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];

        DB::table('customer_addresses')->insert($addresses);
    }
}

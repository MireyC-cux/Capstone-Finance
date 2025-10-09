<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CustomersTableSeeder extends Seeder
{
    public function run()
    {
        $customers = [
            [
                'full_name' => 'Juan Dela Cruz',
                'email' => 'juan.delacruz@example.com',
                'business_name' => 'Juan\'s Sari-sari Store',
                'contact_info' => '09123456789',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'full_name' => 'Maria Santos',
                'email' => 'maria.santos@example.com',
                'business_name' => 'Santos Family Restaurant',
                'contact_info' => '09234567890',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'full_name' => 'Pedro Reyes',
                'email' => 'pedro.reyes@example.com',
                'business_name' => 'Reyes Hardware',
                'contact_info' => '09345678901',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];

        DB::table('customers')->insert($customers);
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $rows = [];
        for ($i=1; $i<=10; $i++) {
            $rows[] = [
                'customer_id' => $i,
                'full_name' => 'Customer '.$i,
                'email' => 'customer'.$i.'@example.com',
                'business_name' => null,
                'contact_info' => '0917'.str_pad((string)random_int(1000000, 9999999), 7, '0', STR_PAD_LEFT),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        DB::table('customers')->upsert($rows, ['customer_id']);

        // Simple default address per customer if table exists
        if (DB::getSchemaBuilder()->hasTable('customer_addresses')) {
            $addr = [];
            for ($i=1; $i<=10; $i++) {
                $addr[] = [
                    'address_id' => $i,
                    'customer_id' => $i,
                    'label' => 'Home',
                    'street_address' => 'Blk '.$i.', Test Subdivision',
                    'barangay' => 'Barangay '.$i,
                    'city' => 'Metro City',
                    'province' => 'Metro Province',
                    'zip_code' => '1000',
                    'is_default' => $i === 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
            DB::table('customer_addresses')->upsert($addr, ['address_id']);
        }
    }
}

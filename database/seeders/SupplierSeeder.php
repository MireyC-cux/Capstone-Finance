<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Supplier;
use Illuminate\Support\Str;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        if (Supplier::count() >= 10) return;
        for ($i=1; $i<=10; $i++) {
            Supplier::create([
                'supplier_name' => 'Supplier '.str_pad((string)$i, 2, '0', STR_PAD_LEFT),
                'contact_info' => '+63 9'.rand(10,99).rand(10000000,99999999),
                'address' => 'Address #'.$i.', Metro Manila',
                'email' => 'supplier'.$i.'@example.com',
            ]);
        }
    }
}

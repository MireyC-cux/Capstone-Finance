<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
{
    DB::statement('SET FOREIGN_KEY_CHECKS=0;');

    $this->call([
        AttendanceSeeder::class,
        DeductionSeeder::class,
        PayrollSeeder::class,
        ServiceSeeder::class,
        BillingSeeder::class,
        InvoiceSeeder::class,
        AccountsReceivableSeeder::class,
        PaymentsReceivedSeeder::class,
        CashFlowSeeder::class,
        SupplierSeeder::class,
        PurchaseOrderSeeder::class,
        AccountsPayableSeeder::class,
        PaymentsMadeSeeder::class,
        InventorySeeder::class,
    ]);
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}

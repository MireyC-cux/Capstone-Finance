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
        EmployeeProfileSeeder::class,
        AdministrativeAccountSeeder::class,
        SalaryRateSeeder::class,
        EmployeeSalaryRateSeeder::class,
        AttendanceSeeder::class,
        LeaveOvertimeRequestSeeder::class,
        CashAdvanceSeeder::class,
        DeductionSeeder::class,
        PayrollSeeder::class,
        CustomerSeeder::class,
        ServiceSeeder::class,
        AirconTypeSeeder::class,
        ServiceRequestSeeder::class,
        ServiceRequestItemSeeder::class,
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

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed the database in the correct order to maintain referential integrity
        $this->call([
            // Core data first
            EmployeeprofilesTableSeeder::class,
            CustomersTableSeeder::class,
            
            // Dependencies for other tables
            CustomerAddressesTableSeeder::class,
            SalariesTableSeeder::class,
            AdministrativeAccountsTableSeeder::class,
            
            // Service related
            AirconTypesTableSeeder::class,
            ServicesTableSeeder::class,
            ServiceRequestsTableSeeder::class,
            ServiceRequestItemsTableSeeder::class,
            
            // Billing and payments
            BillingsTableSeeder::class,
            AccountsReceivableTableSeeder::class,
            InvoicesTableSeeder::class,
            PaymentsReceivedTableSeeder::class,
            
            // Employee related
            AttendancesTableSeeder::class,
            LeaveOvertimeRequestsTableSeeder::class,
            PayrollsTableSeeder::class,
            ExpensesTableSeeder::class,
            
            // Financial summaries
            CashFlowTableSeeder::class,
            FinancialReportsTableSeeder::class,
        ]);
    }
}

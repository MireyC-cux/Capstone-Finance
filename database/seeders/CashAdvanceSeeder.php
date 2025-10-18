<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\EmployeeProfile;
use App\Models\AdministrativeAccount;

class CashAdvanceSeeder extends Seeder
{
    public function run(): void
    {
        $employees = EmployeeProfile::all();
        $adminId = AdministrativeAccount::value('admin_id') ?? 1;
        foreach ($employees as $emp) {
            DB::table('cash_advances')->updateOrInsert(
                [
                    'employeeprofiles_id' => $emp->employeeprofiles_id,
                    'amount' => 2000.00,
                    'filed_date' => now()->subDays(10),
                ],
                [
                    'reason' => 'Short-term cash assistance',
                    'approved_date' => now()->subDays(5),
                    'status' => 'approved',
                    'created_by' => $adminId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}

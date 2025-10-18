<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EmployeeProfile;
use App\Models\SalaryRate;
use App\Models\EmployeeSalaryRate;

class EmployeeSalaryRateSeeder extends Seeder
{
    public function run(): void
    {
        $employees = EmployeeProfile::all();
        foreach ($employees as $emp) {
            $defaultRate = SalaryRate::where('position', $emp->position)->where('status', 'active')->first();
            EmployeeSalaryRate::updateOrCreate(
                [
                    'employeeprofiles_id' => $emp->employeeprofiles_id,
                    'status' => 'active',
                ],
                [
                    'salary_rate_id' => $defaultRate?->salary_rate_id,
                    'custom_salary_rate' => null,
                    'effective_date' => now()->toDateString(),
                    'status' => 'active',
                ]
            );
        }
    }
}

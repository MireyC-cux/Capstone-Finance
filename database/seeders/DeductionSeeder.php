<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\EmployeeProfile;

class DeductionSeeder extends Seeder
{
    public function run(): void
    {
        $employees = EmployeeProfile::all();
        $today = now();
        $firstHalfStart = $today->copy()->startOfMonth();
        $firstHalfAny = $firstHalfStart->copy()->addDays(5); // between 1-15
        $secondHalfStart = $today->copy()->startOfMonth()->addDays(15);
        $secondHalfAny = $secondHalfStart->copy()->addDays(5); // between 16-end

        foreach ($employees as $emp) {
            // First half deductions
            DB::table('deductions')->updateOrInsert(
                [
                    'employeeprofiles_id' => $emp->employeeprofiles_id,
                    'created_at' => $firstHalfAny->copy()->startOfDay(),
                ],
                [
                    'income_tax' => 500.00,
                    'sss' => 225.00,
                    'philhealth' => 150.00,
                    'pagibig' => 100.00,
                    'amount' => 0,
                    'updated_at' => $firstHalfAny->copy()->endOfDay(),
                ]
            );

            // Second half deductions
            DB::table('deductions')->updateOrInsert(
                [
                    'employeeprofiles_id' => $emp->employeeprofiles_id,
                    'created_at' => $secondHalfAny->copy()->startOfDay(),
                ],
                [
                    'income_tax' => 500.00,
                    'sss' => 225.00,
                    'philhealth' => 150.00,
                    'pagibig' => 100.00,
                    'amount' => 0,
                    'updated_at' => $secondHalfAny->copy()->endOfDay(),
                ]
            );
        }
    }
}

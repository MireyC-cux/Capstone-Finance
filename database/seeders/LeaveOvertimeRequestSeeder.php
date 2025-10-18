<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\EmployeeProfile;

class LeaveOvertimeRequestSeeder extends Seeder
{
    public function run(): void
    {
        $employees = EmployeeProfile::all();
        $start = now()->copy()->startOfMonth();
        $end = now()->copy()->endOfMonth();
        foreach ($employees as $emp) {
            // 2 approved OT entries within the month
            DB::table('leave_overtime_requests')->insert([
                [
                    'employeeprofiles_id' => $emp->employeeprofiles_id,
                    'leave_days' => 0,
                    'overtime_hours' => 4,
                    'status' => 'approved',
                    'request_date' => $start->copy()->addDays(3),
                ],
                [
                    'employeeprofiles_id' => $emp->employeeprofiles_id,
                    'leave_days' => 0,
                    'overtime_hours' => 6,
                    'status' => 'approved',
                    'request_date' => $start->copy()->addDays(10),
                ],
            ]);
        }
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\EmployeeProfile;

class AttendanceSeeder extends Seeder
{
    public function run(): void
    {
        $employees = EmployeeProfile::all();
        // Determine current semi-month period
        $today = now();
        if ($today->day <= 15) {
            $start = $today->copy()->startOfMonth();
            $end = $today->copy()->startOfMonth()->addDays(14);
        } else {
            $start = $today->copy()->startOfMonth()->addDays(15);
            $end = $today->copy()->endOfMonth();
        }

        foreach ($employees as $emp) {
            $cursor = $start->copy();
            while ($cursor->lte($end)) {
                // Skip weekends
                if (!in_array($cursor->dayOfWeekIso, [6,7])) {
                    DB::table('attendances')->updateOrInsert(
                        [
                            'employeeprofiles_id' => $emp->employeeprofiles_id,
                            'date' => $cursor->toDateString(),
                        ],
                        [
                            'time_in' => '08:00:00',
                            'time_out' => '17:00:00',
                            'flag' => 1,
                            'status' => 'Present',
                        ]
                    );
                }
                $cursor->addDay();
            }
        }
    }
}

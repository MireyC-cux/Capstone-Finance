<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LeaveOvertimeRequestsTableSeeder extends Seeder
{
    public function run()
    {
        $requests = [
            // Leave requests
            [
                'employeeprofiles_id' => 1,
                'leave_days' => 2,
                'overtime_hours' => null,
                'status' => 'Approved',
                'request_date' => Carbon::now()->subDays(10),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'employeeprofiles_id' => 2,
                'leave_days' => 3,
                'overtime_hours' => null,
                'status' => 'Pending',
                'request_date' => Carbon::now()->subDays(5),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // Overtime requests
            [
                'employeeprofiles_id' => 3,
                'leave_days' => null,
                'overtime_hours' => 4,
                'status' => 'Approved',
                'request_date' => Carbon::now()->subDays(7),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'employeeprofiles_id' => 1,
                'leave_days' => null,
                'overtime_hours' => 3,
                'status' => 'Pending',
                'request_date' => Carbon::now()->subDays(2),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];

        DB::table('leaveovertimerequests')->insert($requests);
    }
}

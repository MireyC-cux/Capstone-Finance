<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AttendancesTableSeeder extends Seeder
{
    public function run()
    {
        $attendances = [
            // Employee 1 - 5 days of attendance
            [
                'employeeprofiles_id' => 1,
                'date' => Carbon::now()->subDays(5)->toDateString(),
                'time_in' => '08:00:00',
                'time_out' => '17:00:00',
                'flag' => 1,
                'status' => 'Present',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'employeeprofiles_id' => 1,
                'date' => Carbon::now()->subDays(4)->toDateString(),
                'time_in' => '08:05:00',
                'time_out' => '17:10:00',
                'flag' => 1,
                'status' => 'Present',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'employeeprofiles_id' => 1,
                'date' => Carbon::now()->subDays(3)->toDateString(),
                'time_in' => '08:10:00',
                'time_out' => '17:05:00',
                'flag' => 1,
                'status' => 'Present',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // Employee 2 - 4 days of attendance
            [
                'employeeprofiles_id' => 2,
                'date' => Carbon::now()->subDays(5)->toDateString(),
                'time_in' => '08:05:00',
                'time_out' => '17:05:00',
                'flag' => 1,
                'status' => 'Present',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'employeeprofiles_id' => 2,
                'date' => Carbon::now()->subDays(4)->toDateString(),
                'time_in' => '08:00:00',
                'time_out' => '17:00:00',
                'flag' => 1,
                'status' => 'Present',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            
            // Employee 3 - 3 days of attendance
            [
                'employeeprofiles_id' => 3,
                'date' => Carbon::now()->subDays(5)->toDateString(),
                'time_in' => '08:10:00',
                'time_out' => '17:10:00',
                'flag' => 1,
                'status' => 'Present',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];

        DB::table('attendances')->insert($attendances);
    }
}

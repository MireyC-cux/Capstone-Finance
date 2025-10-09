<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SalariesTableSeeder extends Seeder
{
    public function run()
    {
        $salaries = [
            [
                'employeeprofiles_id' => 1,
                'basic_salary' => 30000.00,
                'effective_date' => Carbon::now()->subMonths(3),
                'status' => 'Active',
            ],
            [
                'employeeprofiles_id' => 2,
                'basic_salary' => 36000.00,
                'effective_date' => Carbon::now()->subMonths(3),
                'status' => 'Active',
            ],
            [
                'employeeprofiles_id' => 3,
                'basic_salary' => 40000.00,
                'effective_date' => Carbon::now()->subMonths(6),
                'status' => 'Active',
            ]
        ];

        DB::table('salaries')->insert($salaries);
    }
}

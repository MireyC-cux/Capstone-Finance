<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PayrollsTableSeeder extends Seeder
{
    public function run()
    {
        $payrolls = [
            [
                'employeeprofiles_id' => 1,
                'total_days_of_work' => 22,
                'pay_period' => 'First Half',
                'pay_period_start' => Carbon::now()->startOfMonth(),
                'pay_period_end' => Carbon::now()->startOfMonth()->addDays(14),
                'basic_salary' => 15000.00,
                'overtime_pay' => 2500.00,
                'deductions' => 2000.00,
                'net_pay' => 15500.00,
                'status' => 'Approved',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'employeeprofiles_id' => 2,
                'total_days_of_work' => 22,
                'pay_period' => 'First Half',
                'pay_period_start' => Carbon::now()->startOfMonth(),
                'pay_period_end' => Carbon::now()->startOfMonth()->addDays(14),
                'basic_salary' => 18000.00,
                'overtime_pay' => 3200.00,
                'deductions' => 2500.00,
                'net_pay' => 18700.00,
                'status' => 'Approved',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'employeeprofiles_id' => 3,
                'total_days_of_work' => 22,
                'pay_period' => 'First Half',
                'pay_period_start' => Carbon::now()->startOfMonth(),
                'pay_period_end' => Carbon::now()->startOfMonth()->addDays(14),
                'basic_salary' => 20000.00,
                'overtime_pay' => 4000.00,
                'deductions' => 3000.00,
                'net_pay' => 21000.00,
                'status' => 'Pending',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];

        DB::table('payrolls')->insert($payrolls);
    }
}

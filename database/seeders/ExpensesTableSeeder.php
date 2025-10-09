<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ExpensesTableSeeder extends Seeder
{
    public function run()
    {
        $expenses = [
            [
                'employeeprofiles_id' => 1,
                'amount' => 1500.00,
                'description' => 'Office supplies and materials',
                'date' => Carbon::now()->subDays(5),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'employeeprofiles_id' => 2,
                'amount' => 2500.00,
                'description' => 'Equipment maintenance',
                'date' => Carbon::now()->subDays(3),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'employeeprofiles_id' => 3,
                'amount' => 5000.00,
                'description' => 'Vehicle fuel and maintenance',
                'date' => Carbon::now()->subDays(1),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];

        DB::table('expenses')->insert($expenses);
    }
}

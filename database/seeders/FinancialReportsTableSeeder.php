<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FinancialReportsTableSeeder extends Seeder
{
    public function run()
    {
        $reports = [
            [
                'report_name' => 'Monthly Financial Report - September 2025',
                'generated_by' => 'System',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'report_name' => 'Quarterly Financial Report - Q3 2025',
                'generated_by' => 'System',
                'created_at' => now()->subDays(15),
                'updated_at' => now()->subDays(15),
            ],
            [
                'report_name' => 'Annual Financial Report - 2025',
                'generated_by' => 'System',
                'created_at' => now()->subMonths(3),
                'updated_at' => now()->subMonths(3),
            ]
        ];

        DB::table('financialreports')->insert($reports);
    }
}

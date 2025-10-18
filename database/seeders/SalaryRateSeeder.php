<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SalaryRate;

class SalaryRateSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['position' => 'Technician', 'salary_rate' => 800.00, 'status' => 'active'],
            ['position' => 'Manager', 'salary_rate' => 1500.00, 'status' => 'active'],
            ['position' => 'Clerk', 'salary_rate' => 600.00, 'status' => 'active'],
        ];

        foreach ($rows as $r) {
            SalaryRate::updateOrCreate(
                ['position' => $r['position']],
                $r
            );
        }
    }
}

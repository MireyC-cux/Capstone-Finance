<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class EmployeeprofilesTableSeeder extends Seeder
{
    public function run()
    {
        $employees = [
            [
                'first_name' => 'Juan',
                'last_name' => 'Dela Cruz',
                'address' => '123 Rizal St., Quezon City',
                'position' => 'Senior Technician',
                'contact_info' => '09123456789',
                'hire_date' => '2022-01-15',
                'status' => 'Active',
                'emergency_contact' => 'Maria Dela Cruz - 09187654321 (Spouse)',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'first_name' => 'Maria',
                'last_name' => 'Santos',
                'address' => '456 Bonifacio Ave., Makati City',
                'position' => 'Customer Service Representative',
                'contact_info' => '09234567890',
                'hire_date' => '2022-06-20',
                'status' => 'Active',
                'emergency_contact' => 'Juan Santos - 09218765432 (Husband)',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'first_name' => 'Pedro',
                'last_name' => 'Reyes',
                'address' => '789 Roxas Blvd., Manila',
                'position' => 'Junior Technician',
                'contact_info' => '09345678901',
                'hire_date' => '2023-02-10',
                'status' => 'Probationary',
                'emergency_contact' => 'Ana Reyes - 09321234567 (Sister)',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];

        DB::table('employeeprofiles')->insert($employees);
    }
}

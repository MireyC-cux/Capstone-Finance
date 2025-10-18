<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EmployeeProfile;

class EmployeeProfileSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            [
                'first_name' => 'Juan',
                'last_name' => 'Dela Cruz',
                'address' => 'Manila',
                'position' => 'Technician',
                'contact_info' => '09171234567',
                'hire_date' => now()->subYears(2)->toDateString(),
                'status' => 'active',
                'emergency_contact' => 'Maria Dela Cruz',
                'fingerprint_data' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'first_name' => 'Ana',
                'last_name' => 'Santos',
                'address' => 'Quezon City',
                'position' => 'Manager',
                'contact_info' => '09181234567',
                'hire_date' => now()->subYears(3)->toDateString(),
                'status' => 'active',
                'emergency_contact' => 'Pedro Santos',
                'fingerprint_data' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'first_name' => 'Mark',
                'last_name' => 'Reyes',
                'address' => 'Makati',
                'position' => 'Technician',
                'contact_info' => '09191234567',
                'hire_date' => now()->subYear()->toDateString(),
                'status' => 'active',
                'emergency_contact' => 'Jose Reyes',
                'fingerprint_data' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($rows as $r) {
            EmployeeProfile::updateOrCreate(
                [
                    'first_name' => $r['first_name'],
                    'last_name' => $r['last_name'],
                    'position' => $r['position'],
                ],
                $r
            );
        }
    }
}

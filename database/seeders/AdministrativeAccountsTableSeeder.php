<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdministrativeAccountsTableSeeder extends Seeder
{
    public function run()
    {
        $accounts = [
            [
                'employeeprofiles_id' => 1,
                'admin_position' => 'Administrator',
                'username' => 'admin1',
                'password' => Hash::make('admin123'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'employeeprofiles_id' => 2,
                'admin_position' => 'Manager',
                'username' => 'manager1',
                'password' => Hash::make('manager123'),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'employeeprofiles_id' => 3,
                'admin_position' => 'Supervisor',
                'username' => 'supervisor1',
                'password' => Hash::make('super123'),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];

        DB::table('administrativeaccounts')->insert($accounts);
    }
}

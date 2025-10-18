<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AdministrativeAccount;

class AdministrativeAccountSeeder extends Seeder
{
    public function run(): void
    {
        AdministrativeAccount::updateOrCreate(
            ['username' => 'admin'],
            [
                'employeeprofiles_id' => '1',
                'admin_position' => 'Super Admin',
                'username' => 'admin',
                'password' => bcrypt('password'),
            ]
        );
    }
}

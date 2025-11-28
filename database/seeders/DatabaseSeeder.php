<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
{
    DB::statement('SET FOREIGN_KEY_CHECKS=0;');

    $this->call([
  
 
    
  

    
        AccountsReceivableSeeder::class,
        PaymentsReceivedSeeder::class,
        CashFlowSeeder::class,
   
      
        AccountsPayableSeeder::class,
        PaymentsMadeSeeder::class,

    ]);
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ServiceRequestItemsTableSeeder extends Seeder
{
    public function run()
    {
        $serviceRequestItems = [
            [
                'service_request_id' => 1,
                'services_id' => 1,
                'aircon_type_id' => 1,
                'service_type' => 'Regular Maintenance',
                'unit_type' => 'Split Type',
                'quantity' => 2,
                'unit_price' => 1500.00,
                'discount' => 0.00,
                'tax' => 180.00,
                'line_total' => 3000.00,
                'start_date' => Carbon::now()->subDays(10),
                'end_date' => Carbon::now()->subDays(9),
                'start_time' => '09:00:00',
                'end_time' => '17:00:00',
                'assigned_technician_id' => 1,
                'status' => 'Completed',
                'bill_separately' => false,
                'billed' => true,
                'service_notes' => 'Routine cleaning and maintenance performed.'
            ],
            [
                'service_request_id' => 2,
                'services_id' => 2,
                'aircon_type_id' => 2,
                'service_type' => 'Repair',
                'unit_type' => 'Window Type',
                'quantity' => 1,
                'unit_price' => 2500.00,
                'discount' => 250.00,
                'tax' => 270.00,
                'line_total' => 2250.00,
                'start_date' => Carbon::now()->subDays(5),
                'end_date' => Carbon::now()->subDays(4),
                'start_time' => '10:00:00',
                'end_time' => '15:00:00',
                'assigned_technician_id' => 2,
                'status' => 'In Progress',
                'bill_separately' => true,
                'billed' => false,
                'service_notes' => 'Compressor replacement needed.'
            ],
            [
                'service_request_id' => 3,
                'services_id' => 3,
                'aircon_type_id' => 3,
                'service_type' => 'Installation',
                'unit_type' => 'Inverter',
                'quantity' => 1,
                'unit_price' => 5000.00,
                'discount' => 500.00,
                'tax' => 540.00,
                'line_total' => 4500.00,
                'start_date' => Carbon::now()->addDays(2),
                'end_date' => Carbon::now()->addDays(3),
                'start_time' => '13:00:00',
                'end_time' => '17:00:00',
                'assigned_technician_id' => 3,
                'status' => 'Pending',
                'bill_separately' => true,
                'billed' => false,
                'service_notes' => 'New installation with ductwork.'
            ]
        ];

        DB::table('service_request_items')->insert($serviceRequestItems);
    }
}

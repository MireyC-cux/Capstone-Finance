<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ServiceRequestsTableSeeder extends Seeder
{
    public function run()
    {
        // First, let's check if we have the required customers and addresses
        $customer1 = DB::table('customers')->where('customer_id', 1)->first();
        $customer2 = DB::table('customers')->where('customer_id', 2)->first();
        $customer3 = DB::table('customers')->where('customer_id', 3)->first();
        
        $address1 = DB::table('customer_addresses')->where('address_id', 1)->first();
        $address2 = DB::table('customer_addresses')->where('address_id', 2)->first();
        $address3 = DB::table('customer_addresses')->where('address_id', 3)->first();

        // If customers or addresses don't exist, we need to create them first
        if (!$customer1 || !$address1) {
            // Insert a test customer and address if they don't exist
            $customerId1 = DB::table('customers')->insertGetId([
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'john.doe@example.com',
                'phone' => '1234567890',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            $addressId1 = DB::table('customer_addresses')->insertGetId([
                'customer_id' => $customerId1,
                'address_line1' => '123 Main St',
                'city' => 'Sample City',
                'state' => 'Sample State',
                'postal_code' => '12345',
                'country' => 'Sample Country',
                'is_default' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            $customer1 = (object) ['customer_id' => $customerId1];
            $address1 = (object) ['address_id' => $addressId1];
        } else {
            $customerId1 = $customer1->customer_id;
            $addressId1 = $address1->address_id;
        }

        if (!$customer2 || !$address2) {
            $customerId2 = DB::table('customers')->insertGetId([
                'first_name' => 'Jane',
                'last_name' => 'Smith',
                'email' => 'jane.smith@example.com',
                'phone' => '0987654321',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            $addressId2 = DB::table('customer_addresses')->insertGetId([
                'customer_id' => $customerId2,
                'address_line1' => '456 Oak Ave',
                'city' => 'Another City',
                'state' => 'Another State',
                'postal_code' => '67890',
                'country' => 'Sample Country',
                'is_default' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            $customer2 = (object) ['customer_id' => $customerId2];
            $address2 = (object) ['address_id' => $addressId2];
        } else {
            $customerId2 = $customer2->customer_id;
            $addressId2 = $address2->address_id;
        }

        if (!$customer3 || !$address3) {
            $customerId3 = DB::table('customers')->insertGetId([
                'first_name' => 'Bob',
                'last_name' => 'Johnson',
                'email' => 'bob.johnson@example.com',
                'phone' => '5555555555',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            $addressId3 = DB::table('customer_addresses')->insertGetId([
                'customer_id' => $customerId3,
                'address_line1' => '789 Pine St',
                'city' => 'Somewhere',
                'state' => 'Some State',
                'postal_code' => '54321',
                'country' => 'Sample Country',
                'is_default' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            $customer3 = (object) ['customer_id' => $customerId3];
            $address3 = (object) ['address_id' => $addressId3];
        } else {
            $customerId3 = $customer3->customer_id;
            $addressId3 = $address3->address_id;
        }

        $serviceRequests = [
            [
                'customer_id' => $customerId1,
                'address_id' => $addressId1,
                'service_date' => Carbon::now()->subDays(10)->toDateString(),
                'start_date' => Carbon::now()->subDays(10)->toDateString(),
                'end_date' => Carbon::now()->subDays(9)->toDateString(),
                'start_time' => '09:00:00',
                'end_time' => '17:00:00',
                'type_of_payment' => 'Credit Card',
                'order_status' => 'Completed',
                'payment_status' => 'Paid',
                'accomplishment_date' => Carbon::now()->subDays(9)->toDateString(),
                'remarks' => 'Regular maintenance service',
                'service_request_number' => 'SR-' . date('Ymd') . '-001',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'customer_id' => $customerId2,
                'address_id' => $addressId2,
                'service_date' => Carbon::now()->subDays(5)->toDateString(),
                'start_date' => Carbon::now()->subDays(5)->toDateString(),
                'end_date' => Carbon::now()->subDays(4)->toDateString(),
                'start_time' => '10:00:00',
                'end_time' => '15:00:00',
                'type_of_payment' => 'Bank Transfer',
                'order_status' => 'Ongoing',
                'payment_status' => 'Partially Paid',
                'accomplishment_date' => null,
                'remarks' => 'AC repair service',
                'service_request_number' => 'SR-' . date('Ymd') . '-002',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'customer_id' => $customerId3,
                'address_id' => $addressId3,
                'service_date' => Carbon::now()->addDays(2)->toDateString(),
                'start_date' => Carbon::now()->addDays(2)->toDateString(),
                'end_date' => Carbon::now()->addDays(3)->toDateString(),
                'start_time' => '13:00:00',
                'end_time' => '17:00:00',
                'type_of_payment' => 'Cash',
                'order_status' => 'Pending',
                'payment_status' => 'Unpaid',
                'accomplishment_date' => null,
                'remarks' => 'Installation of new AC unit',
                'service_request_number' => 'SR-' . date('Ymd') . '-003',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];

        DB::table('service_requests')->insert($serviceRequests);
    }
}

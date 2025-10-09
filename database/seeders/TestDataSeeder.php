<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TestDataSeeder extends Seeder
{
    public function run()
    {
        // Disable foreign key checks to avoid constraint issues
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // Clear existing data
        DB::table('service_request_items')->truncate();
        DB::table('service_requests')->truncate();
        DB::table('customer_addresses')->truncate();
        DB::table('customers')->truncate();
        DB::table('services')->truncate();
        DB::table('aircon_types')->truncate();
        DB::table('employeeprofiles')->truncate();

        // Insert test data
        $this->insertTestData();

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    private function insertTestData()
    {
        // Insert a test employee
        $employeeId = DB::table('employeeprofiles')->insertGetId([
            'first_name' => 'Test',
            'last_name' => 'Technician',
            'position' => 'AC Technician',
            'address' => '123 Tech St, Tech City',
            'contact_info' => '1234567890',
            'hire_date' => now()->subYear()->toDateString(),
            'status' => 'Active',
            'emergency_contact' => '9876543210',
            'fingerprint_data' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Insert a test customer
        $customerId = DB::table('customers')->insertGetId([
            'full_name' => 'Test Customer',
            'email' => 'customer@example.com',
            'business_name' => 'Test Business',
            'contact_info' => '0987654321',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Insert a test address
        $addressId = DB::table('customer_addresses')->insertGetId([
            'customer_id' => $customerId,
            'label' => 'Home',
            'street_address' => '123 Test St',
            'barangay' => 'Test Barangay',
            'city' => 'Test City',
            'province' => 'Test Province',
            'zip_code' => '1234',
            'is_default' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Insert test aircon type
        $airconTypeId = DB::table('aircon_types')->insertGetId([
            'name' => 'Test Type',
            'description' => 'Test Aircon Type',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Insert test service
        $serviceId = DB::table('services')->insertGetId([
            'service_type' => 'Test Service',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Insert test service request
        $serviceRequestId = DB::table('service_requests')->insertGetId([
            'customer_id' => $customerId,
            'address_id' => $addressId,
            'service_date' => now()->toDateString(),
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDay()->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'type_of_payment' => 'Cash',
            'order_status' => 'Completed',
            'payment_status' => 'Paid',
            'accomplishment_date' => now()->toDateString(),
            'remarks' => 'Test service request',
            'service_request_number' => 'TEST-' . time(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Insert test service request item
        DB::table('service_request_items')->insert([
            'service_request_id' => $serviceRequestId,
            'services_id' => $serviceId,
            'aircon_type_id' => $airconTypeId,
            'service_type' => 'Test Service',
            'unit_type' => 'Test Type',
            'quantity' => 1,
            'unit_price' => 100.00,
            'discount' => 0,
            'tax' => 12.00,
            'line_total' => 112.00,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDay()->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'assigned_technician_id' => $employeeId,
            'status' => 'Completed',
            'bill_separately' => false,
            'billed' => true,
            'service_notes' => 'Test service item',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('service_requests', function (Blueprint $table) {
            $table->id('service_request_id');

            // Customer
            $table->foreignId('customer_id')
                  ->constrained('customers', 'customer_id')
                  ->cascadeOnDelete()
                  ->cascadeOnUpdate();

            // Optional address used for this request (preference)
            $table->foreignId('address_id')->nullable()
                  ->constrained('customer_addresses', 'address_id')
                  ->nullOnDelete()
                  ->cascadeOnUpdate();

            // Booking schedule (order-level defaults)
            $table->date('service_date'); // general booking date
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();

            // Payment & status
            $table->string('type_of_payment')->nullable();
            $table->enum('order_status', ['Pending', 'Ongoing', 'Completed', 'Cancelled'])->default('Pending');
            $table->enum('payment_status', ['Unpaid','Partially Paid','Paid','Cancelled'])->default('Unpaid');
            $table->date('accomplishment_date')->nullable();

            // Extra info
            $table->text('remarks')->nullable();

            // External (HR/Finance) references - allow null and null on delete
            /*$table->foreignId('billing_id')->nullable()
                  ->constrained('billings', 'billings_id')
                  ->nullOnDelete()
                  ->cascadeOnUpdate();

            $table->foreignId('quotation_id')->nullable()
                  ->constrained('quotations', 'quotation_id')
                  ->nullOnDelete()
                  ->cascadeOnUpdate();*/

            // friendly ref number
            $table->string('service_request_number')->nullable()->unique();

            $table->timestamps();

            // useful indexes
            $table->index(['customer_id', 'service_date', 'order_status']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('service_requests');
    }
};
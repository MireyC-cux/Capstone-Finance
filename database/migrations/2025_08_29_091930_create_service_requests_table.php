<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('service_requests', function (Blueprint $table) {
            $table->id('service_request_id');
            $table->string('service_request_number')->nullable()->unique();

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

            $table->decimal('order_total', 12, 2)->nullable();
            $table->decimal('overall_discount', 12, 2)->default(0);

            // Payment & status
            $table->string('type_of_payment')->nullable();
            $table->enum('order_status', ['Pending', 'Ongoing', 'Completed', 'Cancelled'])->default('Pending');
            $table->enum('payment_status', ['Unpaid','Partially Paid','Paid','Cancelled'])->default('Unpaid');
            $table->date('accomplishment_date')->nullable();

            // Extra info
            $table->text('remarks')->nullable();

            // PDF storage fields
            $table->string('pdf_name')->nullable();            // e.g., "ServiceRequest_1234.pdf"
            $table->string('pdf_mime')->default('application/pdf');
            $table->binary('pdf_file')->nullable();            // stores the actual PDF content
            $table->timestamp('pdf_generated_at')->nullable(); // when the PDF was created


            // friendly ref number
            $table->timestamps();

            // useful indexes
            $table->index(['customer_id', 'order_status']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('service_requests');
    }
};
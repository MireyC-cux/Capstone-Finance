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

            // Financial details
            $table->decimal('order_total', 12, 2)->nullable();
            $table->decimal('overall_discount', 12, 2)->default(0);
            $table->decimal('overall_tax_amount', 12, 2)->default(0);

            // Payment & service status
            $table->string('type_of_payment')->nullable();
            $table->enum('order_status', ['Pending', 'Ongoing', 'Completed', 'Cancelled'])->default('Pending');
            $table->enum('payment_status', ['Unpaid','Partially Paid','Paid','Cancelled'])->default('Unpaid');
            $table->date('accomplishment_date')->nullable();

            // Extra info
            $table->text('remarks')->nullable();

            // --- Quotation Management & Approval System ---
            // Track current quotation status and approval info
            $table->enum('quotation_status', ['Pending', 'Approved'])->default('Pending');
            $table->string('quotation_file_path')->nullable(); // e.g. 'quotations/ServiceRequest_1234.pdf'
            $table->string('quotation_file_disk')->default('public')->nullable(); // storage disk reference
            $table->timestamp('quotation_approved_at')->nullable(); // system approval time
            // (Optional future field) $table->foreignId('quotation_approved_by')->nullable()->constrained('users');

            // Friendly timestamps and indexes
            $table->timestamps();
            $table->index(['customer_id', 'order_status']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('service_requests');
    }
};
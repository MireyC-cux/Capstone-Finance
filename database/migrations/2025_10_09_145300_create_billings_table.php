<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('billings', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id('billing_id');

            // Relationships
            $table->unsignedBigInteger('service_request_id');
            $table->unsignedBigInteger('customer_id');

            // Core fields
            $table->date('billing_date');
            $table->enum('status', ['Billed', 'Unbilled'])->default('Billed');
            $table->boolean('generate_invoice_after_approval')->default(true);

            $table->timestamps();

            // Foreign key constraints
            $table->foreign('service_request_id')
                ->references('service_request_id')
                ->on('service_requests')
                ->onDelete('cascade');

            $table->foreign('customer_id')
                ->references('customer_id')
                ->on('customers')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('billings');
    }
};

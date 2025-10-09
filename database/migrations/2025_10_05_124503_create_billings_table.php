<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('billings', function (Blueprint $table) {
            $table->id('billing_id');
            $table->unsignedBigInteger('service_request_id');
            $table->unsignedBigInteger('customer_id');
            $table->date('billing_date');
            $table->date('due_date');
            $table->decimal('total_amount', 12, 2);
            $table->decimal('discount', 12, 2)->default(0.00);
            $table->decimal('tax', 12, 2)->default(0.00);
            $table->decimal('net_amount', 12, 2)->virtualAs('total_amount - discount + tax');
            $table->enum('status', ['Draft', 'Unbilled', 'Billed', 'Cancelled'])->default('Draft');
            $table->timestamps();

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

    public function down()
    {
        Schema::dropIfExists('billings');
    }
};

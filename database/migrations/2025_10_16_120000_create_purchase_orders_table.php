<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id('po_id');
            $table->unsignedBigInteger('ap_id')->nullable();
            $table->unsignedBigInteger('supplier_id');
            $table->unsignedBigInteger('service_request_id')->nullable();
            $table->string('po_number', 50)->unique();
            $table->date('po_date');
            $table->enum('status', ['Pending', 'Approved', 'Rejected', 'Completed'])->default('Pending');
            $table->decimal('total_amount', 12, 2);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->foreign('supplier_id')
                ->references('supplier_id')->on('suppliers')
                ->onDelete('cascade')->onUpdate('cascade');

            $table->foreign('service_request_id')
                ->references('service_request_id')->on('service_requests')
                ->onDelete('set null')->onUpdate('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('purchase_orders');
    }
};

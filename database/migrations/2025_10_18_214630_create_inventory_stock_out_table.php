<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('inventory_stock_out', function (Blueprint $table) {
            $table->id('stock_out_id');
            $table->unsignedBigInteger('service_request_id')->nullable();
            $table->unsignedBigInteger('item_id');
            $table->integer('quantity');
            $table->unsignedBigInteger('issued_to')->nullable();
            $table->date('issued_date');
            $table->string('purpose', 255)->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->foreign('service_request_id')->references('service_request_id')->on('service_requests')->onDelete('set null');
            $table->foreign('item_id')->references('item_id')->on('inventory_items')->onDelete('cascade');
            $table->foreign('issued_to')->references('employeeprofiles_id')->on('employeeprofiles')->onDelete('set null');

            $table->index(['item_id','issued_date']);
            $table->index(['service_request_id']);
            $table->index(['issued_to']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('inventory_stock_out');
    }
};

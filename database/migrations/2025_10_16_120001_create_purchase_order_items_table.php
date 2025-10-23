<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->bigIncrements('purchase_order_item_id');
            $table->unsignedBigInteger('purchase_order_id');
            $table->unsignedBigInteger('item_id');
            $table->unsignedBigInteger('supplier_item_id')->nullable();

            // Optional service request context for auto-populated shortages
            $table->unsignedBigInteger('service_request_id')->nullable();
            $table->unsignedBigInteger('service_request_item_id')->nullable();
            $table->boolean('from_insufficient_auto')->default(false);

            $table->integer('quantity');
            $table->decimal('unit_price', 12, 2)->nullable();
            $table->decimal('line_total', 14, 2)->nullable()->storedAs('quantity * COALESCE(unit_price, 0)');
            $table->string('notes', 255)->nullable();
            $table->timestamps();

            // FKs
            $table->foreign('purchase_order_id')->references('purchase_order_id')->on('purchase_orders')->onDelete('cascade');
            $table->foreign('item_id')->references('item_id')->on('inventory_items')->onDelete('restrict');
            $table->foreign('service_request_id')->references('service_request_id')->on('service_requests')->onDelete('set null');
            $table->foreign('service_request_item_id')->references('item_id')->on('service_request_items')->onDelete('set null');

            $table->index(['purchase_order_id']);
            $table->index(['item_id']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('purchase_order_items');
    }
};

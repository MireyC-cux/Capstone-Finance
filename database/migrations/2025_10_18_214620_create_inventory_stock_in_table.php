<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('inventory_stock_in', function (Blueprint $table) {
            $table->bigIncrements('stock_in_id');
            
            // Linked to purchase_orders
            $table->unsignedBigInteger('purchase_order_id')->nullable();
            
            // Linked to inventory_items
            $table->unsignedBigInteger('item_id');
            
            $table->integer('quantity');
            $table->decimal('unit_cost', 12, 2);
            $table->decimal('total_cost', 12, 2)->storedAs('quantity * unit_cost');
            
            $table->date('received_date');
            $table->unsignedBigInteger('received_by')->nullable(); // admin_id or staff_id who received
            
            $table->text('remarks')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('purchase_order_id')
                  ->references('purchase_order_id')
                  ->on('purchase_orders')
                  ->onDelete('set null');

            $table->foreign('item_id')
                  ->references('item_id')
                  ->on('inventory_items')
                  ->onDelete('cascade');

            $table->foreign('received_by')
                  ->references('admin_id')
                  ->on('administrativeaccounts')
                  ->onDelete('set null');

            // Indexes
            $table->index(['item_id', 'received_date']);
            $table->index(['purchase_order_id']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('inventory_stock_in');
    }
};

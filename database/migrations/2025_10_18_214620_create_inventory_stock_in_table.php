<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('inventory_stock_in', function (Blueprint $table) {
            $table->id('stock_in_id');
            $table->unsignedBigInteger('po_id')->nullable();
            $table->unsignedBigInteger('item_id');
            $table->integer('quantity');
            $table->decimal('unit_cost', 12, 2);
            $table->decimal('total_cost', 12, 2)->storedAs('quantity * unit_cost');
            $table->date('received_date');
            $table->unsignedBigInteger('received_by')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->foreign('po_id')->references('po_id')->on('purchase_orders')->onDelete('set null');
            $table->foreign('item_id')->references('item_id')->on('inventory_items')->onDelete('cascade');

            $table->index(['item_id','received_date']);
            $table->index(['po_id']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('inventory_stock_in');
    }
};

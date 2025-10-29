<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id('item_id');
            $table->string('item_name', 255);
            $table->enum('category', ['Aircon Unit', 'Spare Part', 'Material', 'Consumable']);
            $table->string('brand', 255)->nullable();
            $table->string('model', 255)->nullable();
            $table->string('unit', 50)->default('pcs');
            $table->integer('reorder_level')->default(5);
            $table->decimal('unit_cost', 12, 2)->nullable();
            $table->decimal('selling_price', 12, 2)->nullable();
            $table->enum('status', ['active','inactive'])->default('active');
            $table->timestamps();

            $table->index(['category', 'status']);
            $table->index(['item_name']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('inventory_items');
    }
};

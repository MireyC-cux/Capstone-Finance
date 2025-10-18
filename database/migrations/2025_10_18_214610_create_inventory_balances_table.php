<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('inventory_balances', function (Blueprint $table) {
            $table->unsignedBigInteger('item_id')->primary();
            $table->integer('current_stock')->default(0);
            $table->timestamp('last_updated')->nullable();

            $table->foreign('item_id')->references('item_id')->on('inventory_items')->onDelete('cascade');
        });
    }
    public function down(): void {
        Schema::dropIfExists('inventory_balances');
    }
};

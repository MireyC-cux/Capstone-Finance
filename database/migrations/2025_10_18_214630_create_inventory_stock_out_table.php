<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('inventory_stock_out', function (Blueprint $table) {
    $table->id('stock_out_id');
    $table->unsignedBigInteger('item_id'); // FK
    $table->string('service_type')->nullable();
    $table->string('item_name');
    $table->integer('quantity');
    $table->date('issued_date')->nullable();
    $table->enum('status', ['Needed Request', 'Requested', 'Approve'])->default('Needed Request');
    $table->timestamps();

    // Make sure referenced column is unsignedBigInteger
    $table->foreign('item_id')
          ->references('item_id')
          ->on('service_request_items')
          ->onDelete('cascade');
});

    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_stock_out');
    }
};

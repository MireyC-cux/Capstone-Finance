<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->id('po_item_id');
            $table->unsignedBigInteger('purchase_order_id');
            $table->unsignedBigInteger('item_id')->nullable();
            $table->string('description', 255)->nullable();
            $table->integer('quantity');
            $table->decimal('unit_price', 12, 2);
            $table->timestamps();

            $table->foreign('purchase_order_id')
                ->references('po_id')->on('purchase_orders')
                ->onDelete('cascade');

            $table->foreign('item_id')
                ->references('item_id')->on('service_request_items')
                ->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('purchase_order_items');
    }
};

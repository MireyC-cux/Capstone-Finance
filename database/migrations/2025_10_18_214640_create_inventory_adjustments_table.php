<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('inventory_adjustments', function (Blueprint $table) {
            $table->id('adjustment_id');
            $table->unsignedBigInteger('item_id');
            $table->enum('adjustment_type', ['Increase','Decrease']);
            $table->integer('quantity');
            $table->text('reason')->nullable();
            $table->unsignedBigInteger('adjusted_by')->nullable();
            $table->date('adjustment_date');
            $table->timestamps();

            $table->foreign('item_id')->references('item_id')->on('inventory_items')->onDelete('cascade');
            $table->index(['item_id','adjustment_date']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('inventory_adjustments');
    }
};

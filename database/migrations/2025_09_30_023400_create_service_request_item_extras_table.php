<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('service_request_item_extras', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('item_id');
            $table->string('name');
            $table->unsignedInteger('qty')->default(1);
            $table->decimal('price', 12, 2)->nullable();
            $table->timestamps();
            $table->foreign('item_id')->references('item_id')->on('service_request_items')->onDelete('cascade');
            $table->index(['item_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_request_item_extras');
    }
};

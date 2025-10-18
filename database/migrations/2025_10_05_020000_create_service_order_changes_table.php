<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('service_order_changes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('service_request_id');
            $table->unsignedBigInteger('item_id')->nullable(); // optional: change at item level
            $table->unsignedBigInteger('changed_by')->nullable(); // user/admin id
            $table->string('field', 128)->nullable(); // field or domain (e.g., status, schedule)
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();
            $table->string('reason', 255)->nullable();
            $table->timestamps();

            $table->index(['service_request_id']);
            $table->index(['item_id']);
            $table->index(['changed_by']);
            // Keep FKs light to avoid coupling, but add if tables exist
            // $table->foreign('service_request_id')->references('service_request_id')->on('service_requests')->onDelete('cascade');
            // $table->foreign('item_id')->references('item_id')->on('service_request_items')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_order_changes');
    }
};

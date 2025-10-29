<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aircon_service_prices', function (Blueprint $table) {
            $table->id('aircon_service_price_id');

            $table->foreignId('services_id')
                  ->constrained('services', 'services_id')
                  ->cascadeOnDelete()
                  ->cascadeOnUpdate();

            $table->foreignId('aircon_type_id')
                  ->constrained('aircon_types', 'aircon_type_id')
                  ->cascadeOnDelete()
                  ->cascadeOnUpdate();

            $table->decimal('price', 10, 2)->nullable();
            $table->enum('status', ['active','inactive'])->default('active');
            $table->timestamps();

            // index to speed lookups by service + aircon type
            $table->index(['services_id', 'aircon_type_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aircon_service_prices');
    }
};
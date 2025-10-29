<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_addresses', function (Blueprint $table) {
            $table->id('address_id');
            $table->foreignId('customer_id')
                  ->constrained('customers', 'customer_id')
                  ->cascadeOnDelete()
                  ->cascadeOnUpdate();

            $table->string('label')->nullable(); // Home, Office, etc.
            $table->string('street_address')->nullable();
            $table->string('barangay')->nullable();
            $table->string('city')->nullable();
            $table->string('province')->nullable();
            $table->string('zip_code', 10)->nullable();
            $table->boolean('is_default')->default(false);

            $table->timestamps();

            $table->index(['customer_id', 'is_default']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_addresses');
    }
};
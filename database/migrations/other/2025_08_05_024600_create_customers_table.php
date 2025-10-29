<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('customers', function (Blueprint $table) {
            $table->id('customer_id');
            $table->string('full_name');
            $table->string('email')->nullable();
            $table->string('business_name')->nullable();
            $table->string('contact_info')->nullable(); // phone/email
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('customers');
    }
};

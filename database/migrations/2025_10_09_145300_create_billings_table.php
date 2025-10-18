<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('billings', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id('billing_id');
            $table->unsignedBigInteger('service_request_id');
            $table->unsignedBigInteger('customer_id');
            $table->date('billing_date');
            $table->date('due_date');
            $table->decimal('total_amount', 12, 2);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('tax', 12, 2)->default(0);
            $table->enum('status', ['Billed', 'Paid', 'Cancelled'])->default('Billed');
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('billings');
    }
};

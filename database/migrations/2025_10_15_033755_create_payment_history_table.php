<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('payment_history', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id('payment_id');
            $table->unsignedBigInteger('billing_id');
            $table->unsignedBigInteger('service_request_id');
            $table->date('payment_date');
            $table->date('due_date')->nullable();
            $table->string('type_of_payment', 50)->nullable();
            $table->decimal('amount', 12, 2);
            $table->enum('status', ['Unpaid', 'Partially Paid', 'Paid', 'Overdue', 'Cancelled'])->default('Unpaid');
            $table->timestamps();

            // Foreign keys
            //$table->foreign('billing_id')
            //    ->references('billing_id')
            //    ->on('billings')
            //    ->onDelete('cascade');

            $table->foreign('service_request_id')
                ->references('service_request_id')
                ->on('service_requests')
                ->onDelete('cascade');
        });
    }

    public function down(): void {
        Schema::dropIfExists('payment_history');
    }
};

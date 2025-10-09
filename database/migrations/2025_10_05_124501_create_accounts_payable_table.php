<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('accounts_payable', function (Blueprint $table) {
            $table->id('ap_id');
            $table->unsignedBigInteger('supplier_id');
            $table->unsignedBigInteger('purchase_order_id')->nullable();
            $table->string('invoice_number', 50)->unique();
            $table->date('invoice_date');
            $table->date('due_date');
            $table->decimal('total_amount', 12, 2);
            $table->decimal('amount_paid', 12, 2)->default(0.00);
            $table->decimal('balance', 12, 2)->virtualAs('total_amount - amount_paid');
            $table->enum('status', ['Unpaid', 'Partially Paid', 'Paid', 'Overdue', 'Cancelled'])->default('Unpaid');
            $table->string('payment_terms', 255)->nullable();
            $table->timestamps();

            $table->foreign('supplier_id')
                  ->references('supplier_id')
                  ->on('suppliers')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('accounts_payable');
    }
};

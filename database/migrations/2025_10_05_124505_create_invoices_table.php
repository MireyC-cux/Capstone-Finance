<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id('invoice_id');
            $table->unsignedBigInteger('billing_id');
            $table->unsignedBigInteger('ar_id')->nullable();
            $table->string('invoice_number', 50)->unique();
            $table->date('invoice_date');
            $table->date('due_date');
            $table->decimal('amount', 12, 2);
            $table->enum('status', ['Unpaid', 'Partially Paid', 'Paid', 'Overdue', 'Cancelled'])->default('Unpaid');
            $table->timestamps();

            $table->foreign('billing_id')
                  ->references('billing_id')
                  ->on('billings')
                  ->onDelete('cascade');
                  
            $table->foreign('ar_id')
                  ->references('ar_id')
                  ->on('accounts_receivable')
                  ->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('invoices');
    }
};

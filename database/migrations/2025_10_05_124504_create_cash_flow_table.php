<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('cash_flow', function (Blueprint $table) {
            $table->id('cashflow_id');
            $table->enum('transaction_type', ['Inflow', 'Outflow']);
            $table->enum('source_type', ['Invoice Payment', 'Supplier Payment', 'Expense', 'Other']);
            $table->unsignedBigInteger('source_id');
            $table->decimal('amount', 12, 2);
            $table->date('transaction_date');
            $table->string('description', 255)->nullable();
            $table->string('or_file_path')->nullable();
            $table->timestamps();
            
            // Index for polymorphic relationship
            $table->index(['source_type', 'source_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('cash_flow');
    }
};

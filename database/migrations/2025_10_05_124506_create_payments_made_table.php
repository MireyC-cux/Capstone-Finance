<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('payments_made', function (Blueprint $table) {
            $table->id('payment_id');
            $table->unsignedBigInteger('ap_id');
            $table->date('payment_date');
            $table->decimal('amount', 12, 2);
            $table->enum('payment_method', ['Cash', 'Bank Transfer', 'Check', 'Other']);
            $table->string('reference_number', 255)->nullable();
            $table->timestamps();

            $table->foreign('ap_id')
                  ->references('ap_id')
                  ->on('accounts_payable')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('payments_made');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('payments_received', function (Blueprint $table) {
            $table->id('payment_id');
            $table->unsignedBigInteger('ar_id');
            $table->date('payment_date');
            $table->decimal('amount', 12, 2);
            $table->enum('payment_method', ['Cash', 'GCash', 'Bank Transfer', 'Check']);
            $table->enum('payment_type', ['Full', 'Partial'])->default('Full');
            $table->string('reference_number', 255)->nullable();
            $table->string('or_file_path')->nullable();
            $table->timestamps();

            $table->foreign('ar_id')
                  ->references('ar_id')
                  ->on('accounts_receivable')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('payments_received');
    }
};

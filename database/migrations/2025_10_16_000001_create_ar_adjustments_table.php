<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ar_adjustments', function (Blueprint $table) {
            $table->id('adjustment_id');
            $table->unsignedBigInteger('ar_id');
            $table->date('adjustment_date');
            $table->enum('type', ['Discount', 'Write-off', 'Correction']);
            $table->decimal('amount', 12, 2);
            $table->string('reason', 255)->nullable();
            $table->timestamps();

            $table->foreign('ar_id')
                ->references('ar_id')
                ->on('accounts_receivable')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ar_adjustments');
    }
};

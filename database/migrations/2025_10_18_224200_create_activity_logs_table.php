<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('event_type', 100); // e.g., ar_payment, po_approved, payroll_disbursed, capital_injected, capital_withdrawn, expense_recorded
            $table->string('title');
            $table->string('context_type', 100)->nullable(); // e.g., AccountsReceivable, PurchaseOrder, Payroll, CashFlow, Expense
            $table->unsignedBigInteger('context_id')->nullable();
            $table->decimal('amount', 15, 2)->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};

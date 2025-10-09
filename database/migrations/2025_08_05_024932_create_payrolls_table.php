<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id('payroll_id');
            $table->foreignId('employeeprofiles_id')->constrained('employeeprofiles', 'employeeprofiles_id')->onDelete('cascade');
            $table->integer('total_days_of_work')->default(0);
            $table->string('pay_period');
            $table->date('pay_period_start');
            $table->date('pay_period_end');
            $table->decimal('basic_salary');
            $table->decimal('overtime_pay')->nullable();
            $table->decimal('deductions')->nullable();
            $table->decimal('net_pay')->nullable();
            $table->string('status')->default('Pending');
            $table->timestamps();
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payrolls');
    }
};

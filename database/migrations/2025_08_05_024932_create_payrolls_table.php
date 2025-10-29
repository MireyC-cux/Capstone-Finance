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
            $table->decimal('salary_rate', 10, 2)->default(0);
            $table->decimal('overtime_pay')->nullable()->default(0);
            $table->decimal('deductions')->nullable()->default('0');
            $table->string('bonuses')->nullable()->default('none as of the moment');
            $table->decimal('bonus_amount', 10, 2)->nullable()->default(0);
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
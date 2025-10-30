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
    $table->foreignId('employeeprofiles_id')
        ->constrained('employeeprofiles', 'employeeprofiles_id')
        ->onDelete('cascade');

    $table->integer('total_days_of_work')->default(0);
    $table->string('pay_period');
    $table->date('pay_period_start');
    $table->date('pay_period_end');
    $table->decimal('salary_rate', 10, 2)->default(0);
    $table->decimal('basic_salary', 10, 2)->nullable()->default(0);
    $table->decimal('overtime_pay', 10, 2)->nullable()->default(0);
    $table->decimal('gross_pay', 10, 2)->nullable()->default(0);
    $table->decimal('tax_deduction', 10, 2)->nullable()->default(0);
    $table->decimal('sss_contribution', 10, 2)->nullable()->default(0);
    $table->decimal('philhealth_contribution', 10, 2)->nullable()->default(0);
    $table->decimal('pagibig_contribution', 10, 2)->nullable()->default(0);
    $table->decimal('deductions', 10, 2)->nullable()->default(0);
    $table->string('bonuses')->nullable()->default('none as of the moment');
    $table->decimal('bonus_amount', 10, 2)->nullable()->default(0);
    $table->decimal('net_pay', 10, 2)->nullable()->default(0);
    $table->string('status')->default('Pending');
    $table->year('year')->default(now()->year);
    $table->string('month')->nullable();
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

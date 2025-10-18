<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('deductions')) {
            Schema::create('deductions', function (Blueprint $table) {
                $table->id('deduction_id');
                $table->foreignId('employeeprofiles_id')->constrained('employeeprofiles', 'employeeprofiles_id')->onDelete('cascade');
                $table->decimal('income_tax', 10, 2)->nullable()->default(0.00);
                $table->decimal('sss', 10, 2)->nullable()->default(0.00);
                $table->decimal('philhealth', 10, 2)->nullable()->default(0.00);
                $table->decimal('pagibig', 10, 2)->nullable()->default(0.00);
                $table->decimal('amount', 10, 2)->nullable();
                $table->foreignId('payroll_id')->nullable()->constrained('payrolls', 'payroll_id')->onDelete('set null');
                $table->timestamps();
                $table->index(['employeeprofiles_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('deductions');
    }
};

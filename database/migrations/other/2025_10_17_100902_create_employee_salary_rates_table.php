<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('employee_salary_rates')) {
            Schema::create('employee_salary_rates', function (Blueprint $table) {
                $table->id('employee_salary_id');
                $table->foreignId('employeeprofiles_id')->constrained('employeeprofiles', 'employeeprofiles_id')->onDelete('cascade');
                $table->foreignId('salary_rate_id')->nullable()->constrained('salary_rates', 'salary_rate_id')->onDelete('set null');
                $table->decimal('custom_salary_rate', 10, 2)->nullable();
                $table->date('effective_date')->default(DB::raw('CURRENT_DATE'));
                $table->enum('status', ['active','inactive'])->default('active');
                $table->timestamps();
                $table->index(['employeeprofiles_id', 'status']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_salary_rates');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('payroll_disbursements')) {
            Schema::create('payroll_disbursements', function (Blueprint $table) {
                $table->id('disbursement_id');
                $table->foreignId('payroll_id')->constrained('payrolls', 'payroll_id')->onDelete('cascade');
                $table->foreignId('employeeprofiles_id')->constrained('employeeprofiles', 'employeeprofiles_id')->onDelete('cascade');
                $table->date('payment_date');
                $table->enum('payment_method', ['Cash','Bank Transfer','GCash','Check','Other']);
                $table->string('reference_number')->nullable();
                $table->enum('status', ['Pending','Paid','Cancelled'])->default('Pending');
                $table->timestamps();
                $table->index(['employeeprofiles_id','status']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_disbursements');
    }
};

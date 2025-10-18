<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('payslips')) {
            Schema::create('payslips', function (Blueprint $table) {
                $table->id('payslip_id');
                $table->foreignId('payroll_id')
                    ->constrained('payrolls', 'payroll_id')
                    ->onDelete('cascade');
                $table->foreignId('employeeprofiles_id')
                    ->constrained('employeeprofiles', 'employeeprofiles_id')
                    ->onDelete('cascade');
                $table->string('pdf_name');
                $table->string('pdf_mime', 50)->default('application/pdf');
                // Temporarily use binary for now; we’ll modify it right after
                $table->binary('pdf_file')->nullable();
                $table->timestamp('generated_at')->useCurrent();
            });

            // Modify pdf_file column to LONGBLOB using raw SQL
            DB::statement('ALTER TABLE payslips MODIFY pdf_file LONGBLOB;');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('payslips');
    }
};

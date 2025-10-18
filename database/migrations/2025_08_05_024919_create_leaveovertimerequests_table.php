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
        Schema::create('leave_overtime_requests', function (Blueprint $table) {
        $table->id('request_id');
        $table->foreignId('employeeprofiles_id')->constrained('employeeprofiles', 'employeeprofiles_id')->onDelete('cascade');
        $table->integer('leave_days')->nullable();
        $table->integer('overtime_hours')->nullable();
        $table->string('status')->nullable();
        $table->dateTime('request_date')->nullable();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_overtime_requests');
    }
};

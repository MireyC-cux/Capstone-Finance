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
       Schema::create('attendances', function (Blueprint $table) {
         $table->id('attendance_id');
        $table->foreignId('employeeprofiles_id')->constrained('employeeprofiles', 'employeeprofiles_id')->onDelete('cascade');
        $table->date('date');
        $table->time('time_in');
        $table->time('time_out')->nullable();
        $table->integer('flag');
        $table->string('status');
        
        
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};

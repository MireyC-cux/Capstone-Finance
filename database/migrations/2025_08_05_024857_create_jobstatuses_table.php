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
        Schema::create('jobstatuses', function (Blueprint $table) {
            $table->id('jobstatus_id');
            $table->foreignId('services_id')->constrained('services', 'services_id')->onDelete('cascade');
            $table->foreignId('employeeprofiles_id')->constrained('employeeprofiles', 'employeeprofiles_id')->onDelete('cascade');
            $table->string('status')->nullable();
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jobstatuses');
    }
};

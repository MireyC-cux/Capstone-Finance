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
        Schema::create('expenses', function (Blueprint $table) {
            $table->id('expenses_id');
            $table->foreignId('employeeprofiles_id')->constrained('employeeprofiles', 'employeeprofiles_id')->onDelete('cascade');
            $table->decimal('amount');
            $table->string('description');
            $table->date('date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
{
    Schema::dropIfExists('expenses');
}

};

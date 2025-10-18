<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('cash_advances')) {
            Schema::create('cash_advances', function (Blueprint $table) {
                $table->id('cash_advance_id');
                $table->foreignId('employeeprofiles_id')->constrained('employeeprofiles', 'employeeprofiles_id')->onUpdate('cascade')->onDelete('restrict');
                $table->decimal('amount', 10, 2);
                $table->text('reason')->nullable();
                $table->dateTime('filed_date');
                $table->dateTime('approved_date')->nullable();
                $table->enum('status', ['pending','approved','rejected','deleted'])->default('pending');
                $table->foreignId('created_by')->constrained('administrativeaccounts', 'admin_id')->onUpdate('cascade')->onDelete('restrict');
                $table->timestamps();
                $table->index(['employeeprofiles_id', 'status']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_advances');
    }
};

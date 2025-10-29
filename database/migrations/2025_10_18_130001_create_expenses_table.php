<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id('expense_id');
            $table->unsignedBigInteger('employeeprofiles_id')->nullable();
            $table->unsignedBigInteger('supplier_id')->nullable();
            $table->string('expense_name');
            $table->enum('category', ['Utilities','Maintenance','Transportation','Office Supplies','Other']);
            $table->decimal('amount', 12, 2);
            $table->date('expense_date');
            $table->string('paid_to')->nullable();
            $table->enum('payment_method', ['Cash','Bank Transfer','GCash','Check','Other']);
            $table->string('reference_number')->nullable();
            $table->text('description')->nullable();
            $table->text('remarks')->nullable();
            $table->enum('status', ['Unpaid','Paid','Overdue'])->default('Unpaid');
            $table->timestamps();

            $table->index(['employeeprofiles_id']);
            $table->index(['supplier_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};

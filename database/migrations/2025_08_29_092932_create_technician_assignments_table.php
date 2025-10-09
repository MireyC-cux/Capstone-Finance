<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('technician_assignments', function (Blueprint $table) {
            $table->id('assignment_id');

            // item FK
            $table->foreignId('item_id')
                  ->constrained('service_request_items', 'item_id')
                  ->cascadeOnDelete()
                  ->cascadeOnUpdate();

            // link to employeeprofiles as technician
            $table->foreignId('technician_id')
                  ->constrained('employeeprofiles', 'employeeprofiles_id')
                  ->cascadeOnDelete()
                  ->cascadeOnUpdate();

            $table->enum('role', ['Lead', 'Assistant'])->default('Assistant');
            $table->enum('status', ['Assigned', 'Completed'])->default('Assigned');

            $table->timestamps();

            $table->index(['item_id', 'technician_id']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('technician_assignments');
    }
};
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('service_request_items', function (Blueprint $table) {
            $table->id('item_id');

            // Link to service_requests
            $table->foreignId('service_request_id')
                  ->constrained('service_requests', 'service_request_id')
                  ->cascadeOnDelete()
                  ->cascadeOnUpdate();

            // Prefer normalized FKs for service + aircon type (avoid redundant free-text),
            // but keep a small free-text description for legacy/customs.
            $table->foreignId('services_id')->nullable()
                  ->constrained('services', 'services_id')
                  ->nullOnDelete()
                  ->cascadeOnUpdate();

            $table->foreignId('aircon_type_id')->nullable()
                  ->constrained('aircon_types', 'aircon_type_id')
                  ->nullOnDelete()
                  ->cascadeOnUpdate();

            $table->string('service_type')->nullable(); // fallback / human readable
            $table->integer('quantity')->default(1);

            // Price snapshot - store at creation (immutable history)
            $table->decimal('unit_price', 10, 2)->nullable();
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('tax', 10, 2)->default(0);
            $table->decimal('line_total', 12, 2)->nullable();

            // Schedule per item (overrides header when provided)
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();

            // Technician & status
            $table->foreignId('assigned_technician_id')->nullable()
                  ->constrained('employeeprofiles', 'employeeprofiles_id')
                  ->nullOnDelete()
                  ->cascadeOnUpdate();

            $table->enum('status', ['Pending', 'In Progress', 'Completed'])->default('Pending');

            // Notes
            $table->text('service_notes')->nullable();

            $table->timestamps();

            // Helpful indexes
            $table->index(
                ['service_request_id', 'services_id', 'assigned_technician_id', 'status'],
                'srv_req_items_idx' // short, descriptive, <= 64 chars
            );
        });
    }

    public function down(): void {
        Schema::dropIfExists('service_request_items');
    }
};
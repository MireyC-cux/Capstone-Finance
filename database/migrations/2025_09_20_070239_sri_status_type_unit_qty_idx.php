<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        // Indexes for service_request_items
        Schema::table('service_request_items', function (Blueprint $table) {
            $table->index(
                ['status', 'service_type', 'unit_type', 'quantity'],
                'sri_status_type_unit_qty_idx' // custom short name
            );
            $table->index('service_request_id', 'sri_request_id_idx');
            $table->index('assigned_technician_id', 'sri_technician_id_idx');
        });

        // Indexes for technician_assignments
        Schema::table('technician_assignments', function (Blueprint $table) {
            $table->index(['technician_id', 'status'], 'ta_tech_status_idx');
            $table->index('item_id', 'ta_item_id_idx'); // Assuming item_id is service_request_item_id
        });

        // Indexes for service_requests
        Schema::table('service_requests', function (Blueprint $table) {
            $table->index(['start_date', 'end_date', 'service_date'], 'sr_dates_idx');
        });

        // New service_stats table (precomputed stats)
        Schema::create('service_stats', function (Blueprint $table) {
            $table->id();  // stats_id
            $table->string('service_type');
            $table->string('unit_type')->nullable();
            $table->integer('quantity');
            $table->integer('avg_minutes')->default(0);  // From SQL A/B
            $table->integer('samples')->default(0);      // COUNT(*)
            $table->decimal('avg_techs', 3, 1)->default(0);  // From SQL C
            $table->integer('mode_techs')->default(1);   // Most frequent tech count
            $table->integer('avg_minutes_per_unit')->default(0);  // Fallback
            $table->timestamps();

            $table->unique(
                ['service_type', 'unit_type', 'quantity'],
                'ss_type_unit_qty_idx' // shorter custom name
            );
        });
    }

    public function down(): void {
        Schema::dropIfExists('service_stats');

        Schema::table('service_request_items', function (Blueprint $table) {
            $table->dropIndex('sri_status_type_unit_qty_idx');
            $table->dropIndex('sri_request_id_idx');
            $table->dropIndex('sri_technician_id_idx');
        });

        Schema::table('technician_assignments', function (Blueprint $table) {
            $table->dropIndex('ta_tech_status_idx');
            $table->dropIndex('ta_item_id_idx');
        });

        Schema::table('service_requests', function (Blueprint $table) {
            $table->dropIndex('sr_dates_idx');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // ✅ Indexes for service_request_items
        Schema::table('service_request_items', function (Blueprint $table) {
            // Removed non-existent columns
            $table->index(
                ['status', 'service_type', 'quantity'],
                'sri_status_type_qty_idx'
            );

            $table->index('service_request_id', 'sri_request_id_idx');
            $table->index('assigned_technician_id', 'sri_technician_id_idx');
        });

        // ✅ Indexes for technician_assignments
        Schema::table('technician_assignments', function (Blueprint $table) {
            $table->index(['technician_id', 'status'], 'ta_tech_status_idx');
            $table->index('item_id', 'ta_item_id_idx'); // assuming item_id = service_request_item_id
        });

        // ✅ Indexes for service_requests (only valid columns)
        Schema::table('service_requests', function (Blueprint $table) {
            if (Schema::hasColumn('service_requests', 'service_date')) {
                $table->index('service_date', 'sr_service_date_idx');
            }
        });

        // ✅ New service_stats table
        Schema::create('service_stats', function (Blueprint $table) {
            $table->id();  // stats_id
            $table->string('service_type');
            $table->string('unit_type')->nullable();
            $table->integer('quantity');
            $table->integer('avg_minutes')->default(0);
            $table->integer('samples')->default(0);
            $table->decimal('avg_techs', 3, 1)->default(0);
            $table->integer('mode_techs')->default(1);
            $table->integer('avg_minutes_per_unit')->default(0);
            $table->timestamps();

            $table->unique(
                ['service_type', 'unit_type', 'quantity'],
                'ss_type_unit_qty_idx'
            );
        });
    }

    public function down(): void
    {
        // ✅ Drop the service_stats table
        Schema::dropIfExists('service_stats');

        // ✅ Drop service_request_items indexes
        Schema::table('service_request_items', function (Blueprint $table) {
            $table->dropIndex('sri_status_type_qty_idx');
            $table->dropIndex('sri_request_id_idx');
            $table->dropIndex('sri_technician_id_idx');
        });

        // ✅ Drop technician_assignments indexes
        Schema::table('technician_assignments', function (Blueprint $table) {
            $table->dropIndex('ta_tech_status_idx');
            $table->dropIndex('ta_item_id_idx');
        });

        // ✅ Drop service_requests index (check before dropping)
        Schema::table('service_requests', function (Blueprint $table) {
            if (Schema::hasColumn('service_requests', 'service_date')) {
                $table->dropIndex('sr_service_date_idx');
            }
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('billings', function (Blueprint $table) {
            if (!Schema::hasColumn('billings', 'meta')) {
                $table->json('meta')->nullable()->after('status');
            }
        });

        Schema::table('billings', function (Blueprint $table) {
            // Add foreign keys if missing
            $table->foreign('service_request_id')
                ->references('service_request_id')
                ->on('service_requests')
                ->onDelete('cascade');

            $table->foreign('customer_id')
                ->references('customer_id')
                ->on('customers')
                ->onDelete('cascade');
        });
    }

    public function down(): void {
        Schema::table('billings', function (Blueprint $table) {
            // Drop FKs then column
            try { $table->dropForeign(['service_request_id']); } catch (\Throwable $e) {}
            try { $table->dropForeign(['customer_id']); } catch (\Throwable $e) {}
            if (Schema::hasColumn('billings', 'meta')) {
                $table->dropColumn('meta');
            }
        });
    }
};

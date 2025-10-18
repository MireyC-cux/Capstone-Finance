<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('service_request_items', function (Blueprint $table) {
            if (!Schema::hasColumn('service_request_items', 'billed')) {
                $table->boolean('billed')->nullable()->after('status');
                $table->index(['billed', 'status']);
            }
        });
    }

    public function down(): void {
        Schema::table('service_request_items', function (Blueprint $table) {
            if (Schema::hasColumn('service_request_items', 'billed')) {
                $table->dropIndex(['billed', 'status']);
                $table->dropColumn('billed');
            }
        });
    }
};

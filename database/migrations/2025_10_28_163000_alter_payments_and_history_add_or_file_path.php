<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('payments_received') && !Schema::hasColumn('payments_received', 'or_file_path')) {
            Schema::table('payments_received', function (Blueprint $table) {
                $table->string('or_file_path')->nullable()->after('reference_number');
            });
        }

        if (Schema::hasTable('payment_history') && !Schema::hasColumn('payment_history', 'or_file_path')) {
            Schema::table('payment_history', function (Blueprint $table) {
                $table->string('or_file_path')->nullable()->after('status');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('payments_received') && Schema::hasColumn('payments_received', 'or_file_path')) {
            Schema::table('payments_received', function (Blueprint $table) {
                $table->dropColumn('or_file_path');
            });
        }

        if (Schema::hasTable('payment_history') && Schema::hasColumn('payment_history', 'or_file_path')) {
            Schema::table('payment_history', function (Blueprint $table) {
                $table->dropColumn('or_file_path');
            });
        }
    }
};

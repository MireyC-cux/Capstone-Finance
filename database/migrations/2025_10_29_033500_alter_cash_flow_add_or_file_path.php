<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('cash_flow') && !Schema::hasColumn('cash_flow', 'or_file_path')) {
            Schema::table('cash_flow', function (Blueprint $table) {
                $table->string('or_file_path')->nullable()->after('description');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('cash_flow') && Schema::hasColumn('cash_flow', 'or_file_path')) {
            Schema::table('cash_flow', function (Blueprint $table) {
                $table->dropColumn('or_file_path');
            });
        }
    }
};

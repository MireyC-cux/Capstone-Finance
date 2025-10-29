<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('payments_received') && !Schema::hasColumn('payments_received', 'payment_type')) {
            Schema::table('payments_received', function (Blueprint $table) {
                $table->enum('payment_type', ['Full','Partial'])->default('Full')->after('payment_method');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('payments_received') && Schema::hasColumn('payments_received', 'payment_type')) {
            Schema::table('payments_received', function (Blueprint $table) {
                $table->dropColumn('payment_type');
            });
        }
    }
};

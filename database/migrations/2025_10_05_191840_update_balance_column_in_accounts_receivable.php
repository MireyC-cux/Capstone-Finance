<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, drop the existing virtual column if it exists
        if (Schema::hasColumn('accounts_receivable', 'balance')) {
            Schema::table('accounts_receivable', function (Blueprint $table) {
                $table->dropColumn('balance');
            });
        }

        // Add the balance column as a virtual column
        Schema::table('accounts_receivable', function (Blueprint $table) {
            $table->decimal('balance', 12, 2)->virtualAs('total_amount - amount_paid')->after('amount_paid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the virtual column
        if (Schema::hasColumn('accounts_receivable', 'balance')) {
            Schema::table('accounts_receivable', function (Blueprint $table) {
                $table->dropColumn('balance');
            });
        }
    }
};

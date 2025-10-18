<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cash_flow', function (Blueprint $table) {
            $table->unsignedBigInteger('account_id')->nullable()->after('source_id');
            $table->foreign('account_id')->references('account_id')->on('cash_accounts')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('cash_flow', function (Blueprint $table) {
            $table->dropForeign(['account_id']);
            $table->dropColumn('account_id');
        });
    }
};

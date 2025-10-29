<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('expenses') && !Schema::hasColumn('expenses', 'status')) {
            Schema::table('expenses', function (Blueprint $table) {
                $table->enum('status', ['Unpaid','Paid','Overdue'])->default('Unpaid')->after('remarks');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('expenses') && Schema::hasColumn('expenses', 'status')) {
            Schema::table('expenses', function (Blueprint $table) {
                $table->dropColumn('status');
            });
        }
    }
};

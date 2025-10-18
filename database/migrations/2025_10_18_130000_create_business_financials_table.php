<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_financials', function (Blueprint $table) {
            $table->id('financial_id');
            $table->decimal('capital', 12, 2)->default(0.00);
            $table->decimal('total_inflows', 12, 2)->default(0.00);
            $table->decimal('total_outflows', 12, 2)->default(0.00);
            $table->decimal('current_balance', 12, 2)->storedAs('capital + total_inflows - total_outflows');
            $table->decimal('profit', 12, 2)->storedAs('total_inflows - total_outflows');
            $table->date('as_of_date')->default(DB::raw('CURRENT_DATE'));
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_financials');
    }
};

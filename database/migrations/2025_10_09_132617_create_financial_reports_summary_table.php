<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_reports_summary', function (Blueprint $table) {
            $table->id('report_id');
            $table->enum('report_type', [
                'Cash Flow',
                'Income Statement',
                'Expense Report',
                'AR Aging',
                'AP Aging'
            ])->notNullable();
            $table->date('period_start')->notNullable();
            $table->date('period_end')->notNullable();
            $table->decimal('total_income', 12, 2)->default(0.00);
            $table->decimal('total_expenses', 12, 2)->default(0.00);

            // We'll add the generated column using a raw statement after table creation
            $table->timestamp('generated_at')->useCurrent();
        });

        // Add the generated column (since Laravel Schema builder doesn't yet fully support STORED generated columns)
        DB::statement("
            ALTER TABLE financial_reports_summary
            ADD COLUMN net_cashflow DECIMAL(12,2)
            GENERATED ALWAYS AS (total_income - total_expenses) STORED
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_reports_summary');
    }
};

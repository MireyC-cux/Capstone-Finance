<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop legacy columns if they exist
        if (Schema::hasColumn('billings', 'due_date')) {
            Schema::table('billings', function (Blueprint $table) { $table->dropColumn('due_date'); });
        }
        if (Schema::hasColumn('billings', 'total_amount')) {
            Schema::table('billings', function (Blueprint $table) { $table->dropColumn('total_amount'); });
        }
        if (Schema::hasColumn('billings', 'discount')) {
            Schema::table('billings', function (Blueprint $table) { $table->dropColumn('discount'); });
        }
        if (Schema::hasColumn('billings', 'tax')) {
            Schema::table('billings', function (Blueprint $table) { $table->dropColumn('tax'); });
        }
        if (Schema::hasColumn('billings', 'approval_note')) {
            Schema::table('billings', function (Blueprint $table) { $table->dropColumn('approval_note'); });
        }
        if (Schema::hasColumn('billings', 'approved_by')) {
            Schema::table('billings', function (Blueprint $table) { $table->dropColumn('approved_by'); });
        }
        if (Schema::hasColumn('billings', 'approved_at')) {
            Schema::table('billings', function (Blueprint $table) { $table->dropColumn('approved_at'); });
        }
        if (Schema::hasColumn('billings', 'approval_status')) {
            Schema::table('billings', function (Blueprint $table) { $table->dropColumn('approval_status'); });
        }

        // Ensure desired columns exist
        if (!Schema::hasColumn('billings', 'generate_invoice_after_approval')) {
            Schema::table('billings', function (Blueprint $table) {
                $table->boolean('generate_invoice_after_approval')->default(true)->after('status');
            });
        }
        if (!Schema::hasColumn('billings', 'meta')) {
            Schema::table('billings', function (Blueprint $table) {
                $table->json('meta')->nullable()->after('generate_invoice_after_approval');
            });
        }

        // Update status enum to the new set
        if (Schema::hasColumn('billings', 'status')) {
            DB::statement("ALTER TABLE billings MODIFY status ENUM('Billed','Unbilled') NOT NULL DEFAULT 'Billed'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert status enum to the previous set
        if (Schema::hasColumn('billings', 'status')) {
            DB::statement("ALTER TABLE billings MODIFY status ENUM('Billed','Paid','Cancelled') NOT NULL DEFAULT 'Billed'");
        }

        // Re-add legacy columns (if missing)
        if (!Schema::hasColumn('billings', 'due_date')) {
            Schema::table('billings', function (Blueprint $table) { $table->date('due_date')->nullable(); });
        }
        if (!Schema::hasColumn('billings', 'total_amount')) {
            Schema::table('billings', function (Blueprint $table) { $table->decimal('total_amount', 12, 2)->nullable(); });
        }
        if (!Schema::hasColumn('billings', 'discount')) {
            Schema::table('billings', function (Blueprint $table) { $table->decimal('discount', 12, 2)->default(0); });
        }
        if (!Schema::hasColumn('billings', 'tax')) {
            Schema::table('billings', function (Blueprint $table) { $table->decimal('tax', 12, 2)->default(0); });
        }
        if (!Schema::hasColumn('billings', 'approval_status')) {
            Schema::table('billings', function (Blueprint $table) { $table->enum('approval_status', ['Pending','Approved','Rejected'])->default('Pending'); });
            Schema::table('billings', function (Blueprint $table) { $table->index('approval_status'); });
        }
        if (!Schema::hasColumn('billings', 'approval_note')) {
            Schema::table('billings', function (Blueprint $table) { $table->text('approval_note')->nullable(); });
        }
        if (!Schema::hasColumn('billings', 'approved_by')) {
            Schema::table('billings', function (Blueprint $table) { $table->unsignedBigInteger('approved_by')->nullable(); });
        }
        if (!Schema::hasColumn('billings', 'approved_at')) {
            Schema::table('billings', function (Blueprint $table) { $table->timestamp('approved_at')->nullable(); });
        }
    }
};

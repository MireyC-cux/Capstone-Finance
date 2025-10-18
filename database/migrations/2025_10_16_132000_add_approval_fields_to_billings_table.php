<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('billings', function (Blueprint $table) {
            $table->enum('approval_status', ['Pending','Approved','Rejected'])->default('Pending')->after('total_amount');
            $table->boolean('generate_invoice_after_approval')->default(true)->after('approval_status');
            $table->text('approval_note')->nullable()->after('generate_invoice_after_approval');
            $table->unsignedBigInteger('approved_by')->nullable()->after('approval_note');
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->index('approval_status');
        });
    }

    public function down(): void {
        Schema::table('billings', function (Blueprint $table) {
            $table->dropColumn(['approval_status','generate_invoice_after_approval','approval_note','approved_by','approved_at']);
        });
    }
};

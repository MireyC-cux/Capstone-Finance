<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('accounts_payable', function (Blueprint $table) {
            if (!Schema::hasColumn('accounts_payable', 'purchase_order_id')) {
                $table->unsignedBigInteger('purchase_order_id')->nullable()->after('supplier_id');
            }
            $table->foreign('purchase_order_id')
                ->references('purchase_order_id')->on('purchase_orders')
                ->onDelete('set null')
                ->onUpdate('cascade');
        });
    }

    public function down()
    {
        Schema::table('accounts_payable', function (Blueprint $table) {
            $table->dropForeign(['purchase_order_id']);
        });
    }
};

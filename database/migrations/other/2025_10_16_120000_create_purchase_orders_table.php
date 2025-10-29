<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->bigIncrements('purchase_order_id');
            $table->string('po_number')->unique(); // PO-0001 continuous
            $table->unsignedBigInteger('supplier_id');
            $table->unsignedBigInteger('service_request_item_id')->nullable();
            $table->date('order_date');
            $table->date('delivery_date')->nullable();
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->enum('status', ['pending','delivered','cancelled'])->default('pending');
            $table->enum('payment_status', ['unpaid','partial','paid'])->default('unpaid');
            $table->json('items')->nullable(); // unit/parts ordered
            $table->unsignedBigInteger('created_by')->nullable(); // admin_id
            $table->timestamps();

            $table->foreign('supplier_id')->references('supplier_id')->on('suppliers');
            $table->foreign('service_request_item_id')->references('item_id')->on('service_request_items');
            $table->foreign('created_by')->references('admin_id')->on('administrativeaccounts');
        });
    }
    public function down(): void {
        Schema::dropIfExists('purchase_orders');
    }
};

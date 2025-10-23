<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            // Administrative / workflow fields
            $table->unsignedBigInteger('approved_by')->nullable()->after('created_by');
            $table->string('department', 120)->nullable()->after('approved_by');
            $table->string('reason', 255)->nullable()->after('department');
            $table->text('remarks')->nullable()->after('reason');

            // Terms
            $table->string('payment_terms', 150)->nullable()->after('remarks');
            $table->string('delivery_terms', 150)->nullable()->after('payment_terms');
            $table->string('warranty_policy', 200)->nullable()->after('delivery_terms');

            // Financial breakdown
            $table->decimal('subtotal', 12, 2)->nullable()->after('warranty_policy');
            $table->decimal('discounts', 12, 2)->nullable()->after('subtotal');
            $table->decimal('tax', 12, 2)->nullable()->after('discounts');
            $table->decimal('shipping_fee', 12, 2)->nullable()->after('tax');

            // New: Internal orders and validation overrides
            $table->boolean('is_internal_order')->default(false)->after('supplier_id');
            $table->boolean('ignore_supplier_filter')->default(false)->after('is_internal_order');

            // New: Link the PO to a full service request (not just an item)
            $table->unsignedBigInteger('service_request_id')->nullable()->after('service_request_item_id');

            // New: Delivery auditing and inventory integration markers
            $table->timestamp('delivered_at')->nullable()->after('delivery_date');
            $table->unsignedBigInteger('delivered_by')->nullable()->after('delivered_at');

            // New: Snapshot of insufficient items computed at PO creation
            $table->json('insufficient_items')->nullable()->after('items');

            // Foreign keys
            $table->foreign('approved_by')->references('admin_id')->on('administrativeaccounts');
            $table->foreign('service_request_id')->references('service_request_id')->on('service_requests');
            $table->foreign('delivered_by')->references('admin_id')->on('administrativeaccounts');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropForeign(['service_request_id']);
            $table->dropForeign(['delivered_by']);
            $table->dropColumn([
                'approved_by','department','reason','remarks',
                'payment_terms','delivery_terms','warranty_policy',
                'subtotal','discounts','tax','shipping_fee',
                'is_internal_order','ignore_supplier_filter',
                'service_request_id','delivered_at','delivered_by',
                'insufficient_items'
            ]);
        });
    }
};


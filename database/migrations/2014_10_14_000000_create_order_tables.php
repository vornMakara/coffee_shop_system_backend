<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('order_number', 30)->unique();
            $table->uuid('branch_id')->nullable();
            $table->uuid('shift_id')->nullable();
            $table->uuid('user_id');
            $table->uuid('customer_id')->nullable();
            $table->uuid('table_id')->nullable();
            $table->uuid('reservation_id')->nullable();
            $table->uuid('parent_order_id')->nullable();
            $table->enum('split_type', ['none', 'even', 'item'])->default('none');
            $table->integer('guest_count')->default(1);
            $table->enum('order_type', ['dine_in', 'takeaway', 'delivery', 'drive_thru'])->default('dine_in');
            $table->enum('status', ['pending', 'confirmed', 'preparing', 'ready', 'completed', 'cancelled', 'voided'])->default('pending');
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->string('coupon_code', 50)->nullable();
            $table->decimal('coupon_discount', 12, 2)->default(0);
            $table->decimal('promotion_discount', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('tip_amount', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->integer('points_earned')->default(0);
            $table->integer('points_redeemed')->default(0);
            $table->text('notes')->nullable();
            $table->boolean('is_held')->default(false);
            $table->dateTime('held_at')->nullable();
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('order_id');
            $table->uuid('product_id')->nullable();
            $table->string('product_name', 150);
            $table->string('product_sku', 100)->nullable();
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 10, 2);
            $table->decimal('cost_price', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('line_total', 12, 2)->default(0);
            $table->json('selected_modifiers')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_voided')->default(false);
            $table->dateTime('voided_at')->nullable();
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }
    public function down() {
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
    }
};

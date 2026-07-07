<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('sales', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('sale_number', 30)->unique();
            $table->uuid('order_id');
            $table->uuid('branch_id')->nullable();
            $table->uuid('shift_id')->nullable();
            $table->uuid('user_id');
            $table->uuid('customer_id')->nullable();
            $table->uuid('table_id')->nullable();
            $table->string('order_type', 20);
            $table->decimal('subtotal', 12, 2);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('coupon_discount', 12, 2)->default(0);
            $table->decimal('promotion_discount', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('tip_amount', 12, 2)->default(0);
            $table->decimal('total', 12, 2);
            $table->decimal('total_cost', 12, 2)->default(0);
            $table->decimal('gross_profit', 12, 2)->default(0);
            $table->integer('points_earned')->default(0);
            $table->integer('points_redeemed')->default(0);
            $table->text('notes')->nullable();
            $table->date('sale_date');
            $table->date('business_date')->nullable();
            $table->uuid('created_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down() {
        Schema::dropIfExists('sales');
    }
};

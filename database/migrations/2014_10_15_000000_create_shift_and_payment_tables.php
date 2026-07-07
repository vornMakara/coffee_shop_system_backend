<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('shifts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('branch_id')->nullable();
            $table->uuid('user_id');
            $table->decimal('opening_cash', 12, 2)->default(0);
            $table->decimal('closing_cash', 12, 2)->nullable();
            $table->decimal('expected_cash', 12, 2)->nullable();
            $table->decimal('cash_difference', 12, 2)->nullable();
            $table->decimal('total_cash_in', 12, 2)->default(0);
            $table->decimal('total_cash_out', 12, 2)->default(0);
            $table->decimal('total_sales', 12, 2)->default(0);
            $table->integer('total_orders')->default(0);
            $table->text('notes')->nullable();
            $table->enum('status', ['open', 'closed'])->default('open');
            $table->timestamp('opened_at')->useCurrent();
            $table->timestamp('closed_at')->nullable();
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->timestamps();
        });

        Schema::create('shift_cash_movements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('shift_id');
            $table->enum('type', ['in', 'out']);
            $table->decimal('amount', 12, 2);
            $table->text('note')->nullable();
            $table->uuid('created_by')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('payment_methods', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('branch_id')->nullable();
            $table->string('name', 100);
            $table->enum('type', ['cash', 'card', 'qr', 'ewallet', 'credit', 'gift_card', 'bank_transfer'])->default('cash');
            $table->boolean('is_active')->default(true);
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('sale_payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('sale_id')->nullable();
            $table->uuid('payment_method_id');
            $table->uuid('gift_card_id')->nullable();
            $table->decimal('amount', 12, 2);
            $table->decimal('amount_tendered', 12, 2)->nullable();
            $table->decimal('change_amount', 12, 2)->default(0);
            $table->string('currency_code', 10)->default('USD');
            $table->decimal('exchange_rate', 10, 4)->default(1);
            $table->string('reference_no', 100)->nullable();
            $table->uuid('created_by')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }
    public function down() {
        Schema::dropIfExists('sale_payments');
        Schema::dropIfExists('payment_methods');
        Schema::dropIfExists('shift_cash_movements');
        Schema::dropIfExists('shifts');
    }
};

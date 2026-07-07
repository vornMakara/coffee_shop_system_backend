<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('customers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('first_name', 75);
            $table->string('last_name', 75)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('email', 150)->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->date('date_of_birth')->nullable();
            $table->text('address')->nullable();
            $table->text('avatar_url')->nullable();
            $table->integer('loyalty_points')->default(0);
            $table->decimal('credit_balance', 12, 2)->default(0);
            $table->decimal('total_spent', 14, 2)->default(0);
            $table->integer('visit_count')->default(0);
            $table->dateTime('last_visited_at')->nullable();
            $table->string('source', 30)->default('walk_in');
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('table_categories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('branch_id')->nullable();
            $table->string('name', 50);
            $table->text('description')->nullable();
            $table->string('color_hex', 7)->nullable();
            $table->integer('sort_order')->default(0);
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('tables', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('branch_id')->nullable();
            $table->uuid('table_category_id')->nullable();
            $table->string('number', 20);
            $table->string('name', 50)->nullable();
            $table->integer('capacity')->default(2);
            $table->text('qr_code')->nullable();
            $table->enum('status', ['available', 'occupied', 'reserved', 'cleaning'])->default('available');
            $table->string('floor', 50)->nullable();
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down() {
        Schema::dropIfExists('tables');
        Schema::dropIfExists('table_categories');
        Schema::dropIfExists('customers');
    }
};

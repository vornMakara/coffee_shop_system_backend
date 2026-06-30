<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('branch_id')->nullable();
            $table->uuid('role_id')->nullable();
            $table->string('username', 50)->unique();
            $table->string('first_name', 75);
            $table->string('last_name', 75);
            $table->string('email', 150)->unique();
            $table->string('phone', 20)->nullable();
            $table->string('password');
            $table->text('avatar_url')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->boolean('is_active')->default(true);
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });
    }
    public function down() {
        Schema::dropIfExists('users');
    }
};

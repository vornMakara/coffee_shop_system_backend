<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::table('shifts', function (Blueprint $table) {
            $table->decimal('opening_cash_khr', 15, 2)->default(0)->after('opening_cash');
            $table->decimal('closing_cash_khr', 15, 2)->nullable()->after('closing_cash');
            $table->decimal('expected_cash_khr', 15, 2)->nullable()->after('expected_cash');
            $table->decimal('cash_difference_khr', 15, 2)->nullable()->after('cash_difference');
        });
    }

    public function down() {
        Schema::table('shifts', function (Blueprint $table) {
            $table->dropColumn([
                'opening_cash_khr',
                'closing_cash_khr',
                'expected_cash_khr',
                'cash_difference_khr'
            ]);
        });
    }
};

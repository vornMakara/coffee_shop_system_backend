<?php

use Illuminate\Support\Facades\Route;
use App\Modules\POS\Customer\Controllers\CustomerController;
use App\Modules\POS\Customer\Controllers\Admin\CustomerController as AdminCustomerController;

Route::group(['middleware' => ['auth:api']], function () {
    Route::get('customers', [CustomerController::class, 'index']);
});

Route::group(['prefix' => 'admin', 'middleware' => ['auth:api']], function () {
    Route::apiResource('customers', AdminCustomerController::class);
});

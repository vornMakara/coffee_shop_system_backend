<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Catalog\Product\Controllers\ProductController;
use App\Modules\Catalog\Product\Controllers\Admin\ProductController as AdminProductController;

Route::group(['middleware' => ['auth:api']], function () {
    Route::get('products', [ProductController::class, 'index']);
});

Route::group(['prefix' => 'admin', 'middleware' => ['auth:api']], function () {
    Route::apiResource('products', AdminProductController::class);
});

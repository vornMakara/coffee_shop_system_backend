<?php

use Illuminate\Support\Facades\Route;

Route::group([
    // 'prefix' => 'v1' (Handled globally by RouteServiceProvider)
], function () {
    // Catalog & Menu APIs
    Route::apiResource('categories', \App\Modules\Catalog\Controllers\CategoryController::class)->only(['index', 'show']);
    Route::apiResource('products', \App\Modules\Catalog\Controllers\ProductController::class)->only(['index', 'show']);
});

Route::group([
    'prefix' => 'admin',
    'middleware' => ['auth:api', 'permission:admin.catalog']
], function () {
    // Admin Catalog Management
    Route::post('categories', [\App\Modules\Catalog\Controllers\AdminCategoryController::class, 'store']);
    Route::put('categories/{id}', [\App\Modules\Catalog\Controllers\AdminCategoryController::class, 'update']);
    Route::delete('categories/{id}', [\App\Modules\Catalog\Controllers\AdminCategoryController::class, 'destroy']);

    Route::post('products', [\App\Modules\Catalog\Controllers\AdminProductController::class, 'store']);
    Route::put('products/{id}', [\App\Modules\Catalog\Controllers\AdminProductController::class, 'update']);
    Route::delete('products/{id}', [\App\Modules\Catalog\Controllers\AdminProductController::class, 'destroy']);
    Route::post('products/{id}/modifiers', [\App\Modules\Catalog\Controllers\AdminProductController::class, 'syncModifiers']);

    Route::get('modifiers', [\App\Modules\Catalog\Controllers\ModifierController::class, 'index']);
    Route::post('modifiers', [\App\Modules\Catalog\Controllers\ModifierController::class, 'store']);
    Route::put('modifiers/{id}', [\App\Modules\Catalog\Controllers\ModifierController::class, 'update']);
    Route::delete('modifiers/{id}', [\App\Modules\Catalog\Controllers\ModifierController::class, 'destroy']);
});

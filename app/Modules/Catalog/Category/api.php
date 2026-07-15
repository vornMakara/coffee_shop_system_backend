<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Catalog\Category\Controllers\CategoryController;
use App\Modules\Catalog\Category\Controllers\Admin\CategoryController as AdminCategoryController;

Route::group(['middleware' => ['auth:api']], function () {
    Route::get('categories', [CategoryController::class, 'index']);
});

Route::group(['prefix' => 'admin', 'middleware' => ['auth:api']], function () {
    Route::apiResource('categories', AdminCategoryController::class);
});

<?php

use Illuminate\Support\Facades\Route;
use App\Modules\POS\Table\Controllers\TableController;
use App\Modules\POS\Table\Controllers\Admin\TableController as AdminTableController;

Route::group(['middleware' => ['auth:api']], function () {
    Route::get('tables/categories', [TableController::class, 'categories']);
    Route::get('tables', [TableController::class, 'index']);
});

Route::group(['prefix' => 'admin', 'middleware' => ['auth:api']], function () {
    Route::apiResource('tables', AdminTableController::class);
});

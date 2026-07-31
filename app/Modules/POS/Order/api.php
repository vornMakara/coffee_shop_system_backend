<?php

use Illuminate\Support\Facades\Route;
use App\Modules\POS\Order\Controllers\OrderController;

Route::group(['middleware' => ['auth:api']], function () {
    Route::get('orders', [OrderController::class, 'index']);
    Route::post('orders', [OrderController::class, 'store']);
    Route::get('orders/{identifier}', [OrderController::class, 'show']);
    Route::patch('orders/{id}/status', [OrderController::class, 'updateStatus']);
    Route::delete('orders/{id}', [OrderController::class, 'destroy'])->middleware('permission:pos.void');
});

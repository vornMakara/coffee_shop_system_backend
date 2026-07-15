<?php

use Illuminate\Support\Facades\Route;
use App\Modules\POS\Payment\Controllers\PaymentController;

Route::group(['middleware' => ['auth:api']], function () {
    Route::post('orders/{id}/pay', [PaymentController::class, 'processPayment']);
});

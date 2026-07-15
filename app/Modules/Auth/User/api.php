<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Auth\User\Controllers\Admin\UserController;

Route::group(['prefix' => 'admin', 'middleware' => ['auth:api']], function () {
    Route::apiResource('users', UserController::class);
});

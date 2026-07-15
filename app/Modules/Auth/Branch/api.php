<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Auth\Branch\Controllers\Admin\BranchController;

Route::group(['prefix' => 'admin', 'middleware' => ['auth:api']], function () {
    Route::apiResource('branches', BranchController::class);
});

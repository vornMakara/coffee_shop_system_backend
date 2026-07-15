<?php

use Illuminate\Support\Facades\Route;
use App\Modules\POS\Shift\Controllers\ShiftController;

Route::group(['middleware' => ['auth:api']], function () {
    Route::get('shifts/current', [ShiftController::class, 'current']);
    Route::post('shifts/open', [ShiftController::class, 'open']);
});

<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Catalog\Modifier\Controllers\Admin\ModifierController as AdminModifierController;

Route::group(['prefix' => 'admin', 'middleware' => ['auth:api']], function () {
    Route::apiResource('modifiers', AdminModifierController::class);
    Route::post('{id}/modifiers', [AdminModifierController::class, 'syncModifiers']);
});

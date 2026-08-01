<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Auth\Role\Controllers\Admin\RoleController;

Route::group(['prefix' => 'admin', 'middleware' => ['auth:api', 'permission:admin.roles']], function () {
    Route::get('permissions', [RoleController::class, 'permissions']);
    Route::apiResource('roles', RoleController::class)->except(['show', 'destroy']);
    Route::put('roles/{id}/permissions', [RoleController::class, 'updatePermissions']);
});

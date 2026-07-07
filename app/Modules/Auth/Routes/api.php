<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Auth\Controllers\UserController;
use App\Modules\Auth\Controllers\BranchController;

Route::group([
    'prefix' => 'admin',
    'middleware' => ['auth:api', 'permission:admin.users']
], function () {
    // Branch Management
    Route::get('branches', [BranchController::class, 'index']);
    Route::post('branches', [BranchController::class, 'store']);
    Route::put('branches/{id}', [BranchController::class, 'update']);
    Route::delete('branches/{id}', [BranchController::class, 'destroy']);

    // User Management
    Route::get('users', [UserController::class, 'index']);
    Route::post('users', [UserController::class, 'store']);
    Route::put('users/{id}', [UserController::class, 'update']);
    Route::delete('users/{id}', [UserController::class, 'destroy']);
});

<?php

use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->group(function () {
    // Orders
    Route::post('orders', [\App\Modules\POS\Controllers\OrderController::class, 'store'])->middleware('permission:pos.access');
    Route::get('orders', [\App\Modules\POS\Controllers\OrderController::class, 'index'])->middleware('permission:pos.access');
    Route::get('orders/{identifier}', [\App\Modules\POS\Controllers\OrderController::class, 'show'])->middleware('permission:pos.access');
    Route::patch('orders/{id}/status', [\App\Modules\POS\Controllers\OrderController::class, 'updateStatus'])->middleware('permission:pos.access');
    Route::post('orders/{id}/pay', [\App\Modules\POS\Controllers\PaymentController::class, 'processPayment'])->middleware('permission:pos.payments');
    Route::delete('orders/{id}', [\App\Modules\POS\Controllers\OrderController::class, 'destroy'])->middleware('permission:pos.void');

    // Shifts
    Route::get('shifts/current', [\App\Modules\POS\Controllers\ShiftController::class, 'current'])->middleware('permission:pos.shifts');
    Route::post('shifts/open', [\App\Modules\POS\Controllers\ShiftController::class, 'open'])->middleware('permission:pos.shifts');

    // Roles and Permissions (Admin)
    Route::get('permissions', [\App\Modules\Auth\Controllers\PermissionController::class, 'index'])->middleware('permission:admin.roles');
    Route::get('roles', [\App\Modules\Auth\Controllers\RoleController::class, 'index'])->middleware('permission:admin.roles');
    Route::post('roles', [\App\Modules\Auth\Controllers\RoleController::class, 'store'])->middleware('permission:admin.roles');
    Route::put('roles/{id}/permissions', [\App\Modules\Auth\Controllers\RoleController::class, 'updatePermissions'])->middleware('permission:admin.roles');

    // Tables
    Route::get('tables/categories', [\App\Modules\POS\Controllers\TableController::class, 'categories']);
    Route::get('tables', [\App\Modules\POS\Controllers\TableController::class, 'index']);

    // Customers
    Route::get('customers', [\App\Modules\POS\Controllers\CustomerController::class, 'index']);
});

Route::group([
    'prefix' => 'admin',
    'middleware' => ['auth:api']
], function () {
    // Admin Table Management
    Route::post('tables', [\App\Modules\POS\Controllers\AdminTableController::class, 'store'])->middleware('permission:admin.pos_setup');
    Route::put('tables/{id}', [\App\Modules\POS\Controllers\AdminTableController::class, 'update'])->middleware('permission:admin.pos_setup');
    Route::delete('tables/{id}', [\App\Modules\POS\Controllers\AdminTableController::class, 'destroy'])->middleware('permission:admin.pos_setup');

    // Admin Customer Management
    Route::post('customers', [\App\Modules\POS\Controllers\CustomerController::class, 'store'])->middleware('permission:admin.customers');
    Route::put('customers/{id}', [\App\Modules\POS\Controllers\CustomerController::class, 'update'])->middleware('permission:admin.customers');
    Route::delete('customers/{id}', [\App\Modules\POS\Controllers\CustomerController::class, 'destroy'])->middleware('permission:admin.customers');
});

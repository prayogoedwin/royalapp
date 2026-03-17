<?php

use App\Http\Controllers\DivisionController;
use App\Http\Controllers\EmployeeTypeController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\PositionController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\Settings;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::get('settings/profile', [Settings\ProfileController::class, 'edit'])->name('settings.profile.edit');
    Route::put('settings/profile', [Settings\ProfileController::class, 'update'])->name('settings.profile.update');
    Route::delete('settings/profile', [Settings\ProfileController::class, 'destroy'])->name('settings.profile.destroy');
    Route::get('settings/password', [Settings\PasswordController::class, 'edit'])->name('settings.password.edit');
    Route::put('settings/password', [Settings\PasswordController::class, 'update'])->name('settings.password.update');
    Route::get('settings/appearance', [Settings\AppearanceController::class, 'edit'])->name('settings.appearance.edit');
    Route::put('settings/appearance', [Settings\AppearanceController::class, 'update'])->name('settings.appearance.update');

    // Roles Management - dengan permission check
    Route::get('roles', [RoleController::class, 'index'])->name('roles.index')->middleware('permission:view-roles');
    Route::get('roles/export', [RoleController::class, 'export'])->name('roles.export')->middleware('permission:download-roles');
    Route::get('roles/create', [RoleController::class, 'create'])->name('roles.create')->middleware('permission:create-roles');
    Route::post('roles', [RoleController::class, 'store'])->name('roles.store')->middleware('permission:create-roles');
    Route::get('roles/{role}', [RoleController::class, 'show'])->name('roles.show')->middleware('permission:show-roles');
    Route::get('roles/{role}/edit', [RoleController::class, 'edit'])->name('roles.edit')->middleware('permission:edit-roles');
    Route::put('roles/{role}', [RoleController::class, 'update'])->name('roles.update')->middleware('permission:edit-roles');
    Route::delete('roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy')->middleware('permission:delete-roles');
    
    // Permissions Management - dengan permission check
    Route::get('permissions', [PermissionController::class, 'index'])->name('permissions.index')->middleware('permission:view-permissions');
    Route::get('permissions/export', [PermissionController::class, 'export'])->name('permissions.export')->middleware('permission:download-permissions');
    Route::get('permissions/create', [PermissionController::class, 'create'])->name('permissions.create')->middleware('permission:create-permissions');
    Route::post('permissions', [PermissionController::class, 'store'])->name('permissions.store')->middleware('permission:create-permissions');
    Route::get('permissions/{permission}', [PermissionController::class, 'show'])->name('permissions.show')->middleware('permission:show-permissions');
    Route::get('permissions/{permission}/edit', [PermissionController::class, 'edit'])->name('permissions.edit')->middleware('permission:edit-permissions');
    Route::put('permissions/{permission}', [PermissionController::class, 'update'])->name('permissions.update')->middleware('permission:edit-permissions');
    Route::delete('permissions/{permission}', [PermissionController::class, 'destroy'])->name('permissions.destroy')->middleware('permission:delete-permissions');
    
    // Users Management - dengan permission check
    Route::get('users', [UserController::class, 'index'])->name('users.index')->middleware('permission:view-users');
    Route::get('users/export', [UserController::class, 'export'])->name('users.export')->middleware('permission:download-users');
    Route::get('users/create', [UserController::class, 'create'])->name('users.create')->middleware('permission:create-users');
    Route::post('users', [UserController::class, 'store'])->name('users.store')->middleware('permission:create-users');
    Route::get('users/{user}', [UserController::class, 'show'])->name('users.show')->middleware('permission:show-users');
    Route::get('users/{user}/edit', [UserController::class, 'edit'])->name('users.edit')->middleware('permission:edit-users');
    Route::put('users/{user}', [UserController::class, 'update'])->name('users.update')->middleware('permission:edit-users');
    Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy')->middleware('permission:delete-users');

    // Positions Management
    Route::get('positions', [PositionController::class, 'index'])->name('positions.index')->middleware('permission:view-positions');
    Route::get('positions/create', [PositionController::class, 'create'])->name('positions.create')->middleware('permission:create-positions');
    Route::post('positions', [PositionController::class, 'store'])->name('positions.store')->middleware('permission:create-positions');
    Route::get('positions/{position}', [PositionController::class, 'show'])->name('positions.show')->middleware('permission:show-positions');
    Route::get('positions/{position}/edit', [PositionController::class, 'edit'])->name('positions.edit')->middleware('permission:edit-positions');
    Route::put('positions/{position}', [PositionController::class, 'update'])->name('positions.update')->middleware('permission:edit-positions');
    Route::delete('positions/{position}', [PositionController::class, 'destroy'])->name('positions.destroy')->middleware('permission:delete-positions');

    // Divisions Management
    Route::get('divisions', [DivisionController::class, 'index'])->name('divisions.index')->middleware('permission:view-divisions');
    Route::get('divisions/create', [DivisionController::class, 'create'])->name('divisions.create')->middleware('permission:create-divisions');
    Route::post('divisions', [DivisionController::class, 'store'])->name('divisions.store')->middleware('permission:create-divisions');
    Route::get('divisions/{division}', [DivisionController::class, 'show'])->name('divisions.show')->middleware('permission:show-divisions');
    Route::get('divisions/{division}/edit', [DivisionController::class, 'edit'])->name('divisions.edit')->middleware('permission:edit-divisions');
    Route::put('divisions/{division}', [DivisionController::class, 'update'])->name('divisions.update')->middleware('permission:edit-divisions');
    Route::delete('divisions/{division}', [DivisionController::class, 'destroy'])->name('divisions.destroy')->middleware('permission:delete-divisions');

    // Employee Types Management
    Route::get('employee-types', [EmployeeTypeController::class, 'index'])->name('employee-types.index')->middleware('permission:view-employee-types');
    Route::get('employee-types/create', [EmployeeTypeController::class, 'create'])->name('employee-types.create')->middleware('permission:create-employee-types');
    Route::post('employee-types', [EmployeeTypeController::class, 'store'])->name('employee-types.store')->middleware('permission:create-employee-types');
    Route::get('employee-types/{employeeType}', [EmployeeTypeController::class, 'show'])->name('employee-types.show')->middleware('permission:show-employee-types');
    Route::get('employee-types/{employeeType}/edit', [EmployeeTypeController::class, 'edit'])->name('employee-types.edit')->middleware('permission:edit-employee-types');
    Route::put('employee-types/{employeeType}', [EmployeeTypeController::class, 'update'])->name('employee-types.update')->middleware('permission:edit-employee-types');
    Route::delete('employee-types/{employeeType}', [EmployeeTypeController::class, 'destroy'])->name('employee-types.destroy')->middleware('permission:delete-employee-types');

    // Employees Management
    Route::get('employees', [\App\Http\Controllers\EmployeeController::class, 'index'])->name('employees.index')->middleware('permission:view-employees');
    Route::get('employees/create', [\App\Http\Controllers\EmployeeController::class, 'create'])->name('employees.create')->middleware('permission:create-employees');
    Route::post('employees', [\App\Http\Controllers\EmployeeController::class, 'store'])->name('employees.store')->middleware('permission:create-employees');
    Route::get('employees/{employee}', [\App\Http\Controllers\EmployeeController::class, 'show'])->name('employees.show')->middleware('permission:show-employees');
    Route::get('employees/{employee}/edit', [\App\Http\Controllers\EmployeeController::class, 'edit'])->name('employees.edit')->middleware('permission:edit-employees');
    Route::put('employees/{employee}', [\App\Http\Controllers\EmployeeController::class, 'update'])->name('employees.update')->middleware('permission:edit-employees');
    Route::delete('employees/{employee}', [\App\Http\Controllers\EmployeeController::class, 'destroy'])->name('employees.destroy')->middleware('permission:delete-employees');

    // Orders Management
    Route::get('orders', [\App\Http\Controllers\OrderController::class, 'index'])->name('orders.index')->middleware('permission:view-orders');
    Route::get('orders/create', [\App\Http\Controllers\OrderController::class, 'create'])->name('orders.create')->middleware('permission:create-orders');
    Route::post('orders', [\App\Http\Controllers\OrderController::class, 'store'])->name('orders.store')->middleware('permission:create-orders');
    Route::get('orders/{order}', [\App\Http\Controllers\OrderController::class, 'show'])->name('orders.show')->middleware('permission:show-orders');
    Route::get('orders/{order}/edit', [\App\Http\Controllers\OrderController::class, 'edit'])->name('orders.edit')->middleware('permission:edit-orders');
    Route::put('orders/{order}', [\App\Http\Controllers\OrderController::class, 'update'])->name('orders.update')->middleware('permission:edit-orders');
    Route::delete('orders/{order}', [\App\Http\Controllers\OrderController::class, 'destroy'])->name('orders.destroy')->middleware('permission:delete-orders');
    Route::delete('order-photos/{photo}', [\App\Http\Controllers\OrderController::class, 'deletePhoto'])->name('order-photos.destroy')->middleware('permission:edit-orders');

    // Order Vehicle Issues
    Route::get('order-vehicle-issues', [\App\Http\Controllers\OrderVehicleIssueController::class, 'index'])
        ->name('order-vehicle-issues.index')
        ->middleware('permission:view-orders');
    Route::get('orders/{order}/vehicle-issues/create', [\App\Http\Controllers\OrderVehicleIssueController::class, 'create'])
        ->name('order-vehicle-issues.create')
        ->middleware('permission:edit-orders');
    Route::post('orders/{order}/vehicle-issues', [\App\Http\Controllers\OrderVehicleIssueController::class, 'store'])
        ->name('order-vehicle-issues.store')
        ->middleware('permission:edit-orders');
    Route::get('order-vehicle-issues/{orderVehicleIssue}', [\App\Http\Controllers\OrderVehicleIssueController::class, 'show'])
        ->name('order-vehicle-issues.show')
        ->middleware('permission:view-orders');
    Route::get('order-vehicle-issues/{orderVehicleIssue}/edit', [\App\Http\Controllers\OrderVehicleIssueController::class, 'edit'])
        ->name('order-vehicle-issues.edit')
        ->middleware('permission:edit-orders');
    Route::put('order-vehicle-issues/{orderVehicleIssue}', [\App\Http\Controllers\OrderVehicleIssueController::class, 'update'])
        ->name('order-vehicle-issues.update')
        ->middleware('permission:edit-orders');
    Route::delete('order-vehicle-issues/{orderVehicleIssue}', [\App\Http\Controllers\OrderVehicleIssueController::class, 'destroy'])
        ->name('order-vehicle-issues.destroy')
        ->middleware('permission:delete-orders');

    // Units Management
    Route::get('units', [\App\Http\Controllers\UnitController::class, 'index'])->name('units.index')->middleware('permission:view-units');
    Route::get('units/create', [\App\Http\Controllers\UnitController::class, 'create'])->name('units.create')->middleware('permission:create-units');
    Route::post('units', [\App\Http\Controllers\UnitController::class, 'store'])->name('units.store')->middleware('permission:create-units');
    Route::get('units/{unit}', [\App\Http\Controllers\UnitController::class, 'show'])->name('units.show')->middleware('permission:show-units');
    Route::get('units/{unit}/edit', [\App\Http\Controllers\UnitController::class, 'edit'])->name('units.edit')->middleware('permission:edit-units');
    Route::put('units/{unit}', [\App\Http\Controllers\UnitController::class, 'update'])->name('units.update')->middleware('permission:edit-units');
    Route::delete('units/{unit}', [\App\Http\Controllers\UnitController::class, 'destroy'])->name('units.destroy')->middleware('permission:delete-units');
});

require __DIR__.'/auth.php';

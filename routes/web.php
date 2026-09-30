<?php

use App\Http\Controllers\AbsensiController;
use App\Http\Controllers\DivisionController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\EmployeeTypeController;
use App\Http\Controllers\LeaderboardController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderVehicleIssueController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\PoolController;
use App\Http\Controllers\PositionController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\Settings;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\UploadFolderController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VehicleMaintenanceController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::get('leaderboard', [LeaderboardController::class, 'index'])
        ->name('leaderboard.index')
        ->middleware('permission:view-leaderboard');
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
    Route::get('employees', [EmployeeController::class, 'index'])->name('employees.index')->middleware('permission:view-employees');
    Route::get('employees/create', [EmployeeController::class, 'create'])->name('employees.create')->middleware('permission:create-employees');
    Route::post('employees', [EmployeeController::class, 'store'])->name('employees.store')->middleware('permission:create-employees');
    Route::get('employees/{employee}', [EmployeeController::class, 'show'])->name('employees.show')->middleware('permission:show-employees');
    Route::get('employees/{employee}/edit', [EmployeeController::class, 'edit'])->name('employees.edit')->middleware('permission:edit-employees');
    Route::put('employees/{employee}', [EmployeeController::class, 'update'])->name('employees.update')->middleware('permission:edit-employees');
    Route::post('employees/{employee}/create-account', [EmployeeController::class, 'createAccount'])->name('employees.create-account')->middleware('permission:edit-employees');
    Route::delete('employees/{employee}', [EmployeeController::class, 'destroy'])->name('employees.destroy')->middleware('permission:delete-employees');

    // Presensi / Absensi
    Route::get('employees/{employee}/presensi', [AbsensiController::class, 'showEmployeePresensi'])
        ->name('employees.presensi')
        ->middleware('permission:view-presensi-all|edit-absensi-status');
    Route::get('employees/{employee}/presensi/print', [AbsensiController::class, 'exportEmployeePresensiPdf'])
        ->name('employees.presensi.print')
        ->middleware('permission:view-presensi-all|edit-absensi-status');
    Route::get('presensi', [AbsensiController::class, 'showMyPresensi'])
        ->name('presensi.my')
        ->middleware('permission:view-presensi');
    Route::get('presensi/print', [AbsensiController::class, 'exportMyPresensiPdf'])
        ->name('presensi.print')
        ->middleware('permission:view-presensi');
    Route::get('absensi/today', [AbsensiController::class, 'showAllEmployeesToday'])
        ->name('absensi.today.all')
        ->middleware('permission:view-presensi-all');
    Route::post('presensi/masuk', [AbsensiController::class, 'storeMasuk'])
        ->name('presensi.masuk')
        ->middleware('permission:create-absensi-masuk');
    Route::post('presensi/pulang', [AbsensiController::class, 'storePulang'])
        ->name('presensi.pulang')
        ->middleware('permission:create-absensi-pulang');
    Route::put('absensis/{absensi}/status', [AbsensiController::class, 'adminUpdateStatus'])
        ->name('absensis.status.update')
        ->middleware('permission:edit-absensi-status');
    Route::post('employees/{employee}/presensi/status', [AbsensiController::class, 'adminUpsertStatusForDate'])
        ->name('absensis.status.upsert-by-date')
        ->middleware('permission:edit-absensi-status');

    // Upload folders by year/month
    Route::get('upload-folders', [UploadFolderController::class, 'index'])
        ->name('upload-folders.index')
        ->middleware('permission:view-upload-folders');
    Route::delete('upload-folders/{year}/{month}', [UploadFolderController::class, 'destroy'])
        ->name('upload-folders.destroy')
        ->middleware('permission:delete-upload-folders');

    // Orders Management
    Route::get('orders', [OrderController::class, 'index'])->name('orders.index')->middleware('permission:view-orders');
    Route::get('orders/create', [OrderController::class, 'create'])->name('orders.create')->middleware('permission:create-orders');
    Route::post('orders', [OrderController::class, 'store'])->name('orders.store')->middleware('permission:create-orders');
    Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show')->middleware('permission:show-orders');
    Route::get('orders/{order}/edit', [OrderController::class, 'edit'])->name('orders.edit')
        ->middleware('permission:edit-orders|edit-order-report|create-order-expenses|create-order-etoll|create-order-photos|create-order-vehicle-issues|edit-order-vehicle-issues');
    Route::put('orders/{order}', [OrderController::class, 'update'])->name('orders.update')
        ->middleware('permission:edit-orders|edit-order-report|create-order-expenses|create-order-etoll|create-order-photos');
    Route::delete('orders/{order}/report', [OrderController::class, 'deleteOrderReport'])->name('order-report.destroy')
        ->middleware('permission:delete-order-report');
    Route::delete('order-expenses/{expense}', [OrderController::class, 'deleteExpense'])->name('order-expenses.destroy')
        ->middleware('permission:delete-order-expenses');
    Route::delete('order-etoll/{trx}', [OrderController::class, 'deleteEtoll'])->name('order-etoll.destroy')
        ->middleware('permission:delete-order-etoll');
    Route::delete('orders/{order}', [OrderController::class, 'destroy'])->name('orders.destroy')->middleware('permission:delete-orders');
    Route::delete('order-photos/{photo}', [OrderController::class, 'deletePhoto'])->name('order-photos.destroy')
        ->middleware('permission:delete-order-photos');

    // Tasks (internal order)
    Route::get('tasks', [TaskController::class, 'index'])->name('tasks.index')->middleware('permission:view-tasks');
    Route::get('tasks/create', [TaskController::class, 'create'])->name('tasks.create')->middleware('permission:create-tasks');
    Route::post('tasks', [TaskController::class, 'store'])->name('tasks.store')->middleware('permission:create-tasks');
    Route::get('tasks/crew-search', [TaskController::class, 'searchCrew'])->name('tasks.crew-search')->middleware('permission:create-tasks|edit-tasks');
    Route::get('tasks/{task}', [TaskController::class, 'show'])->name('tasks.show')->middleware('permission:show-tasks');
    Route::get('tasks/{task}/edit', [TaskController::class, 'edit'])->name('tasks.edit')->middleware('permission:edit-tasks');
    Route::put('tasks/{task}', [TaskController::class, 'update'])->name('tasks.update')->middleware('permission:edit-tasks');
    Route::delete('tasks/{task}', [TaskController::class, 'destroy'])->name('tasks.destroy')->middleware('permission:delete-tasks');

    Route::post('tasks/{task}/attachments', [TaskController::class, 'storeAttachment'])->name('tasks.attachments.store')
        ->middleware('permission:create-task-attachments');
    Route::delete('task-attachments/{taskAttachment}', [TaskController::class, 'destroyAttachment'])->name('tasks.attachments.destroy')
        ->middleware('permission:delete-task-attachments');

    Route::post('tasks/{task}/comments', [TaskController::class, 'storeComment'])->name('tasks.comments.store')
        ->middleware('permission:create-task-comments');
    Route::delete('task-comments/{taskComment}', [TaskController::class, 'destroyComment'])->name('tasks.comments.destroy')
        ->middleware('permission:delete-task-comments');

    // Vehicle Maintenances
    Route::get('vehicle-maintenances', [VehicleMaintenanceController::class, 'index'])->name('vehicle-maintenances.index')->middleware('permission:view-vehicle-maintenances');
    Route::get('vehicle-maintenances/create', [VehicleMaintenanceController::class, 'create'])->name('vehicle-maintenances.create')->middleware('permission:create-vehicle-maintenances');
    Route::get('vehicle-maintenances/pic-search', [VehicleMaintenanceController::class, 'searchPic'])->name('vehicle-maintenances.pic-search')->middleware('permission:create-vehicle-maintenances|edit-vehicle-maintenances');
    Route::post('vehicle-maintenances', [VehicleMaintenanceController::class, 'store'])->name('vehicle-maintenances.store')->middleware('permission:create-vehicle-maintenances');
    Route::get('vehicle-maintenances/{vehicleMaintenance}', [VehicleMaintenanceController::class, 'show'])->name('vehicle-maintenances.show')->middleware('permission:show-vehicle-maintenances');
    Route::get('vehicle-maintenances/{vehicleMaintenance}/edit', [VehicleMaintenanceController::class, 'edit'])->name('vehicle-maintenances.edit')->middleware('permission:edit-vehicle-maintenances');
    Route::put('vehicle-maintenances/{vehicleMaintenance}', [VehicleMaintenanceController::class, 'update'])->name('vehicle-maintenances.update')->middleware('permission:edit-vehicle-maintenances');
    Route::delete('vehicle-maintenances/{vehicleMaintenance}', [VehicleMaintenanceController::class, 'destroy'])->name('vehicle-maintenances.destroy')->middleware('permission:delete-vehicle-maintenances');

    // Order Vehicle Issues
    Route::get('order-vehicle-issues', [OrderVehicleIssueController::class, 'index'])
        ->name('order-vehicle-issues.index')
        ->middleware('permission:view-order-vehicle-issues');
    Route::get('orders/{order}/vehicle-issues/create', [OrderVehicleIssueController::class, 'create'])
        ->name('order-vehicle-issues.create')
        ->middleware('permission:create-order-vehicle-issues');
    Route::post('orders/{order}/vehicle-issues', [OrderVehicleIssueController::class, 'store'])
        ->name('order-vehicle-issues.store')
        ->middleware('permission:create-order-vehicle-issues');
    Route::get('order-vehicle-issues/{orderVehicleIssue}', [OrderVehicleIssueController::class, 'show'])
        ->name('order-vehicle-issues.show')
        ->middleware('permission:show-order-vehicle-issues');
    Route::get('order-vehicle-issues/{orderVehicleIssue}/edit', [OrderVehicleIssueController::class, 'edit'])
        ->name('order-vehicle-issues.edit')
        ->middleware('permission:edit-order-vehicle-issues');
    Route::put('order-vehicle-issues/{orderVehicleIssue}', [OrderVehicleIssueController::class, 'update'])
        ->name('order-vehicle-issues.update')
        ->middleware('permission:edit-order-vehicle-issues');
    Route::delete('order-vehicle-issues/{orderVehicleIssue}', [OrderVehicleIssueController::class, 'destroy'])
        ->name('order-vehicle-issues.destroy')
        ->middleware('permission:delete-order-vehicle-issues');

    // Units Management
    Route::get('units', [UnitController::class, 'index'])->name('units.index')->middleware('permission:view-units');
    Route::get('units/create', [UnitController::class, 'create'])->name('units.create')->middleware('permission:create-units');
    Route::post('units', [UnitController::class, 'store'])->name('units.store')->middleware('permission:create-units');
    Route::get('units/{unit}', [UnitController::class, 'show'])->name('units.show')->middleware('permission:show-units');
    Route::get('units/{unit}/edit', [UnitController::class, 'edit'])->name('units.edit')->middleware('permission:edit-units');
    Route::put('units/{unit}', [UnitController::class, 'update'])->name('units.update')->middleware('permission:edit-units');
    Route::delete('units/{unit}', [UnitController::class, 'destroy'])->name('units.destroy')->middleware('permission:delete-units');

    // Pools Management
    Route::get('pools', [PoolController::class, 'index'])->name('pools.index')->middleware('permission:view-pools');
    Route::get('pools/create', [PoolController::class, 'create'])->name('pools.create')->middleware('permission:create-pools');
    Route::post('pools', [PoolController::class, 'store'])->name('pools.store')->middleware('permission:create-pools');
    Route::get('pools/{pool}', [PoolController::class, 'show'])->name('pools.show')->middleware('permission:show-pools');
    Route::get('pools/{pool}/edit', [PoolController::class, 'edit'])->name('pools.edit')->middleware('permission:edit-pools');
    Route::put('pools/{pool}', [PoolController::class, 'update'])->name('pools.update')->middleware('permission:edit-pools');
    Route::delete('pools/{pool}', [PoolController::class, 'destroy'])->name('pools.destroy')->middleware('permission:delete-pools');
});

require __DIR__.'/auth.php';

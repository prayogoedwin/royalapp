<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AbsensiApiController;
use App\Http\Controllers\Api\OrderApiController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [UserController::class, 'me']);
    Route::put('/profile', [UserController::class, 'updateProfile']);
    Route::put('/profile/password', [UserController::class, 'updatePassword']);
    Route::get('/stats/orders/total', [UserController::class, 'totalOrders']);
    Route::get('/stats/tasks/total', [UserController::class, 'totalTasks']);
    Route::post('/absensi/masuk', [AbsensiApiController::class, 'storeMasuk']);
    Route::post('/absensi/pulang', [AbsensiApiController::class, 'storePulang']);
    Route::get('/absensi/latest', [AbsensiApiController::class, 'latest']);
    Route::get('/absensi/rekap-bulanan', [AbsensiApiController::class, 'monthlyRecap']);

    Route::get('/order-statuses', [OrderApiController::class, 'statuses']);
    Route::get('/order-expense-categories', [OrderApiController::class, 'expenseCategories']);
    Route::get('/order-issue-categories', [OrderApiController::class, 'issueCategories']);
    Route::get('/orders', [OrderApiController::class, 'myOrders']);
    Route::get('/orders/{order}', [OrderApiController::class, 'show']);

    Route::get('/orders/{order}/photos', [OrderApiController::class, 'photos']);
    Route::post('/orders/{order}/photos', [OrderApiController::class, 'storePhoto']);
    Route::post('/orders/{order}/photos/{photo}', [OrderApiController::class, 'updatePhoto']);
    Route::delete('/orders/{order}/photos/{photo}', [OrderApiController::class, 'destroyPhoto']);

    Route::get('/orders/{order}/expenses', [OrderApiController::class, 'expenses']);
    Route::post('/orders/{order}/expenses', [OrderApiController::class, 'storeExpense']);
    Route::put('/orders/{order}/expenses/{expense}', [OrderApiController::class, 'updateExpense']);
    Route::delete('/orders/{order}/expenses/{expense}', [OrderApiController::class, 'destroyExpense']);

    Route::put('/orders/{order}/report', [OrderApiController::class, 'updateReport']);

    Route::get('/orders/{order}/etolls', [OrderApiController::class, 'etolls']);
    Route::post('/orders/{order}/etolls', [OrderApiController::class, 'storeEtoll']);
    Route::put('/orders/{order}/etolls/{trx}', [OrderApiController::class, 'updateEtoll']);
    Route::delete('/orders/{order}/etolls/{trx}', [OrderApiController::class, 'destroyEtoll']);

    Route::get('/orders/{order}/vehicle-issues', [OrderApiController::class, 'vehicleIssues']);
    Route::post('/orders/{order}/vehicle-issues', [OrderApiController::class, 'storeVehicleIssue']);
    Route::put('/orders/{order}/vehicle-issues/{issue}', [OrderApiController::class, 'updateVehicleIssue']);
    Route::delete('/orders/{order}/vehicle-issues/{issue}', [OrderApiController::class, 'destroyVehicleIssue']);

    Route::get('/users', [UserController::class, 'index'])->middleware('role:admin');
});

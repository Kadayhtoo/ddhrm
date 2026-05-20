<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\DepartmentController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\StaffController;
use Illuminate\Support\Facades\Route;

Route::post('/auth/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);

    Route::get('/dashboard/summary', [DashboardController::class, 'summary'])
        ->middleware('permission:dashboard.view');

    Route::get('/roles/assignable', [RoleController::class, 'assignable']);
    Route::get('roles', [RoleController::class, 'index'])->middleware('permission:roles.view');
    Route::get('roles/{role}', [RoleController::class, 'show'])->middleware('permission:roles.view');
    Route::put('roles/{role}', [RoleController::class, 'update'])->middleware('permission:roles.manage');

    Route::apiResource('staff', StaffController::class)->parameters(['staff' => 'user']);
    Route::apiResource('department', DepartmentController::class);

    Route::get('admin/profile', [AuthController::class, 'profile']); 
    Route::put('/profile', [AuthController::class, 'updateProfile']);
});

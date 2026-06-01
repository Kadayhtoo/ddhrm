<?php

use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\AttendanceReportController;
use App\Http\Controllers\Api\AttendanceSettingsController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\DepartmentController;
use App\Http\Controllers\Api\LeaveBalanceController;
use App\Http\Controllers\Api\LeaveRequestController;
use App\Http\Controllers\Api\LeaveRuleController;
use App\Http\Controllers\Api\PayrollController;
use App\Http\Controllers\APi\PositionController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\StaffController;
use Illuminate\Support\Facades\Route;

Route::post('/auth/login', [AuthController::class, 'login'])->name('login');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::get('admin/profile', [AuthController::class, 'profile']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);

    Route::get('/dashboard/summary', [DashboardController::class, 'summary'])
        ->middleware('permission:dashboard.view');

    Route::get('/roles/assignable', [RoleController::class, 'assignable']);
    Route::get('roles', [RoleController::class, 'index'])->middleware('permission:roles.view');
    Route::get('roles/{role}', [RoleController::class, 'show'])->middleware('permission:roles.view');
    Route::put('roles/{role}', [RoleController::class, 'update'])->middleware('permission:roles.manage');

    Route::apiResource('staff', StaffController::class)->parameters(['staff' => 'user'])->middleware('permission:staff.view');
    Route::get('staff/{user}', [StaffController::class, 'showDetail']);
    Route::get('staff/{user}/leave-balances', [StaffController::class, 'getLeaveBalances']);
    Route::get('staff/{user}/leave-requests', [StaffController::class, 'getLeaveRequests']);
    Route::get('/staff-dropdown', [StaffController::class, 'dropdownList']);

    Route::get('staff/{user}/attendances', [StaffController::class, 'getAttendances']);

    Route::prefix('attendance')->middleware('permission:attendance.view')->group(function () {
        Route::get('/', [AttendanceController::class, 'index']);
        Route::get('/today', [AttendanceController::class, 'today']);
        Route::post('/check-in', [AttendanceController::class, 'checkIn']);
        Route::post('/check-out', [AttendanceController::class, 'checkOut']);
        Route::get('/reports/widgets', [AttendanceReportController::class, 'widgets']);
        Route::get('/reports/daily', [AttendanceReportController::class, 'daily']);
        Route::get('/reports/monthly', [AttendanceReportController::class, 'monthly']);
        Route::get('/reports/employee/{user}', [AttendanceReportController::class, 'employee']);
        Route::get('/settings', [AttendanceSettingsController::class, 'show'])->middleware('permission:attendance.manage');
        Route::put('/settings', [AttendanceSettingsController::class, 'update'])->middleware('permission:attendance.manage');
        Route::get('/{attendance}', [AttendanceController::class, 'show']);
    });

    Route::apiResource('department', DepartmentController::class);
    Route::apiResource('position', PositionController::class);
    Route::get('department/{department}/positions', [DepartmentController::class, 'getPositions']);

    Route::apiResource('leave-rules', LeaveRuleController::class);
    Route::apiResource('leave-requests', LeaveRequestController::class);
    Route::patch('/leave-requests/{id}/status', [LeaveRequestController::class, 'changeStatus']);
    Route::post('leave-requests/{id}/cancel', [LeaveRequestController::class, 'cancel']);

    Route::get('/leave-balances', [LeaveBalanceController::class, 'index']);

    Route::prefix('payroll')->middleware('permission:payroll.view')->group(function () {
        Route::get('/', [PayrollController::class, 'index']);
        Route::get('/stats', [PayrollController::class, 'stats']);
        
        Route::get('/settings', [PayrollController::class, 'settings'])->middleware('permission:payroll.manage');
        Route::put('/settings', [PayrollController::class, 'settings'])->middleware('permission:payroll.manage');
        Route::post('/calculate', [PayrollController::class, 'calculate'])->middleware('permission:payroll.manage');
        Route::post('/{id}/mark-paid', [PayrollController::class, 'markPaid'])->middleware('permission:payroll.manage');
        Route::get('/{id}', [PayrollController::class, 'show']);
    });

});

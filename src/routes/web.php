<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AdminLoginController;
use App\Http\Controllers\AdminAttendanceController;

Route::get('/register', [AuthController::class, 'show_register']);
Route::post('/register', [AuthController::class, 'store_user']);
Route::post('/login', [AuthController::class, 'login']);

// 管理者ログイン
Route::prefix('admin')->group(function () {
    Route::get('/login', [AdminLoginController::class, 'show_admin_login'])->name('admin.login');
    Route::post('/login', [AdminLoginController::class, 'login']);
    Route::post('/logout', [AdminLoginController::class, 'logout'])->name('admin.logout');

    Route::middleware('auth:admin')->group(function () {
        Route::get('/attendance/list', [AdminAttendanceController::class, 'admin_show_list'])->name('admin.list');
        Route::get('/staff/list', [AdminAttendanceController::class, 'show_staff_list']);
    });
});

Route::middleware(['auth'])->group(function () {
    Route::get('/attendance', [AttendanceController::class, 'show_main'])->name('home');
    Route::get('/attendance/list', [AttendanceController::class, 'show_list'])->name('attendance_list');
    Route::get('/stamp_correction_request/list', [AttendanceController::class, 'show_stamp_list'])->name('stamp_list');
    Route::get('/attendance/detail/{id}', [AttendanceController::class, 'show_detail'])->name('detail');
});



<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AdminLoginController;
use App\Http\Controllers\AdminAttendanceController;

Route::get('/register', [AuthController::class, 'show_register']);
Route::post('/register', [AuthController::class, 'store_user']);
Route::post('/login', [AuthController::class, 'login']);

// メール認証
Route::get('/email/verify', [AuthController::class, 'verifyNotice'])->middleware('auth')->name('verification.notice');
Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])->middleware(['auth', 'signed'])->name('verification.verify');
Route::post('/email/resend', [AuthController::class, 'resendVerification'])->middleware(['auth', 'throttle:6,1'])->name('verification.send');

// 管理者ログイン
Route::prefix('admin')->group(function () {
    Route::get('/login', [AdminLoginController::class, 'show_admin_login'])->name('admin.login')->middleware('guest:admin');
    Route::post('/login', [AdminLoginController::class, 'login'])->middleware('guest:admin');
    Route::post('/logout', [AdminLoginController::class, 'logout'])->name('admin.logout');

    Route::middleware('auth:admin')->group(function () {
        Route::get('/attendance/list', [AdminAttendanceController::class, 'adminShowList'])->name('admin.list');
        Route::get('/attendance/detail/{id}', [AdminAttendanceController::class, 'adminShowDetail'])->name('admin.detail');
        Route::post('/attendance/detail/correction', [AdminAttendanceController::class, 'adminDetailCorrection'])->name('admin.detail.correction');
        Route::get('/staff/list', [AdminAttendanceController::class, 'showStaffList']);
        Route::get('/attendance/staff/{id}', [AdminAttendanceController::class, 'showAttendanceStaffList'])->name('admin.attendance_staff_list');
        Route::get('/stamp_correction_request/approve/{attendance_correct_request_id}', [AdminAttendanceController::class, 'approveCorrectRequest'])->name('admin.approve_correct_request');
        Route::post('/stamp_correction_request/approve/exec', [AdminAttendanceController::class, 'approveCorrectRequestExec'])->name('admin.approve_correct_request_exec');
    });
});

// 一般ユーザーログイン
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/attendance', [AttendanceController::class, 'showMain'])->name('home');
    Route::post('/attendance/clock_in', [AttendanceController::class, 'clockIn'])->name('attendance.clockIn');
    Route::post('/attendance/clock_out', [AttendanceController::class, 'clockOut'])->name('attendance.clockOut');
    Route::post('/attendance/break_start', [AttendanceController::class, 'breakStart'])->name('attendance.breakStart');
    Route::post('/attendance/break_end', [AttendanceController::class, 'breakEnd'])->name('attendance.breakEnd');
    Route::get('/attendance/list', [AttendanceController::class, 'show_list'])->name('attendance_list');
    Route::get('/attendance/detail/{id}', [AttendanceController::class, 'showDetail'])->name('attendance.detail');
    Route::post('/attendance/detail/correction', [AttendanceController::class, 'submitDetailCorrection'])->name('submit.detail.correction');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

// 管理者、一般ユーザー両方からアクセス
Route::middleware('auth.web_or_admin')->group(function () {
    Route::get('/stamp_correction_request/list', [AttendanceController::class, 'showStampList'])->name('stamp_list');
});
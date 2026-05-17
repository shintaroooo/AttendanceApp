<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\BreakController;
use App\Http\Controllers\AttendanceCorrectionController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AdminAttendanceController;
use App\Http\Controllers\AdminCorrectionController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return redirect('/login');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::get('/attendance/correction', [AttendanceCorrectionController::class, 'index'])->name('correction.list');

    Route::post('/attendance/start', [AttendanceController::class, 'start'])->name('attendance.start');
    Route::post('/attendance/end', [AttendanceController::class, 'end'])->name('attendance.end');

    Route::post('/break/start', [BreakController::class, 'start'])->name('break.start');
    Route::post('/break/end', [BreakController::class, 'end'])->name('break.end');

    Route::get('/attendance/list', [AttendanceController::class, 'list'])->name('attendance.list');
    Route::get('/attendance/detail/{id}', [AttendanceController::class, 'detail'])->name('attendance.detail');
    Route::get('/attendance/detail/date/{date}', [AttendanceController::class, 'detailByDate'])
    ->name('attendance.detail.byDate');

    Route::post('/attendance/correction', [AttendanceCorrectionController::class, 'store'])->name('attendance.correction.store');
    Route::get('/stamp_correction_request/list', [AttendanceCorrectionController::class, 'index'])->name('correction.list');
});

Route::prefix('admin')->group(function () {
    // 管理者ログイン
    Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('admin.login');
    // ログイン処理
    Route::post('/login', [AdminAuthController::class, 'login'])->name('admin.login.post');
    // ログアウト
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');
    // 管理者のみ
    Route::middleware(['auth', 'admin'])->group(function () {
        Route::get('/attendance/list', [AdminAttendanceController::class, 'index'])
            ->name('admin.attendance.list');
        Route::get('/attendance/detail/{id}', [AdminAttendanceController::class, 'show'])
            ->name('admin.attendance.detail');
        Route::get('/attendance/detail', [AdminAttendanceController::class, 'detailByDate'])
            ->name('admin.attendance.detail.byDate');
        Route::post('/attendance/save', [AdminAttendanceController::class, 'updateOrCreate'])
            ->name('admin.attendance.save');

        Route::put('/attendance/{id}', [AdminAttendanceController::class, 'update'])
            ->name('admin.attendance.update');
        Route::get('/staff/list', [AdminAttendanceController::class, 'staffList'])
            ->name('admin.staff.list');
        Route::get('/staff/{id}/attendance', [AdminAttendanceController::class, 'staffAttendance'])
            ->name('admin.staff.attendance');
        Route::get('/staff/{id}/attendance/csv', [AdminAttendanceController::class, 'exportCsv'])
            ->name('admin.staff.attendance.csv');
        
            Route::get('/correction/', [AdminCorrectionController::class, 'index'])
            ->name('admin.correction.list');
        Route::get('/correction/{id}', [AdminCorrectionController::class, 'show'])
            ->name('admin.correction.detail');
        Route::post('/correction/{id}/approve', [AdminCorrectionController::class, 'approve'])
            ->name('admin.correction.approve');
    });
});
<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\HolidayController;
use App\Http\Controllers\AdjustmentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\QrAttendanceController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('auth.login');
})->name('login');

// Public QR Attendance Routes (No Authentication Required)
Route::prefix('qr-attendance')->name('qr-attendance.')->group(function () {
    Route::get('/scanner', [QrAttendanceController::class, 'scanner'])->name('scanner');
    Route::post('/scan', [QrAttendanceController::class, 'scan'])->name('scan');
    Route::get('/status/{employeeId}', [QrAttendanceController::class, 'status'])->name('status');
});


Route::middleware(['auth'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Employees (Admin Only)
    Route::middleware('admin')->group(function () {
        Route::resource('employees', EmployeeController::class);
    });

    // Attendance
    Route::prefix('attendance')->name('attendance.')->group(function () {
        Route::get('/', [AttendanceController::class, 'index'])->name('index');
        Route::get('/user/{user}', [AttendanceController::class, 'userAttendance'])->name('user');

        Route::middleware('admin')->group(function () {
            Route::get('/manual', [AttendanceController::class, 'markManual'])->name('manual');
            Route::post('/manual', [AttendanceController::class, 'storeManual'])->name('manual.store');
        });
    });

    // Departments
    Route::middleware('admin')->group(function () {
        Route::resource('departments', DepartmentController::class);
    });

    // Holidays
    Route::middleware('admin')->group(function () {
        Route::resource('holidays', HolidayController::class);
    });

    // Adjustments
    Route::prefix('adjustments')->name('adjustments.')->group(function () {
        Route::get('/', [AdjustmentController::class, 'index'])->name('index');
        Route::get('/create', [AdjustmentController::class, 'create'])->name('create');
        Route::post('/', [AdjustmentController::class, 'store'])->name('store');

        Route::middleware('admin')->group(function () {
            Route::post('/{adjustment}/approve', [AdjustmentController::class, 'approve'])->name('approve');
            Route::post('/{adjustment}/reject', [AdjustmentController::class, 'reject'])->name('reject');
        });
    });

    // Activity Logs
    Route::middleware('admin')->group(function () {
        Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
    });
});


require __DIR__ . '/auth.php';

<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\TeacherController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\SubjectController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SchoolClassController;
use App\Http\Controllers\TeacherSessionController;
use App\Http\Controllers\StudentScanController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::middleware('role:admin,guru')->group(function () {
        Route::get('/attendance', [AttendanceController::class, 'manage'])->name('attendance.manage');
        Route::post('/attendance', [AttendanceController::class, 'store'])->name('attendance.store');
    });

    Route::middleware('role:admin')->group(function () {
        Route::resource('teachers', TeacherController::class)->except(['show']);
        Route::resource('students', StudentController::class)->except(['show']);
        Route::resource('subjects', SubjectController::class)->except(['show']);
        Route::post('/classes', [SchoolClassController::class, 'store'])->name('classes.store');
    });

    Route::middleware('role:admin,guru')->group(function () {
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/export', [ReportController::class, 'export'])->name('reports.export');
    });

    Route::middleware('role:admin,guru')->group(function () {
        Route::get('/attendance/session', [TeacherSessionController::class, 'index'])->name('attendance.session');
        Route::post('/attendance/session/start', [TeacherSessionController::class, 'start'])->name('attendance.session.start');
        Route::post('/attendance/session/pause', [TeacherSessionController::class, 'pause'])->name('attendance.session.pause');
        Route::post('/attendance/session/resume', [TeacherSessionController::class, 'resume'])->name('attendance.session.resume');
        Route::post('/attendance/session/close', [TeacherSessionController::class, 'close'])->name('attendance.session.close');
        Route::post('/attendance/session/manual', [TeacherSessionController::class, 'markManual'])->name('attendance.session.manual');
        Route::post('/attendance/session/refresh', [TeacherSessionController::class, 'refreshCode'])->name('attendance.session.refresh');
        Route::get('/attendance/session/scans', [TeacherSessionController::class, 'scans'])->name('attendance.session.scans');
    });

    Route::middleware('role:siswa')->group(function () {
        Route::view('/attendance/scan', 'attendance.scan')->name('attendance.scan');
        Route::post('/attendance/scan/confirm', [StudentScanController::class, 'confirm'])->name('attendance.scan.confirm');
    });

    Route::get('/attendance/me', [AttendanceController::class, 'me'])->name('attendance.me');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

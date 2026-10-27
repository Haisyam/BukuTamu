<?php

declare(strict_types=1);

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DivisionController;
use App\Http\Controllers\GuestBookController;
use App\Http\Controllers\GuestLogController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes (Kiosk & Guest Logging) with Rate Limiting
|--------------------------------------------------------------------------
*/
Route::get('/', [GuestBookController::class, 'index'])->name('guestbook.index');
Route::post('/guest-book', [GuestBookController::class, 'store'])
    ->middleware('throttle:20,1')
    ->name('guestbook.store');
Route::get('/api/guest-lookup', [GuestBookController::class, 'lookup'])
    ->middleware('throttle:60,1')
    ->name('guestbook.lookup');

/*
|--------------------------------------------------------------------------
| Authentication Routes with Brute-Force Rate Limiting
|--------------------------------------------------------------------------
*/
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])
    ->middleware('throttle:5,1')
    ->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| Protected Admin Routes (Require Auth)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

    // Guest Logs Exports (MUST BE DEFINED BEFORE PARAMETERIZED ROUTES)
    Route::get('/guest-logs/export/pdf', [GuestLogController::class, 'exportPdf'])->name('admin.guest-logs.export.pdf');
    Route::get('/guest-logs/export/excel', [GuestLogController::class, 'exportExcel'])->name('admin.guest-logs.export.excel');

    // Guest Logs Management
    Route::get('/guest-logs', [GuestLogController::class, 'index'])->name('admin.guest-logs.index');
    Route::get('/guest-logs/{guestLog}', [GuestLogController::class, 'show'])->name('admin.guest-logs.show');
    Route::put('/guest-logs/{guestLog}', [GuestLogController::class, 'update'])->name('admin.guest-logs.update');
    Route::delete('/guest-logs/{guestLog}', [GuestLogController::class, 'destroy'])->name('admin.guest-logs.destroy');

    // Divisions Management
    Route::resource('/divisions', DivisionController::class)->names('admin.divisions')->except(['create', 'show', 'edit']);
    Route::patch('/divisions/{division}/toggle', [DivisionController::class, 'toggle'])->name('admin.divisions.toggle');
});

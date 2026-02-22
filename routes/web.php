<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\User;
use Illuminate\Support\Facades\Route;

// Root redirect
Route::get('/', function () {
    return redirect()->route('login');
});

// Auth routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'loginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// User panel
Route::prefix('panel')->name('user.')->middleware('auth')->group(function () {
    Route::get('/dashboard', [User\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/password', [User\PasswordController::class, 'edit'])->name('password.edit');
    Route::put('/password', [User\PasswordController::class, 'update'])->name('password.update');
});

// Admin panel
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/dashboard', [Admin\DashboardController::class, 'index'])->name('dashboard');

    // Users
    Route::resource('users', Admin\UserController::class)->except(['show']);

    // Licenses
    Route::resource('licenses', Admin\LicenseController::class)->except(['show']);
    Route::post('licenses/{license}/extend', [Admin\LicenseController::class, 'extendExpiry'])->name('licenses.extend');

    // Logs
    Route::get('logs', [Admin\LogController::class, 'index'])->name('logs.index');

    // Device Blacklist
    Route::get('blacklist', [Admin\DeviceBlacklistController::class, 'index'])->name('blacklist.index');
    Route::post('blacklist', [Admin\DeviceBlacklistController::class, 'store'])->name('blacklist.store');
    Route::delete('blacklist/{blacklist}', [Admin\DeviceBlacklistController::class, 'destroy'])->name('blacklist.destroy');
});


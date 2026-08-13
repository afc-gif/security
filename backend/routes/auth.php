<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Operations\AdminController;
use App\Http\Controllers\Auth\UserAdminController as ApiUserAdminController;

// Authentication routes (Login, Register, Logout)
Route::middleware('web')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');
});

// Admin API Users Management
Route::middleware(['auth', 'admin'])->prefix('api')->group(function () {
    Route::get('/users', [ApiUserAdminController::class, 'index']);
    Route::put('/users/{user}', [ApiUserAdminController::class, 'update']);
    Route::delete('/users/{user}', [ApiUserAdminController::class, 'destroy']);
});

// Admin Web Users Management (view pending, approve, assign roles)
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/users', [AdminController::class, 'users'])->name('admin.users.index');
    Route::get('/users/pending', [AdminController::class, 'pendingUsers'])->name('admin.users.pending');
    Route::patch('/users/{user}/approve/{role}', [AdminController::class, 'approveUser'])->name('admin.users.approve');
    Route::patch('/users/{user}/reject', [AdminController::class, 'rejectUser'])->name('admin.users.reject');
    Route::delete('/users/{user}', [AdminController::class, 'deleteUser'])->name('admin.users.delete');
});

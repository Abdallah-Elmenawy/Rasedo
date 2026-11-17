<?php

use App\Http\Controllers\Admin\PublicSettings\ActivityLogController;
use App\Http\Controllers\Admin\PublicSettings\ProfileController;
use App\Http\Controllers\Admin\PublicSettings\RoleController;
use App\Http\Controllers\Admin\PublicSettings\SettingController;
use App\Http\Controllers\Admin\PublicSettings\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('permission:users.view')->group(function () {
    Route::get('users', [UserController::class, 'index'])->name('users.index');
    Route::get('users/{user}', [UserController::class, 'show'])->name('users.show');
    Route::get('roles', [RoleController::class, 'index'])->name('roles.index');
    Route::get('activity-logs', [ActivityLogController::class, 'index'])->name('logs.index');
    Route::middleware('permission:users.create')->group(function () {
        Route::post('users', [UserController::class, 'store'])->name('users.store');
        Route::post('roles', [RoleController::class, 'store'])->name('roles.store');
    });
    Route::middleware('permission:users.update')->group(function () {
        Route::get('users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::match(['put', 'patch'], 'users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::post('users/{user}/avatar', [UserController::class, 'updateAvatar'])->name('users.avatar');
        Route::get('roles/{role}/edit', [RoleController::class, 'edit'])->name('roles.edit');
        Route::match(['put', 'patch'], 'roles/{role}', [RoleController::class, 'update'])->name('roles.update');
    });
    Route::delete('users/{user}', [UserController::class, 'destroy'])->middleware('permission:users.delete')->name('users.destroy');
    Route::delete('roles/{role}', [RoleController::class, 'destroy'])->middleware('permission:users.delete')->name('roles.destroy');
});

Route::middleware('permission:settings.update')->group(function () {
    Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
    Route::put('settings', [SettingController::class, 'update'])->name('settings.update');
});

Route::get('profile', [ProfileController::class, 'index'])->name('profile.edit');
Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');
Route::put('profile/password', [ProfileController::class, 'password'])->name('profile.password');

<?php

use App\Http\Controllers\Admin\Accounting\AccountController;
use App\Http\Controllers\Admin\Accounting\JournalController;
use Illuminate\Support\Facades\Route;

Route::middleware('permission:accounting.view')->group(function () {
    Route::get('accounts/receivables', [AccountController::class, 'receivables'])->name('accounts.receivables');
    Route::get('accounts/payables', [AccountController::class, 'payables'])->name('accounts.payables');
    Route::get('accounts', [AccountController::class, 'index'])->name('accounts.index');
    Route::get('journals', [JournalController::class, 'index'])->name('journals.index');
    Route::middleware('permission:accounting.create')->group(function () {
        Route::post('accounts', [AccountController::class, 'store'])->name('accounts.store');
        Route::get('journals/create', [JournalController::class, 'create'])->name('journals.create');
        Route::post('journals', [JournalController::class, 'store'])->name('journals.store');
    });
    Route::get('journals/{journal}', [JournalController::class, 'show'])->name('journals.show');
    Route::put('accounts/{account}', [AccountController::class, 'update'])->middleware('permission:accounting.update')->name('accounts.update');
    Route::delete('accounts/{account}', [AccountController::class, 'destroy'])->middleware('permission:accounting.delete')->name('accounts.destroy');
});

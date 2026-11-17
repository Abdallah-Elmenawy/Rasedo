<?php

use App\Http\Controllers\Admin\Treasury\TreasuryController;
use Illuminate\Support\Facades\Route;

Route::middleware('permission:treasury.view')->group(function () {
    Route::get('treasury', [TreasuryController::class, 'index'])->name('treasury.index');
    Route::middleware('permission:treasury.create')->group(function () {
        Route::post('treasury', [TreasuryController::class, 'storeTreasury'])->name('treasury.store');
        Route::post('treasury/receipt', [TreasuryController::class, 'receipt'])->name('treasury.receipt');
        Route::post('treasury/payment', [TreasuryController::class, 'payment'])->name('treasury.payment');
        Route::post('treasury/transfer', [TreasuryController::class, 'transfer'])->name('treasury.transfer');
    });
});

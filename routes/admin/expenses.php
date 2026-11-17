<?php

use App\Http\Controllers\Admin\Expenses\ExpenseController;
use Illuminate\Support\Facades\Route;

Route::middleware('permission:expenses.view')->group(function () {
    Route::get('expenses', [ExpenseController::class, 'index'])->name('expenses.index');
    Route::get('expenses/{entry}', [ExpenseController::class, 'show'])->name('expenses.show');
    Route::middleware('permission:expenses.create')->group(function () {
        Route::post('expenses', [ExpenseController::class, 'store'])->name('expenses.store');
        Route::post('expenses/categories', [ExpenseController::class, 'storeCategory'])->name('expenses.categories.store');
    });
    Route::middleware('permission:expenses.update')->group(function () {
        Route::get('expenses/{entry}/edit', [ExpenseController::class, 'edit'])->name('expenses.edit');
        Route::match(['put', 'patch'], 'expenses/{entry}', [ExpenseController::class, 'update'])->name('expenses.update');
    });
    Route::delete('expenses/{entry}', [ExpenseController::class, 'destroy'])->middleware('permission:expenses.delete')->name('expenses.destroy');
});

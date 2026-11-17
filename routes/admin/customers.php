<?php

use App\Http\Controllers\Admin\Customers\CustomerController;
use Illuminate\Support\Facades\Route;

Route::middleware('permission:customers.view')->group(function () {
    Route::get('customers/accounts', [CustomerController::class, 'accounts'])->name('customers.accounts');
    Route::get('customers', [CustomerController::class, 'index'])->name('customers.index');
    Route::middleware('permission:customers.create')->group(function () {
        Route::get('customers/create', [CustomerController::class, 'create'])->name('customers.create');
        Route::post('customers', [CustomerController::class, 'store'])->name('customers.store');
    });
    Route::get('customers/{customer}', [CustomerController::class, 'show'])->name('customers.show');
    Route::middleware('permission:customers.update')->group(function () {
        Route::get('customers/{customer}/edit', [CustomerController::class, 'edit'])->name('customers.edit');
        Route::match(['put', 'patch'], 'customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');
        Route::post('customers/{customer}/collect', [CustomerController::class, 'collect'])->name('customers.collect');
    });
    Route::delete('customers/{customer}', [CustomerController::class, 'destroy'])->middleware('permission:customers.delete')->name('customers.destroy');
});

<?php

use App\Http\Controllers\Admin\Suppliers\SupplierController;
use Illuminate\Support\Facades\Route;

Route::middleware('permission:suppliers.view')->group(function () {
    Route::get('suppliers/accounts', [SupplierController::class, 'accounts'])->name('suppliers.accounts');
    Route::get('suppliers', [SupplierController::class, 'index'])->name('suppliers.index');
    Route::middleware('permission:suppliers.create')->group(function () {
        Route::get('suppliers/create', [SupplierController::class, 'create'])->name('suppliers.create');
        Route::post('suppliers', [SupplierController::class, 'store'])->name('suppliers.store');
    });
    Route::get('suppliers/{supplier}', [SupplierController::class, 'show'])->name('suppliers.show');
    Route::middleware('permission:suppliers.update')->group(function () {
        Route::get('suppliers/{supplier}/edit', [SupplierController::class, 'edit'])->name('suppliers.edit');
        Route::match(['put', 'patch'], 'suppliers/{supplier}', [SupplierController::class, 'update'])->name('suppliers.update');
        Route::post('suppliers/{supplier}/pay', [SupplierController::class, 'pay'])->name('suppliers.pay');
    });
    Route::delete('suppliers/{supplier}', [SupplierController::class, 'destroy'])->middleware('permission:suppliers.delete')->name('suppliers.destroy');
});

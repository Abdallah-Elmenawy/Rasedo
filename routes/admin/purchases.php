<?php

use App\Http\Controllers\Admin\Purchases\PurchaseInvoiceController;
use App\Http\Controllers\Admin\Purchases\PurchaseReturnController;
use Illuminate\Support\Facades\Route;

Route::middleware('permission:purchases.view')->group(function () {
    Route::get('purchases', [PurchaseInvoiceController::class, 'index'])->name('purchases.index');
    Route::get('purchase-returns', [PurchaseReturnController::class, 'index'])->name('purchase-returns.index');
    Route::middleware('permission:purchases.create')->group(function () {
        Route::get('purchases/create', [PurchaseInvoiceController::class, 'create'])->name('purchases.create');
        Route::post('purchases', [PurchaseInvoiceController::class, 'store'])->name('purchases.store');
        Route::get('purchase-returns/create', [PurchaseReturnController::class, 'create'])->name('purchase-returns.create');
        Route::post('purchase-returns', [PurchaseReturnController::class, 'store'])->name('purchase-returns.store');
    });
    Route::get('purchases/{purchase}', [PurchaseInvoiceController::class, 'show'])->name('purchases.show');
    Route::get('purchases/{purchase}/print', [PurchaseInvoiceController::class, 'print'])->name('purchases.print');
    Route::get('purchase-returns/{purchase_return}', [PurchaseReturnController::class, 'show'])->name('purchase-returns.show');
    Route::middleware('permission:purchases.update')->group(function () {
        Route::get('purchases/{purchase}/edit', [PurchaseInvoiceController::class, 'edit'])->name('purchases.edit');
        Route::get('purchase-returns/{purchase_return}/edit', [PurchaseReturnController::class, 'edit'])->name('purchase-returns.edit');
        Route::match(['put', 'patch'], 'purchase-returns/{purchase_return}', [PurchaseReturnController::class, 'update'])->name('purchase-returns.update');
        Route::match(['put', 'patch'], 'purchases/{purchase}', [PurchaseInvoiceController::class, 'update'])->name('purchases.update');
        Route::post('purchases/{purchase}/confirm', [PurchaseInvoiceController::class, 'confirm'])->name('purchases.confirm');
        Route::post('purchases/{purchase}/pay', [PurchaseInvoiceController::class, 'pay'])->name('purchases.pay');
        Route::post('purchases/{purchase}/attachment', [PurchaseInvoiceController::class, 'attachment'])->name('purchases.attachment');
        Route::post('purchases/{purchase}/cancel', [PurchaseInvoiceController::class, 'cancel'])->name('purchases.cancel');
        Route::post('purchase-returns/{purchase_return}/confirm', [PurchaseReturnController::class, 'confirm'])->name('purchase-returns.confirm');
    });
    Route::middleware('permission:purchases.delete')->group(function () {
        Route::delete('purchases/{purchase}', [PurchaseInvoiceController::class, 'destroy'])->name('purchases.destroy');
        Route::delete('purchase-returns/{purchase_return}', [PurchaseReturnController::class, 'destroy'])->name('purchase-returns.destroy');
    });
});

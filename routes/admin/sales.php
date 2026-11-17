<?php

use App\Http\Controllers\Admin\Sales\PosController;
use App\Http\Controllers\Admin\Sales\SalesInvoiceController;
use App\Http\Controllers\Admin\Sales\SalesReturnController;
use Illuminate\Support\Facades\Route;

Route::middleware('permission:pos.view')->group(function () {
    Route::get('pos', [PosController::class, 'index'])->name('pos.index');
});
Route::middleware('permission:pos.create')->group(function () {
    Route::get('pos/sell', [PosController::class, 'sell'])->name('pos.sell');
    Route::post('pos/shift/open', [PosController::class, 'openShift'])->name('pos.shift.open');
    Route::post('pos/shift/close', [PosController::class, 'closeShift'])->name('pos.shift.close');
    Route::post('pos/checkout', [PosController::class, 'checkout'])->name('pos.checkout');
});
Route::middleware('permission:sales.view')->group(function () {
    Route::get('sales', [SalesInvoiceController::class, 'index'])->name('sales.index');
    Route::get('sales-returns', [SalesReturnController::class, 'index'])->name('sales-returns.index');
    Route::middleware('permission:sales.create')->group(function () {
        Route::get('sales/create', [SalesInvoiceController::class, 'create'])->name('sales.create');
        Route::post('sales', [SalesInvoiceController::class, 'store'])->name('sales.store');
        Route::get('sales-returns/create', [SalesReturnController::class, 'create'])->name('sales-returns.create');
        Route::post('sales-returns', [SalesReturnController::class, 'store'])->name('sales-returns.store');
    });
    Route::get('sales/{sale}', [SalesInvoiceController::class, 'show'])->name('sales.show');
    Route::get('sales/{sale}/print', [SalesInvoiceController::class, 'print'])->name('sales.print');
    Route::get('sales-returns/{sales_return}', [SalesReturnController::class, 'show'])->name('sales-returns.show');
    Route::middleware('permission:sales.update')->group(function () {
        Route::get('sales/{sale}/edit', [SalesInvoiceController::class, 'edit'])->name('sales.edit');
        Route::get('sales-returns/{sales_return}/edit', [SalesReturnController::class, 'edit'])->name('sales-returns.edit');
        Route::match(['put', 'patch'], 'sales-returns/{sales_return}', [SalesReturnController::class, 'update'])->name('sales-returns.update');
        Route::match(['put', 'patch'], 'sales/{sale}', [SalesInvoiceController::class, 'update'])->name('sales.update');
        Route::post('sales/{sale}/confirm', [SalesInvoiceController::class, 'confirm'])->name('sales.confirm');
        Route::post('sales/{sale}/pay', [SalesInvoiceController::class, 'pay'])->name('sales.pay');
        Route::post('sales/{sale}/cancel', [SalesInvoiceController::class, 'cancel'])->name('sales.cancel');
        Route::post('sales-returns/{sales_return}/confirm', [SalesReturnController::class, 'confirm'])->name('sales-returns.confirm');
    });
    Route::middleware('permission:sales.delete')->group(function () {
        Route::delete('sales/{sale}', [SalesInvoiceController::class, 'destroy'])->name('sales.destroy');
        Route::delete('sales-returns/{sales_return}', [SalesReturnController::class, 'destroy'])->name('sales-returns.destroy');
    });
});

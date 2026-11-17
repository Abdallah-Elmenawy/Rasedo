<?php

use App\Http\Controllers\Admin\Products\ProductCategoryController;
use App\Http\Controllers\Admin\Products\ProductController;
use Illuminate\Support\Facades\Route;

Route::get('products/scan', [ProductController::class, 'scan'])->name('products.scan');

Route::middleware('permission:products.view')->group(function () {
    Route::get('products/{product}/barcode', [ProductController::class, 'barcode'])->name('products.barcode');
    Route::get('products/{product}/qr', [ProductController::class, 'qr'])->name('products.qr');
    Route::get('products/{product}/label', [ProductController::class, 'label'])->name('products.label');
    Route::get('products', [ProductController::class, 'index'])->name('products.index');
    Route::get('categories', [ProductCategoryController::class, 'index'])->name('categories.index');
    Route::middleware('permission:products.create')->group(function () {
        Route::get('products/create', [ProductController::class, 'create'])->name('products.create');
        Route::post('products', [ProductController::class, 'store'])->name('products.store');
        Route::post('categories', [ProductCategoryController::class, 'store'])->name('categories.store');
    });
    Route::middleware('permission:products.update')->group(function () {
        Route::get('products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
        Route::post('products/{product}/serials', [ProductController::class, 'storeSerial'])->name('products.serials.store');
        Route::delete('products/{product}/serials/{serial}', [ProductController::class, 'destroySerial'])->name('products.serials.destroy');
        Route::match(['put', 'patch'], 'products/{product}', [ProductController::class, 'update'])->name('products.update');
        Route::match(['put', 'patch'], 'categories/{category}', [ProductCategoryController::class, 'update'])->name('categories.update');
    });
    Route::get('products/{product}', [ProductController::class, 'show'])->name('products.show');
    Route::middleware('permission:products.delete')->group(function () {
        Route::delete('products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
        Route::delete('categories/{category}', [ProductCategoryController::class, 'destroy'])->name('categories.destroy');
    });
});

<?php

use App\Http\Controllers\Admin\Inventory\InventoryController;
use Illuminate\Support\Facades\Route;

Route::middleware('permission:inventory.view')->group(function () {
    Route::get('inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::get('inventory/{product}', [InventoryController::class, 'show'])->name('inventory.show');
    Route::post('inventory', [InventoryController::class, 'store'])->middleware('permission:inventory.create')->name('inventory.store');
});

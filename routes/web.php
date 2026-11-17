<?php

use App\Http\Controllers\TwoFactorController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check() ? redirect(home_route()) : redirect()->route('login');
});

Route::middleware('auth')->group(function () {
    Route::resource('verify', TwoFactorController::class)->only(['index', 'store']);
    Route::post('verify/resend', [TwoFactorController::class, 'resend'])->name('verify.resend');
});

Route::middleware(['auth', 'twofactor'])->group(function () {
    Route::get('exports/{sheet}', \App\Http\Controllers\Admin\ExportController::class)->name('exports.download');
    require __DIR__.'/admin/dashboard.php';
    require __DIR__.'/admin/customers.php';
    require __DIR__.'/admin/suppliers.php';
    require __DIR__.'/admin/products.php';
    require __DIR__.'/admin/inventory.php';
    require __DIR__.'/admin/sales.php';
    require __DIR__.'/admin/purchases.php';
    require __DIR__.'/admin/accounting.php';
    require __DIR__.'/admin/treasury.php';
    require __DIR__.'/admin/expenses.php';
    require __DIR__.'/admin/reports.php';
    require __DIR__.'/admin/public-settings.php';
});

require __DIR__.'/auth.php';

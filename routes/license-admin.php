<?php

/**
 * License server admin panel — NOT loaded on buyer ERP installs.
 *
 * Not registered in the buyer ERP build (config license.admin_enabled is false).
 */

use App\Http\Controllers\LicenseServer\LicenseAdminAuthController;
use App\Http\Controllers\LicenseServer\LicenseAdminController;
use App\Http\Middleware\LicenseAdminAuthenticate;
use Illuminate\Support\Facades\Route;

Route::prefix('license-admin')->name('license-admin.')->group(function () {
    Route::get('login', [LicenseAdminAuthController::class, 'showLogin'])->name('login');
    Route::post('login', [LicenseAdminAuthController::class, 'login'])->name('login.submit');
    Route::post('logout', [LicenseAdminAuthController::class, 'logout'])->name('logout');

    Route::middleware(LicenseAdminAuthenticate::class)->group(function () {
        Route::get('/', [LicenseAdminController::class, 'index'])->name('dashboard');
        Route::get('/create', [LicenseAdminController::class, 'create'])->name('create');
        Route::post('/', [LicenseAdminController::class, 'store'])->name('store');
        Route::get('/licenses/{license}', [LicenseAdminController::class, 'show'])->name('show');
        Route::put('/licenses/{license}', [LicenseAdminController::class, 'update'])->name('update');
        Route::patch('/licenses/{license}/status', [LicenseAdminController::class, 'updateStatus'])->name('status');
        Route::delete('/licenses/{license}/domains/{domain}', [LicenseAdminController::class, 'destroyDomain'])
            ->name('domains.destroy');
    });
});


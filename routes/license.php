<?php

use App\Http\Controllers\LicenseController;
use Illuminate\Support\Facades\Route;

/*
| Buyer activation UI (excluded from license middleware via config/license.php)
*/
Route::prefix('license')->name('license.')->group(function () {
    Route::redirect('/activate', '/license');
    Route::get('/', [LicenseController::class, 'showActivate'])->name('activate');
    Route::post('/activate', [LicenseController::class, 'activate'])->name('activate.submit');
    Route::post('/add-domain', [LicenseController::class, 'addDomain'])->name('add-domain');
    Route::get('/status', [LicenseController::class, 'status'])->name('status');
    Route::post('/deactivate', [LicenseController::class, 'deactivate'])->name('deactivate');
});


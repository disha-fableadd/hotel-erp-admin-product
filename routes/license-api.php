<?php

/**
 * License server API — NOT loaded on buyer ERP installs.
 *
 * Not registered in the buyer ERP build. Buyers call LICENSE_SERVER_URL/api/v1/license/*
 */

use App\Http\Controllers\LicenseServer\LicenseApiController;
use App\Http\Middleware\LicenseServerApiKey;
use Illuminate\Support\Facades\Route;

Route::prefix('api/v1/license')
    ->middleware([LicenseServerApiKey::class])
    ->group(function () {
        Route::post('/activate', [LicenseApiController::class, 'activate']);
        Route::post('/verify', [LicenseApiController::class, 'verify']);
        Route::post('/add-domain', [LicenseApiController::class, 'addDomain']);
    });


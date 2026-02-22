<?php

use App\Http\Controllers\Api\V1\LicenseController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('license/check', [LicenseController::class, 'check'])
        ->middleware('throttle:30,1')
        ->name('api.v1.license.check');
});

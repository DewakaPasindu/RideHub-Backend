<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Location\LocationController;
use App\Http\Controllers\Api\V1\Customer\CustomerProfileController;
use App\Http\Controllers\Api\V1\Driver\DriverApplicationController;

Route::prefix('v1')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Authentication
    |--------------------------------------------------------------------------
    */

    Route::prefix('auth')->group(function () {

        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);

        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::get('/me', [AuthController::class, 'me']);
        });

    });

    /*
    |--------------------------------------------------------------------------
    | Locations
    |--------------------------------------------------------------------------
    */

    Route::prefix('locations')->group(function () {

        Route::get('/countries', [LocationController::class, 'countries']);
        Route::get('/provinces/{countryUuid}', [LocationController::class, 'provinces']);
        Route::get('/districts/{provinceUuid}', [LocationController::class, 'districts']);
        Route::get('/cities/{districtUuid}', [LocationController::class, 'cities']);
        Route::get('/areas/{cityUuid}', [LocationController::class, 'areas']);

    });

    /*
    |--------------------------------------------------------------------------
    | Protected Routes
    |--------------------------------------------------------------------------
    */

    Route::middleware('auth:sanctum')->group(function () {

        Route::prefix('customer')->group(function () {

            Route::post('/profile', [CustomerProfileController::class, 'store']);
            Route::get('/profile', [CustomerProfileController::class, 'show']);
            Route::put('/profile', [CustomerProfileController::class, 'update']);
            Route::delete('/profile', [CustomerProfileController::class, 'destroy']);

        });

        Route::prefix('driver')->group(function () {

            Route::post('/application', [DriverApplicationController::class, 'store']);
            Route::get('/application', [DriverApplicationController::class, 'show']);
            Route::put('/application', [DriverApplicationController::class, 'update']);
            Route::delete('/application', [DriverApplicationController::class, 'destroy']);

        });

    });

});
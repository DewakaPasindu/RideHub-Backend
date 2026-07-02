<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Location\LocationController;


Route::prefix('v1')->group(function () {

    Route::prefix('auth')->group(function () {

        Route::post('/register', [AuthController::class, 'register']);

        Route::post('/login', [AuthController::class, 'login']);

        Route::middleware('auth:sanctum')->group(function () {

            Route::post('/logout', [AuthController::class, 'logout']);

            Route::get('/me', [AuthController::class, 'me']);

        });

    });

});

Route::prefix('v1')->group(function () {

    Route::prefix('locations')->group(function () {

        Route::get('/countries', [LocationController::class, 'countries']);

        Route::get('/provinces/{countryUuid}', [LocationController::class, 'provinces']);

        Route::get('/districts/{provinceUuid}', [LocationController::class, 'districts']);

        Route::get('/cities/{districtUuid}', [LocationController::class, 'cities']);

        Route::get('/areas/{cityUuid}', [LocationController::class, 'areas']);

    });

});
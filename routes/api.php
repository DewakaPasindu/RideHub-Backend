<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Location\LocationController;
use App\Http\Controllers\Api\V1\Admin\AdminDriverApplicationController;
use App\Http\Controllers\Api\V1\Customer\CustomerProfileController;
use App\Http\Controllers\Api\V1\Driver\DriverApplicationController;
use App\Http\Controllers\Api\V1\VehicleOwner\VehicleOwnerProfileController;
use App\Http\Controllers\Api\V1\Admin\VehicleOwnerReviewController;
use App\Http\Controllers\Api\V1\VehicleOwner\VehicleController;
use App\Http\Controllers\Api\V1\VehicleOwner\VehicleDocumentController;
use App\Http\Controllers\Api\V1\VehicleOwner\VehicleImageController;

Route::prefix('v1')->group(function () {

    Route::get('/ping', function () {
    return response()->json([
        'status' => 'ok',
        'time' => now(),
    ]);
});

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

        /*
        |--------------------------------------------------------------------------
        | Customer
        |--------------------------------------------------------------------------
        */

        Route::prefix('customer')->group(function () {

            Route::get('/profile', [CustomerProfileController::class, 'show']);
            Route::put('/profile', [CustomerProfileController::class, 'update']);
            Route::post('/profile/avatar', [CustomerProfileController::class, 'uploadAvatar']);

        });

        /*
        |--------------------------------------------------------------------------
        | Driver
        |--------------------------------------------------------------------------
        */

        Route::prefix('driver')->group(function () {

                Route::post('/application', [DriverApplicationController::class, 'store']);
                Route::get('/application', [DriverApplicationController::class, 'show']);     
                Route::put('/application', [DriverApplicationController::class, 'update']);
                Route::delete('/application', [DriverApplicationController::class, 'destroy']);

            

        });

        /*
        |--------------------------------------------------------------------------
        | Admin - Driver Applications
        |--------------------------------------------------------------------------
        */

        Route::prefix('admin')->group(function () {

                        Route::get(
                    '/driver-applications',
                    [AdminDriverApplicationController::class, 'index']
                );

                Route::get(
                    '/driver-applications/{uuid}',
                    [AdminDriverApplicationController::class, 'show']
                );

                Route::patch(
                    '/driver-applications/{uuid}/more-info',
                    [AdminDriverApplicationController::class, 'requestMoreInformation']
                );

                        
                Route::patch(
                    '/driver-applications/{uuid}/approve',
                    [AdminDriverApplicationController::class, 'approve']
                );

                        
                Route::patch(
                    '/driver-applications/{uuid}/reject',
                    [AdminDriverApplicationController::class, 'reject']
                );

                Route::patch(
                    '/vehicle-owner-profiles/{uuid}/approve',
                    [VehicleOwnerReviewController::class, 'approve']
                );

                Route::patch(
                    '/vehicle-owner-profiles/{uuid}/reject',
                    [VehicleOwnerReviewController::class, 'reject']
                );

                Route::patch(
                    '/vehicle-owner-profiles/{uuid}/more-info',
                    [VehicleOwnerReviewController::class, 'requestMoreInformation']
                );

            
        });

        /*
        |--------------------------------------------------------------------------
        | Vehicle Owner
        |--------------------------------------------------------------------------
        */

        Route::prefix('vehicle-owner')->group(function () {

            Route::post(
                '/profile',
                [VehicleOwnerProfileController::class, 'store']
            );

            Route::get(
                '/profile',
                [VehicleOwnerProfileController::class, 'show']
            );

            Route::put(
                '/profile',
                [VehicleOwnerProfileController::class, 'update']
            );

            /*
            |--------------------------------------------------------------------------
            | Vehicle Documents
            |--------------------------------------------------------------------------
            */

            Route::prefix('vehicles/{vehicle}/documents')->group(function () {

                Route::get(
                    '/',
                    [VehicleDocumentController::class, 'index']
                );

                Route::post(
                    '/',
                    [VehicleDocumentController::class, 'store']
                );

                Route::delete(
                    '/{documentUuid}',
                    [VehicleDocumentController::class, 'destroy']
                );

            });

            /*
            |--------------------------------------------------------------------------
            | Vehicle Images
            |--------------------------------------------------------------------------
            */

            Route::prefix('vehicles/{vehicle}/images')->group(function () {

                Route::get(
                    '/',
                    [VehicleImageController::class, 'index']
                );

                Route::post(
                    '/',
                    [VehicleImageController::class, 'store']
                );

                Route::delete(
                    '/{imageUuid}',
                    [VehicleImageController::class, 'destroy']
                );

            });

            

            /*
        |--------------------------------------------------------------------------
        | Vehicles
        |--------------------------------------------------------------------------
        */

        Route::get(
            '/vehicles',
            [VehicleController::class, 'index']
        );

        Route::post(
            '/vehicles',
            [VehicleController::class, 'store']
        );

        Route::get(
            '/vehicles/{vehicle}',
            [VehicleController::class, 'show']
        );

        Route::put(
            '/vehicles/{vehicle}',
            [VehicleController::class, 'update']
        );

        Route::delete(
            '/vehicles/{vehicle}',
            [VehicleController::class, 'destroy']
        );

        });

    });

});
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

use App\Http\Controllers\Api\V1\Driver\DriverController;
use App\Http\Controllers\Api\V1\Driver\DriverAvailabilityController;
use App\Http\Controllers\Api\V1\Booking\BookingController;
use App\Http\Controllers\Api\V1\Shared\ReviewController;
use App\Http\Controllers\Api\V1\Location\LocationSearchController;
use App\Http\Controllers\Api\V1\AI\AIController;
use App\Http\Controllers\Api\V1\Notification\NotificationController;
use App\Http\Controllers\Api\V1\Rental\RentalApplicationController;
use App\Http\Controllers\Api\V1\Rental\RentalDocumentController;
use App\Http\Controllers\Api\V1\Rental\OwnerRentalRequestController;
use App\Http\Controllers\Api\V1\Rental\RentalConditionController;
use App\Http\Controllers\Api\V1\Rental\RentalHandoverController;
use App\Http\Controllers\Api\V1\Rental\RentalLocationController;
use App\Http\Controllers\Api\V1\Rental\RentalReturnController;

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
    | Locations (Administrative Hierarchy)
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
    | Maps & Road Routing APIs (Public)
    |--------------------------------------------------------------------------
    */
    Route::get('/locations/search', [LocationSearchController::class, 'search']);
    Route::get('/locations/reverse', [LocationSearchController::class, 'reverse']);
    Route::get('/routes/distance', [LocationSearchController::class, 'distance']);
    Route::get('/drivers/{driver}/location', [LocationSearchController::class, 'getDriverLocation']);

    /*
    |--------------------------------------------------------------------------
    | Drivers Public Listing (Public)
    |--------------------------------------------------------------------------
    */
    Route::get('/drivers', [DriverController::class, 'index']);
    Route::get('/drivers/{uuid}', [DriverController::class, 'show']);
    Route::get('/drivers/by-user/{userId}', [DriverController::class, 'byUser']);
    Route::get('/drivers/{driver}/availability', [DriverAvailabilityController::class, 'driverAvailability']);

    /*
    |--------------------------------------------------------------------------
    | Vehicles Public Listing (Public)
    |--------------------------------------------------------------------------
    |*/
    Route::get('/vehicles', [VehicleController::class, 'listPublic']);
    Route::get('/vehicles/{uuid}', [VehicleController::class, 'showPublic']);

    /*
    |--------------------------------------------------------------------------
    | Reviews & Stats (Public)
    |--------------------------------------------------------------------------
    */
    Route::get('/vehicles/{vehicle}/reviews', [ReviewController::class, 'vehicleReviews']);
    Route::get('/drivers/{driver}/reviews', [ReviewController::class, 'driverReviews']);
    Route::get('/vehicles/{vehicle}/reviews/stats', [ReviewController::class, 'vehicleStats']);
    Route::get('/drivers/{driver}/reviews/stats', [ReviewController::class, 'driverStats']);

    /*
    |--------------------------------------------------------------------------
    | Protected Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware('auth:sanctum')->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Customer Profile
        |--------------------------------------------------------------------------
        */
        Route::prefix('customer')->group(function () {
            Route::get('/profile', [CustomerProfileController::class, 'show']);
            Route::put('/profile', [CustomerProfileController::class, 'update']);
            Route::post('/profile/avatar', [CustomerProfileController::class, 'uploadAvatar']);
        });

        /*
        |--------------------------------------------------------------------------
        | Driver Applications & Profiles
        |--------------------------------------------------------------------------
        */
        Route::prefix('driver')->group(function () {
            Route::post('/application', [DriverApplicationController::class, 'store']);
            Route::middleware('role:Driver')->group(function () {
                Route::get('/application', [DriverApplicationController::class, 'show']);     
                Route::put('/application', [DriverApplicationController::class, 'update']);
                Route::delete('/application', [DriverApplicationController::class, 'destroy']);
            });
        });

        Route::post('/drivers/register', [DriverController::class, 'register']);
        Route::post('/drivers', [DriverController::class, 'register']);
        
        Route::middleware('role:Driver')->group(function () {
            Route::put('/drivers/{uuid}', [DriverController::class, 'update']);
            Route::post('/drivers/{uuid}/documents', [DriverController::class, 'uploadDocuments']);
            Route::post('/drivers/{driver}/location', [LocationSearchController::class, 'updateDriverLocation']);
            Route::get('/availability', [DriverAvailabilityController::class, 'index']);
            Route::post('/availability', [DriverAvailabilityController::class, 'store']);
            Route::put('/availability/{uuid}', [DriverAvailabilityController::class, 'update']);
            Route::delete('/availability/{uuid}', [DriverAvailabilityController::class, 'destroy']);
        });

        Route::post('/vehicles/{uuid}/documents', [VehicleController::class, 'uploadDocuments']);

        /*
        |--------------------------------------------------------------------------
        | Vehicle Owner Profile & Vehicles
        |--------------------------------------------------------------------------
        |*/
        Route::prefix('vehicle-owner')->group(function () {
            Route::post('/profile', [VehicleOwnerProfileController::class, 'store']);
            
            Route::middleware('role:Vehicle Owner')->group(function () {
                Route::get('/profile', [VehicleOwnerProfileController::class, 'show']);
                Route::put('/profile', [VehicleOwnerProfileController::class, 'update']);

                Route::get('/vehicles', [VehicleController::class, 'index']);
                Route::post('/vehicles', [VehicleController::class, 'store']);
                Route::get('/vehicles/{vehicle}', [VehicleController::class, 'show']);
                Route::put('/vehicles/{vehicle}', [VehicleController::class, 'update']);
                Route::delete('/vehicles/{vehicle}', [VehicleController::class, 'destroy']);
            });
        });

        /*
        |--------------------------------------------------------------------------
        | Bookings
        |--------------------------------------------------------------------------
        */
        Route::post('/bookings', [BookingController::class, 'store']);
        Route::get('/bookings', [BookingController::class, 'index']);
        Route::get('/bookings/{booking}', [BookingController::class, 'show']);
        Route::put('/bookings/{booking}/cancel', [BookingController::class, 'cancel']);
        Route::patch('/bookings/{booking}/status', [BookingController::class, 'updateStatus']);

        /*
        |--------------------------------------------------------------------------
        | Reviews Submission
        |--------------------------------------------------------------------------
        */
        Route::post('/reviews', [ReviewController::class, 'store']);

        /*
        |--------------------------------------------------------------------------
        | AI Recommendations
        |--------------------------------------------------------------------------
        */
        Route::post('/ai/vehicle-recommendations', [AIController::class, 'vehicleRecommendations']);
        Route::post('/ai/driver-matching', [AIController::class, 'driverMatching']);
        Route::post('/ai/chat', [AIController::class, 'chat']);

        /*
        |--------------------------------------------------------------------------
        | Notifications
        |--------------------------------------------------------------------------
        */
        Route::get('/notifications', [NotificationController::class, 'index']);
        Route::patch('/notifications/{notification}/read', [NotificationController::class, 'markAsRead']);
        Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);

        /*
        |--------------------------------------------------------------------------
        | Admin Operations
        |--------------------------------------------------------------------------
        */
        Route::prefix('admin')->middleware('role:Admin|Super Admin')->group(function () {
            // Driver Applications
            Route::get('/driver-applications', [AdminDriverApplicationController::class, 'index']);
            Route::get('/driver-applications/{uuid}', [AdminDriverApplicationController::class, 'show']);
            Route::patch('/driver-applications/{uuid}/more-info', [AdminDriverApplicationController::class, 'requestMoreInformation']);
            Route::patch('/driver-applications/{uuid}/approve', [AdminDriverApplicationController::class, 'approve']);
            Route::patch('/driver-applications/{uuid}/reject', [AdminDriverApplicationController::class, 'reject']);

            // Drivers
            Route::post('/drivers/{uuid}/approve', [DriverController::class, 'approve']);
            Route::post('/drivers/{uuid}/reject', [DriverController::class, 'reject']);
            Route::post('/drivers/{uuid}/suspend', [DriverController::class, 'suspend']);
            Route::get('/drivers/pending-count', [DriverController::class, 'pendingCount']);

            // Vehicles
            Route::get('/vehicles/pending-count', [VehicleController::class, 'pendingCount']);

            // Vehicle Owners
            Route::patch('/vehicle-owner-profiles/{uuid}/approve', [VehicleOwnerReviewController::class, 'approve']);
            Route::patch('/vehicle-owner-profiles/{uuid}/reject', [VehicleOwnerReviewController::class, 'reject']);
            Route::patch('/vehicle-owner-profiles/{uuid}/more-info', [VehicleOwnerReviewController::class, 'requestMoreInformation']);

            // Bookings
            Route::get('/bookings', [BookingController::class, 'listAll']);
            Route::post('/bookings/{booking}/approve', [BookingController::class, 'approve']);
            Route::post('/bookings/{booking}/reject', [BookingController::class, 'reject']);
            Route::post('/bookings/{booking}/assign-driver', [BookingController::class, 'assignDriver']);
            Route::post('/bookings/{booking}/start-trip', [BookingController::class, 'startTrip']);
            Route::post('/bookings/{booking}/complete-trip', [BookingController::class, 'completeTrip']);
            Route::get('/bookings/status-counts', [BookingController::class, 'statusCounts']);

            // Reviews Moderation
            Route::get('/reviews', [ReviewController::class, 'listAll']);
            Route::post('/reviews/{id}/approve', [ReviewController::class, 'approveReview']);
            Route::post('/reviews/{id}/reject', [ReviewController::class, 'rejectReview']);
            Route::delete('/reviews/{id}', [ReviewController::class, 'deleteReview']);
        });

        /*
        |--------------------------------------------------------------------------
        | Self-Drive Rentals
        |--------------------------------------------------------------------------
        */
        // Customer rental applications
        Route::get('/rental-applications', [RentalApplicationController::class, 'index']);
        Route::post('/rental-applications', [RentalApplicationController::class, 'store']);
        Route::get('/rental-applications/{uuid}', [RentalApplicationController::class, 'show']);
        Route::put('/rental-applications/{uuid}', [RentalApplicationController::class, 'update']);
        Route::post('/rental-applications/{uuid}/submit', [RentalApplicationController::class, 'submit']);
        Route::post('/rental-applications/{uuid}/documents', [RentalApplicationController::class, 'uploadDocument']);
        Route::post('/rental-applications/{uuid}/live-photo', [RentalApplicationController::class, 'uploadLivePhoto']);

        // Secure document streaming
        Route::get('/rental-documents/{uuid}', [RentalDocumentController::class, 'show'])->name('rental-documents.show');

        // Owner review endpoints
        Route::get('/owner/rental-requests', [OwnerRentalRequestController::class, 'index']);
        Route::get('/owner/rental-requests/{uuid}', [RentalApplicationController::class, 'show']);
        Route::post('/owner/rental-requests/{uuid}/approve', [OwnerRentalRequestController::class, 'approve']);
        Route::post('/owner/rental-requests/{uuid}/reject', [OwnerRentalRequestController::class, 'reject']);
        Route::post('/owner/rental-requests/{uuid}/request-information', [OwnerRentalRequestController::class, 'requestInformation']);

        // Vehicle condition report endpoints
        Route::get('/rentals/{uuid}/condition', [RentalConditionController::class, 'index']);
        Route::post('/rentals/{uuid}/condition', [RentalConditionController::class, 'store']);
        Route::post('/rentals/{uuid}/condition/photos', [RentalConditionController::class, 'uploadPhoto']);
        Route::get('/rentals/{uuid}/condition-comparison', [RentalConditionController::class, 'getComparison']);

        // Handover endpoints
        Route::get('/rentals/{uuid}/handover', [RentalHandoverController::class, 'show']);
        Route::post('/rentals/{uuid}/handover/customer-confirm', [RentalHandoverController::class, 'customerConfirm']);
        Route::post('/rentals/{uuid}/handover/owner-confirm', [RentalHandoverController::class, 'ownerConfirm']);

        // Live location endpoints
        Route::post('/rentals/{uuid}/location', [RentalLocationController::class, 'ping']);
        Route::get('/rentals/{uuid}/location/latest', [RentalLocationController::class, 'latest']);
        Route::get('/rentals/{uuid}/location/history', [RentalLocationController::class, 'history']);

        // Return completion endpoint
        Route::post('/rentals/{uuid}/return', [RentalReturnController::class, 'completeReturn']);

    });

});
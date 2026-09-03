<?php

namespace App\Http\Controllers\Api\V1\Rental;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Resources\RentalApplicationResource;
use App\Models\Booking;
use App\Models\RentalApplication;
use App\Models\Vehicle;
use App\Services\Rental\RentalNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OwnerRentalRequestController extends BaseApiController
{
    /**
     * List all rental requests for vehicles owned by the auth user.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $requests = RentalApplication::with(['vehicle', 'customer'])
            ->whereHas('vehicle.vehicleOwnerProfile', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->latest()
            ->get();

        return $this->success(
            RentalApplicationResource::collection($requests),
            'Owner rental requests retrieved successfully.'
        );
    }

    /**
     * Approve a rental request.
     */
    public function approve(Request $request, string $uuid): JsonResponse
    {
        $app = RentalApplication::with(['vehicle.vehicleOwnerProfile', 'documents'])->where('uuid', $uuid)->first();

        if (!$app) {
            return $this->error('Rental request not found.', null, 404);
        }

        // 1 & 2. Verify auth user is the owner
        $ownerUserId = $app->vehicle?->vehicleOwnerProfile?->user_id;
        if (!$ownerUserId || auth()->id() !== $ownerUserId) {
            return $this->error('Unauthorized: You do not own this vehicle.', null, 403);
        }

        // 4. Verify status is submitted or under_review
        if (!in_array($app->status, ['submitted', 'under_review'])) {
            return $this->error('Application is not eligible for review.', null, 422);
        }

        // 5. Verify documents are present
        if ($app->documents->count() < 5) {
            return $this->error('Verification error: Customer documents are incomplete.', null, 422);
        }

        // 6 & 7. Perform double-booking/availability checks using Transaction and Lock
        return DB::transaction(function () use ($app) {
            // Lock vehicle row
            $vehicle = Vehicle::where('id', $app->vehicle_id)->lockForUpdate()->first();
            
            if ($vehicle->application_status !== 'approved') {
                return $this->error('Vehicle is not approved or available.', null, 422);
            }

            // Check for overlapping APPROVED or ACTIVE bookings for the vehicle
            $overlapBooking = Booking::where('vehicle_id', $app->vehicle_id)
                ->whereIn('status', ['approved', 'active'])
                ->where(function ($query) use ($app) {
                    $query->where(function ($q) use ($app) {
                        $q->where('start_date', '<=', $app->end_at->toDateString())
                          ->where('end_date', '>=', $app->start_at->toDateString());
                    });
                })->exists();

            if ($overlapBooking) {
                return $this->error('Double Booking: Selected vehicle is already reserved for these dates.', null, 422);
            }

            // Calculate total price based on price_per_day
            $days = max(1, $app->start_at->diffInDays($app->end_at));
            $totalAmount = $days * $app->vehicle->price_per_day;

            // Create system Booking record
            $booking = Booking::create([
                'uuid' => (string) Str::uuid(),
                'user_id' => $app->customer_id,
                'booking_type' => 'vehicle',
                'vehicle_id' => $app->vehicle_id,
                'start_date' => $app->start_at->toDateString(),
                'end_date' => $app->end_at->toDateString(),
                'start_time' => $app->start_at->toTimeString(),
                'end_time' => $app->end_at->toTimeString(),
                'pickup_location' => $app->pickup_address,
                'dropoff_location' => $app->return_address,
                'pickup_lat' => $app->pickup_latitude,
                'pickup_lng' => $app->pickup_longitude,
                'dropoff_lat' => $app->return_latitude,
                'dropoff_lng' => $app->return_longitude,
                'passenger_count' => $app->passenger_count,
                'total_amount' => $totalAmount,
                'status' => 'approved',
                'payment_method' => 'cash',
                'approved_at' => now(),
                'approved_by' => auth()->id(),
            ]);

            // Update rental application state
            $app->status = 'owner_approved';
            $app->booking_id = $booking->id;
            $app->approved_at = now();
            $app->save();

            RentalNotificationService::notify(
                $app,
                'OWNER_APPROVED_RENTAL',
                'Rental Request Approved',
                "Your rental request for {$app->vehicle->make} {$app->vehicle->model} was approved! Next step: prepare handover condition."
            );

            return $this->success(
                new RentalApplicationResource($app),
                'Rental application approved and vehicle reserved successfully.'
            );
        });
    }

    /**
     * Reject a rental request.
     */
    public function reject(Request $request, string $uuid): JsonResponse
    {
        $app = RentalApplication::with(['vehicle.vehicleOwnerProfile'])->where('uuid', $uuid)->first();

        if (!$app) {
            return $this->error('Rental request not found.', null, 404);
        }

        $ownerUserId = $app->vehicle?->vehicleOwnerProfile?->user_id;
        if (!$ownerUserId || auth()->id() !== $ownerUserId) {
            return $this->error('Unauthorized.', null, 403);
        }

        $request->validate([
            'reason' => 'required|string|max:500'
        ]);

        $app->status = 'owner_rejected';
        $app->rejected_at = now();
        $app->more_info_reason = $request->reason;
        $app->save();

        RentalNotificationService::notify(
            $app,
            'OWNER_REJECTED_RENTAL',
            'Rental Request Rejected',
            "Your rental request for the vehicle was rejected. Reason: {$request->reason}"
        );

        return $this->success(
            new RentalApplicationResource($app),
            'Rental application rejected.'
        );
    }

    /**
     * Request more information from the customer.
     */
    public function requestInformation(Request $request, string $uuid): JsonResponse
    {
        $app = RentalApplication::with(['vehicle.vehicleOwnerProfile'])->where('uuid', $uuid)->first();

        if (!$app) {
            return $this->error('Rental request not found.', null, 404);
        }

        $ownerUserId = $app->vehicle?->vehicleOwnerProfile?->user_id;
        if (!$ownerUserId || auth()->id() !== $ownerUserId) {
            return $this->error('Unauthorized.', null, 403);
        }

        $request->validate([
            'details' => 'required|string|max:500'
        ]);

        $app->status = 'more_information_required';
        $app->more_info_reason = $request->details;
        $app->save();

        RentalNotificationService::notify(
            $app,
            'MORE_INFORMATION_REQUIRED',
            'Information Required for Rental',
            "Owner requested additional details: {$request->details}"
        );

        return $this->success(
            new RentalApplicationResource($app),
            'More information requested successfully.'
        );
    }
}

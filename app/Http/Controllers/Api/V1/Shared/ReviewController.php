<?php

namespace App\Http\Controllers\Api\V1\Shared;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Resources\ReviewResource;
use App\Models\Review;
use App\Models\Booking;
use App\Models\Vehicle;
use App\Models\DriverApplication;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ReviewController extends BaseApiController
{
    /**
     * Store a new review.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'booking_id' => 'required|string',
            'target_type' => 'required|in:vehicle,driver',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        $user = $request->user();
        $data = $request->all();

        // Convert UUIDs
        $booking = Booking::where('uuid', $data['booking_id'])->first();
        if (!$booking) {
            return $this->error('Booking not found.', null, 404);
        }

        // Verify booking owner
        if ($booking->user_id !== $user->id) {
            return $this->error('You can only review your own bookings.', null, 403);
        }

        $vehicleId = null;
        if ($data['target_type'] === 'vehicle' && $booking->vehicle_id) {
            $vehicleId = $booking->vehicle_id;
        }

        $driverProfileId = null;
        if ($data['target_type'] === 'driver' && $booking->driver_profile_id) {
            $driverProfileId = $booking->driver_profile_id;
        } elseif ($data['target_type'] === 'driver' && $booking->driver_assigned_id) {
            $driverProfileId = $booking->driver_assigned_id;
        }

        // Check if already reviewed
        $existing = Review::where('booking_id', $booking->id)
            ->where('target_type', $data['target_type'])
            ->first();

        if ($existing) {
            return $this->error('You have already submitted a review for this trip.', null, 422);
        }

        $review = Review::create([
            'uuid' => (string) Str::uuid(),
            'user_id' => $user->id,
            'booking_id' => $booking->id,
            'target_type' => $data['target_type'],
            'vehicle_id' => $vehicleId,
            'driver_profile_id' => $driverProfileId,
            'rating' => $data['rating'],
            'comment' => $data['comment'] ?? null,
            'status' => 'approved', // Auto-approve reviews by default
        ]);

        return $this->success(
            new ReviewResource($review),
            'Review submitted successfully.',
            201
        );
    }

    /**
     * Get reviews for a vehicle.
     */
    public function vehicleReviews(string $vehicleUuid): JsonResponse
    {
        $vehicle = Vehicle::where('uuid', $vehicleUuid)->first();
        if (!$vehicle) {
            return $this->error('Vehicle not found.', null, 404);
        }

        $reviews = Review::with('user')
            ->where('target_type', 'vehicle')
            ->where('vehicle_id', $vehicle->id)
            ->where('status', 'approved')
            ->latest()
            ->paginate(10);

        return $this->success(
            ReviewResource::collection($reviews),
            'Vehicle reviews retrieved successfully.'
        );
    }

    /**
     * Get reviews for a driver.
     */
    public function driverReviews(string $driverUuid): JsonResponse
    {
        $driver = DriverApplication::where('uuid', $driverUuid)->first();
        if (!$driver) {
            return $this->error('Driver not found.', null, 404);
        }

        $reviews = Review::with('user')
            ->where('target_type', 'driver')
            ->where('driver_profile_id', $driver->id)
            ->where('status', 'approved')
            ->latest()
            ->paginate(10);

        return $this->success(
            ReviewResource::collection($reviews),
            'Driver reviews retrieved successfully.'
        );
    }

    /**
     * Admin list all reviews.
     */
    public function listAll(Request $request): JsonResponse
    {
        $query = Review::with(['user', 'booking']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $reviews = $query->latest()->paginate($request->get('per_page', 20));

        return $this->success(
            ReviewResource::collection($reviews),
            'All reviews retrieved successfully.'
        );
    }

    /**
     * Admin approve review.
     */
    public function approveReview(string $uuid): JsonResponse
    {
        $review = Review::where('uuid', $uuid)->first();
        if (!$review) {
            return $this->error('Review not found.', null, 404);
        }

        $review->status = 'approved';
        $review->moderated_at = now();
        $review->moderated_by = auth()->id();
        $review->save();

        return $this->success(null, 'Review approved successfully.');
    }

    /**
     * Admin reject review.
     */
    public function rejectReview(Request $request, string $uuid): JsonResponse
    {
        $review = Review::where('uuid', $uuid)->first();
        if (!$review) {
            return $this->error('Review not found.', null, 404);
        }

        $review->status = 'rejected';
        $review->moderation_note = $request->get('note', 'Rejected by moderator');
        $review->moderated_at = now();
        $review->moderated_by = auth()->id();
        $review->save();

        return $this->success(null, 'Review rejected.');
    }

    /**
     * Admin delete review.
     */
    public function deleteReview(string $uuid): JsonResponse
    {
        $review = Review::where('uuid', $uuid)->first();
        if (!$review) {
            return $this->error('Review not found.', null, 404);
        }

        $review->delete();

        return $this->success(null, 'Review deleted successfully.');
    }

    /**
     * Get review stats for a vehicle.
     */
    public function vehicleStats(string $vehicleUuid): JsonResponse
    {
        $vehicle = Vehicle::where('uuid', $vehicleUuid)->first();
        if (!$vehicle) {
            return $this->error('Vehicle not found.', null, 404);
        }

        $stats = $this->calculateStats('vehicle', $vehicle->id);
        return $this->success($stats, 'Vehicle review stats.');
    }

    /**
     * Get review stats for a driver.
     */
    public function driverStats(string $driverUuid): JsonResponse
    {
        $driver = DriverApplication::where('uuid', $driverUuid)->first();
        if (!$driver) {
            return $this->error('Driver not found.', null, 404);
        }

        $stats = $this->calculateStats('driver', $driver->id);
        return $this->success($stats, 'Driver review stats.');
    }

    /**
     * Helper to compute stats.
     */
    private function calculateStats(string $type, int $id): array
    {
        $column = $type === 'vehicle' ? 'vehicle_id' : 'driver_profile_id';
        
        $avg = Review::where('target_type', $type)
            ->where($column, $id)
            ->where('status', 'approved')
            ->avg('rating') ?? 0;

        $count = Review::where('target_type', $type)
            ->where($column, $id)
            ->where('status', 'approved')
            ->count();

        $distribution = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
        $groups = Review::where('target_type', $type)
            ->where($column, $id)
            ->where('status', 'approved')
            ->groupBy('rating')
            ->selectRaw('rating, count(*) as cnt')
            ->pluck('cnt', 'rating')
            ->toArray();

        foreach ($groups as $rating => $cnt) {
            $distribution[(int)$rating] = (int)$cnt;
        }

        return [
            'avg' => round($avg, 2),
            'count' => $count,
            'distribution' => $distribution,
        ];
    }
}

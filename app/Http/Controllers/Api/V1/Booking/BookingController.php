<?php

namespace App\Http\Controllers\Api\V1\Booking;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Resources\BookingResource;
use App\Models\Booking;
use App\Models\Vehicle;
use App\Models\DriverApplication;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BookingController extends BaseApiController
{
    /**
     * List user's bookings.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = Booking::with(['vehicle', 'driverProfile', 'driverAssigned'])
            ->where('user_id', $user->id);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('booking_type')) {
            $query->where('booking_type', $request->booking_type);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('target_name', 'like', "%{$search}%");
        }

        $bookings = $query->latest()->paginate($request->get('per_page', 12));

        return $this->success(
            BookingResource::collection($bookings),
            'User bookings retrieved successfully.'
        );
    }

    /**
     * Admin list all bookings.
     */
    public function listAll(Request $request): JsonResponse
    {
        $query = Booking::with(['user', 'vehicle', 'driverProfile', 'driverAssigned']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('booking_type')) {
            $query->where('booking_type', $request->booking_type);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('target_name', 'like', "%{$search}%")
                  ->orWhereHas('user', function($uq) use ($search) {
                      $uq->where('first_name', 'like', "%{$search}%")
                         ->orWhere('last_name', 'like', "%{$search}%");
                  });
            });
        }

        $bookings = $query->latest()->paginate($request->get('per_page', 12));

        return $this->success(
            BookingResource::collection($bookings),
            'All bookings retrieved successfully.'
        );
    }

    /**
     * Get booking details.
     */
    public function show(string $uuid): JsonResponse
    {
        $booking = Booking::with(['user', 'vehicle', 'driverProfile', 'driverAssigned'])
            ->where('uuid', $uuid)
            ->first();

        if (!$booking) {
            return $this->error('Booking not found.', null, 404);
        }

        if ($booking->user_id !== auth()->id() && !auth()->user()->hasAnyRole(['Admin', 'Super Admin'])) {
            return $this->error('You are not authorized to view this booking.', null, 403);
        }

        return $this->success(
            new BookingResource($booking),
            'Booking retrieved successfully.'
        );
    }

    /**
     * Create a booking.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'booking_type' => 'required|in:vehicle,driver',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'pickup_location' => 'required|string',
            'total_amount' => 'required|numeric|min:0',
            'payment_method' => 'nullable|string|in:cash,card',
        ]);

        $user = $request->user();
        $data = $request->all();

        // Convert UUIDs to IDs
        $vehicleId = null;
        if (!empty($data['vehicle_id'])) {
            $vehicle = Vehicle::where('uuid', $data['vehicle_id'])->first();
            if ($vehicle) {
                $vehicleId = $vehicle->id;
            }
        }

        $driverProfileId = null;
        if (!empty($data['driver_profile_id'])) {
            $driverApp = DriverApplication::where('uuid', $data['driver_profile_id'])->first();
            if ($driverApp) {
                $driverProfileId = $driverApp->id;
            }
        }

        $driverAssignedId = null;
        if (!empty($data['driver_assigned_id'])) {
            $assignedDriver = DriverApplication::where('uuid', $data['driver_assigned_id'])->first();
            if ($assignedDriver) {
                $driverAssignedId = $assignedDriver->id;
            }
        }

        // Check availability
        if ($vehicleId) {
            $conflictingVehicle = Booking::where('vehicle_id', $vehicleId)
                ->whereIn('status', ['approved', 'active'])
                ->where('start_date', '<=', $data['end_date'])
                ->where('end_date', '>=', $data['start_date'])
                ->exists();

            if ($conflictingVehicle) {
                return $this->error('Vehicle is already booked on these dates.', null, 422);
            }
        }

        if ($driverProfileId) {
            $conflictingDriver = Booking::where('driver_profile_id', $driverProfileId)
                ->whereIn('status', ['approved', 'active'])
                ->where('start_date', '<=', $data['end_date'])
                ->where('end_date', '>=', $data['start_date'])
                ->exists();

            if ($conflictingDriver) {
                return $this->error('Driver is already booked on these dates.', null, 422);
            }
        }

        $booking = Booking::create([
            'uuid' => (string) Str::uuid(),
            'user_id' => $user->id,
            'booking_type' => $data['booking_type'],
            'vehicle_id' => $vehicleId,
            'driver_profile_id' => $driverProfileId,
            'driver_assigned_id' => $driverAssignedId,
            'target_name' => $data['target_name'] ?? 'Booking',
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'start_time' => $data['start_time'] ?? null,
            'end_time' => $data['end_time'] ?? null,
            'pickup_location' => $data['pickup_location'],
            'dropoff_location' => $data['dropoff_location'] ?? null,
            'pickup_lat' => $data['pickup_lat'] ?? null,
            'pickup_lng' => $data['pickup_lng'] ?? null,
            'dropoff_lat' => $data['dropoff_lat'] ?? null,
            'dropoff_lng' => $data['dropoff_lng'] ?? null,
            'passenger_count' => $data['passenger_count'] ?? 1,
            'ac_preference' => $data['ac_preference'] ?? 'any',
            'total_amount' => $data['total_amount'],
            'advance_amount' => $data['advance_amount'] ?? 0,
            'payment_method' => $data['payment_method'] ?? 'cash',
            'status' => 'pending',
            'notes' => $data['notes'] ?? null,
        ]);

        return $this->success(
            new BookingResource($booking),
            'Booking created successfully.',
            201
        );
    }

    /**
     * Cancel booking.
     */
    public function cancel(string $uuid): JsonResponse
    {
        $booking = Booking::where('uuid', $uuid)->first();
        if (!$booking) {
            return $this->error('Booking not found.', null, 404);
        }

        if ($booking->user_id !== auth()->id() && !auth()->user()->hasAnyRole(['Admin', 'Super Admin'])) {
            return $this->error('You are not authorized to cancel this booking.', null, 403);
        }

        if (!in_array($booking->status, ['pending', 'approved'])) {
            return $this->error('Only pending or approved bookings can be cancelled.', null, 422);
        }

        $booking->status = 'cancelled';
        $booking->cancelled_at = now();
        $booking->save();

        return $this->success(new BookingResource($booking), 'Booking cancelled successfully.');
    }

    /**
     * Admin approve booking.
     */
    public function approve(string $uuid): JsonResponse
    {
        $booking = Booking::where('uuid', $uuid)->first();
        if (!$booking) {
            return $this->error('Booking not found.', null, 404);
        }

        $booking->status = 'approved';
        $booking->approved_at = now();
        $booking->approved_by = auth()->id();
        $booking->save();

        return $this->success(null, 'Booking approved successfully.');
    }

    /**
     * Admin reject booking.
     */
    public function reject(Request $request, string $uuid): JsonResponse
    {
        $booking = Booking::where('uuid', $uuid)->first();
        if (!$booking) {
            return $this->error('Booking not found.', null, 404);
        }

        $booking->status = 'rejected';
        $booking->rejection_reason = $request->get('reason', 'Booking rejected by Admin.');
        $booking->rejected_at = now();
        $booking->save();

        return $this->success(null, 'Booking rejected successfully.');
    }

    /**
     * Admin assign driver.
     */
    public function assignDriver(Request $request, string $uuid): JsonResponse
    {
        $booking = Booking::where('uuid', $uuid)->first();
        if (!$booking) {
            return $this->error('Booking not found.', null, 404);
        }

        $driver = DriverApplication::where('uuid', $request->driver_profile_id)->first();
        if (!$driver) {
            return $this->error('Driver not found.', null, 404);
        }

        $booking->driver_assigned_id = $driver->id;
        $booking->save();

        return $this->success(null, 'Driver assigned successfully.');
    }

    /**
     * Start trip.
     */
    public function startTrip(string $uuid): JsonResponse
    {
        $booking = Booking::where('uuid', $uuid)->first();
        if (!$booking) {
            return $this->error('Booking not found.', null, 404);
        }

        $booking->status = 'active';
        $booking->trip_started_at = now();
        $booking->save();

        return $this->success(null, 'Trip started successfully.');
    }

    /**
     * Complete trip.
     */
    public function completeTrip(string $uuid): JsonResponse
    {
        $booking = Booking::where('uuid', $uuid)->first();
        if (!$booking) {
            return $this->error('Booking not found.', null, 404);
        }

        $booking->status = 'completed';
        $booking->trip_ended_at = now();
        $booking->save();

        return $this->success(null, 'Trip completed successfully.');
    }

    /**
     * Get booking status counts.
     */
    public function statusCounts(): JsonResponse
    {
        $counts = [
            'pending' => Booking::where('status', 'pending')->count(),
            'approved' => Booking::where('status', 'approved')->count(),
            'active' => Booking::where('status', 'active')->count(),
            'completed' => Booking::where('status', 'completed')->count(),
            'cancelled' => Booking::where('status', 'cancelled')->count(),
            'rejected' => Booking::where('status', 'rejected')->count(),
        ];

        return $this->success($counts, 'Booking status counts.');
    }

    /**
     * Update booking status.
     */
    public function updateStatus(Request $request, string $uuid): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:pending,approved,active,completed,cancelled,rejected',
        ]);

        $booking = Booking::where('uuid', $uuid)->first();
        if (!$booking) {
            return $this->error('Booking not found.', null, 404);
        }

        $booking->status = $request->status;
        if ($request->status === 'active') {
            $booking->trip_started_at = now();
        } elseif ($request->status === 'completed') {
            $booking->trip_ended_at = now();
        } elseif ($request->status === 'cancelled') {
            $booking->cancelled_at = now();
        } elseif ($request->status === 'rejected') {
            $booking->rejected_at = now();
        }
        $booking->save();

        return $this->success(
            new BookingResource($booking),
            'Booking status updated successfully.'
        );
    }
}

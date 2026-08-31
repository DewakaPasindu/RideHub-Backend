<?php

namespace App\Http\Controllers\Api\V1\Driver;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\DriverAvailability;
use App\Models\DriverApplication;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DriverAvailabilityController extends BaseApiController
{
    /**
     * Get authenticated driver's availability schedule.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $driver = DriverApplication::where('user_id', $user->id)->first();
        
        if (!$driver) {
            return $this->error('Driver profile not found.', null, 404);
        }

        $availability = DriverAvailability::where('driver_profile_id', $driver->id)
            ->orderBy('date', 'asc')
            ->get();

        return $this->success($availability, 'Driver availability retrieved successfully.');
    }

    /**
     * Set driver availability.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'date' => 'required|date',
            'is_available' => 'required|boolean',
            'start_time' => 'nullable',
            'end_time' => 'nullable',
            'notes' => 'nullable|string|max:500',
        ]);

        $user = $request->user();
        $driver = DriverApplication::where('user_id', $user->id)->first();
        
        if (!$driver) {
            return $this->error('Driver profile not found.', null, 404);
        }

        $data = $request->all();

        // Check if date already set
        $availability = DriverAvailability::updateOrCreate(
            [
                'driver_profile_id' => $driver->id,
                'date' => $data['date']
            ],
            [
                'uuid' => (string) Str::uuid(),
                'is_available' => $data['is_available'],
                'start_time' => $data['start_time'] ?? null,
                'end_time' => $data['end_time'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]
        );

        return $this->success($availability, 'Availability saved successfully.', 201);
    }

    /**
     * Update driver availability slot.
     */
    public function update(Request $request, string $uuid): JsonResponse
    {
        $request->validate([
            'is_available' => 'sometimes|boolean',
            'start_time' => 'nullable',
            'end_time' => 'nullable',
            'notes' => 'nullable|string|max:500',
        ]);

        $availability = DriverAvailability::where('uuid', $uuid)->first();
        if (!$availability) {
            return $this->error('Availability slot not found.', null, 404);
        }

        $availability->update($request->all());

        return $this->success($availability, 'Availability slot updated successfully.');
    }

    /**
     * Delete driver availability slot.
     */
    public function destroy(string $uuid): JsonResponse
    {
        $availability = DriverAvailability::where('uuid', $uuid)->first();
        if (!$availability) {
            return $this->error('Availability slot not found.', null, 404);
        }

        $availability->delete();

        return $this->success(null, 'Availability slot deleted successfully.');
    }

    /**
     * Public check of driver availability calendar/range.
     */
    public function driverAvailability(Request $request, string $driverUuid): JsonResponse
    {
        $driver = DriverApplication::where('uuid', $driverUuid)->first();
        if (!$driver) {
            return $this->error('Driver not found.', null, 404);
        }

        $query = DriverAvailability::where('driver_profile_id', $driver->id);

        if ($request->filled('from')) {
            $query->where('date', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->where('date', '<=', $request->to);
        }

        $availability = $query->orderBy('date', 'asc')->get();

        return $this->success($availability, 'Driver schedule retrieved.');
    }
}

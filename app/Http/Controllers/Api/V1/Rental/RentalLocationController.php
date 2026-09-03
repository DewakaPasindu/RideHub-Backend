<?php

namespace App\Http\Controllers\Api\V1\Rental;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Resources\RentalLocationResource;
use App\Models\RentalApplication;
use App\Models\RentalLocation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RentalLocationController extends BaseApiController
{
    /**
     * Store live coordinate updates during ACTIVE rental.
     */
    public function ping(Request $request, string $appUuid): JsonResponse
    {
        $app = RentalApplication::where('uuid', $appUuid)->first();

        if (!$app) {
            return $this->error('Rental application not found.', null, 404);
        }

        // Only the customer driving the vehicle can ping location
        if ($app->customer_id !== auth()->id()) {
            return $this->error('Unauthorized to update location for this rental.', null, 403);
        }

        if ($app->status !== 'active') {
            return $this->error('GPS location can only be updated for active rentals.', null, 422);
        }

        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'accuracy' => 'nullable|numeric',
        ]);

        $loc = RentalLocation::create([
            'rental_application_id' => $app->id,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'accuracy' => $request->accuracy,
            'recorded_at' => now(),
        ]);

        return $this->success(
            new RentalLocationResource($loc),
            'GPS coordinates recorded.'
        );
    }

    /**
     * Get latest coordinate update.
     */
    public function latest(string $appUuid): JsonResponse
    {
        $app = RentalApplication::where('uuid', $appUuid)->first();

        if (!$app) {
            return $this->error('Rental application not found.', null, 404);
        }

        if (auth()->user()->cannot('view', $app)) {
            return $this->error('Unauthorized to view this rental location.', null, 403);
        }

        $loc = RentalLocation::where('rental_application_id', $app->id)
            ->latest('recorded_at')
            ->first();

        if (!$loc) {
            return $this->error('No location ping recorded yet.', null, 404);
        }

        return $this->success(
            new RentalLocationResource($loc),
            'Latest location retrieved successfully.'
        );
    }

    /**
     * Get authorized location history trail.
     */
    public function history(string $appUuid): JsonResponse
    {
        $app = RentalApplication::where('uuid', $appUuid)->first();

        if (!$app) {
            return $this->error('Rental application not found.', null, 404);
        }

        if (auth()->user()->cannot('view', $app)) {
            return $this->error('Unauthorized to view this location history.', null, 403);
        }

        $history = RentalLocation::where('rental_application_id', $app->id)
            ->oldest('recorded_at')
            ->get();

        return $this->success(
            RentalLocationResource::collection($history),
            'Location history retrieved successfully.'
        );
    }
}

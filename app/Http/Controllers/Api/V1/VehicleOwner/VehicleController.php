<?php

namespace App\Http\Controllers\Api\V1\VehicleOwner;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\VehicleOwner\StoreVehicleRequest;
use App\Http\Requests\VehicleOwner\UpdateVehicleRequest;
use App\Http\Resources\VehicleResource;
use App\Models\Vehicle;
use App\Services\VehicleOwner\VehicleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VehicleController extends BaseApiController
{
    public function __construct(
        protected VehicleService $vehicleService
    ) {
    }

    /**
     * List all vehicles belonging to the authenticated vehicle owner.
     */
    public function index(Request $request): JsonResponse
    {
        $vehicles = $this->vehicleService->getVehicles(
            $request->user()
        );

        return $this->success(
            VehicleResource::collection($vehicles),
            'Vehicles retrieved successfully.'
        );
    }

    /**
     * Create a new vehicle.
     */
    public function store(
        StoreVehicleRequest $request
    ): JsonResponse {
        $vehicle = $this->vehicleService->create(
            $request->user(),
            $request->validated()
        );

        return $this->success(
            new VehicleResource($vehicle),
            'Vehicle created successfully.',
            201
        );
    }

    /**
     * Display a specific vehicle.
     */
    public function show(
        Request $request,
        Vehicle $vehicle
    ): JsonResponse {
        $vehicle = $this->vehicleService->getVehicle(
            $request->user(),
            $vehicle
        );

        return $this->success(
            new VehicleResource($vehicle),
            'Vehicle retrieved successfully.'
        );
    }

    /**
     * Update a vehicle.
     */
    public function update(
        UpdateVehicleRequest $request,
        Vehicle $vehicle
    ): JsonResponse {
        $vehicle = $this->vehicleService->update(
            $request->user(),
            $vehicle,
            $request->validated()
        );

        return $this->success(
            new VehicleResource($vehicle),
            'Vehicle updated successfully.'
        );
    }

    /**
     * Delete a vehicle.
     */
    public function destroy(
        Request $request,
        Vehicle $vehicle
    ): JsonResponse {
        $this->vehicleService->delete(
            $request->user(),
            $vehicle
        );

        return $this->success(
            null,
            'Vehicle deleted successfully.'
        );
    }
}
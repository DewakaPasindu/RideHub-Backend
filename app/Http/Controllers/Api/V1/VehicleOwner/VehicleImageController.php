<?php

namespace App\Http\Controllers\Api\V1\VehicleOwner;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\VehicleOwner\StoreVehicleImageRequest;
use App\Http\Resources\VehicleImageResource;
use App\Models\Vehicle;
use App\Models\VehicleImage;
use App\Services\VehicleOwner\VehicleImageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VehicleImageController extends BaseApiController
{
    public function __construct(
        private readonly VehicleImageService $vehicleImageService
    ) {
    }

    /**
     * Upload a vehicle image.
     */
    public function store(
        StoreVehicleImageRequest $request,
        Vehicle $vehicle
    ): JsonResponse {
        $image = $this->vehicleImageService->upload(
            $request->user(),
            $vehicle,
            $request->file('image'),
            $request->string('image_type')->toString()
        );

        return $this->success(
            new VehicleImageResource($image),
            'Vehicle image uploaded successfully.',
            201
        );
    }

    /**
     * Get all images for a vehicle.
     */
    public function index(
        Request $request,
        Vehicle $vehicle
    ): JsonResponse {
        $images = $this->vehicleImageService->getImages(
            $request->user(),
            $vehicle
        );

        return $this->success(
            VehicleImageResource::collection($images),
            'Vehicle images retrieved successfully.'
        );
    }

    /**
     * Delete a vehicle image.
     */
    public function destroy(
        Request $request,
        Vehicle $vehicle,
        string $imageUuid
    ): JsonResponse {
        $image = VehicleImage::where('uuid', $imageUuid)->firstOrFail();

        $this->vehicleImageService->delete(
            $request->user(),
            $vehicle,
            $image
        );

        return $this->success(
            null,
            'Vehicle image deleted successfully.'
        );
    }
}
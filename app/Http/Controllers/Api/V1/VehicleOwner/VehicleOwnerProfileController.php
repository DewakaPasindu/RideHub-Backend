<?php

namespace App\Http\Controllers\Api\V1\VehicleOwner;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\VehicleOwner\StoreVehicleOwnerProfileRequest;
use App\Http\Requests\VehicleOwner\UpdateVehicleOwnerProfileRequest;
use App\Http\Resources\VehicleOwnerProfileResource;
use App\Services\VehicleOwner\VehicleOwnerProfileService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class VehicleOwnerProfileController extends BaseApiController
{
    public function __construct(
        protected VehicleOwnerProfileService $profileService
    ) {}

    /**
     * Get the authenticated user's vehicle owner profile.
     */
    public function show(Request $request)
    {
        $profile = $this->profileService->getProfile(
            $request->user()
        );

        if (!$profile) {
            return $this->error(
                'Vehicle owner profile not found.',
                null,
                404
            );
        }

        return $this->success(
            new VehicleOwnerProfileResource($profile),
            'Vehicle owner profile retrieved successfully.'
        );
    }

    /**
     * Create vehicle owner profile.
     */
    public function store(StoreVehicleOwnerProfileRequest $request)
    {
        try {
            $profile = $this->profileService->create(
                $request->user(),
                $request->validated()
            );

            return $this->success(
                new VehicleOwnerProfileResource($profile),
                'Vehicle owner profile created successfully.',
                201
            );
        } catch (ValidationException $e) {
            return $this->error(
                'Unable to create vehicle owner profile.',
                $e->errors(),
                422
            );
        }
    }

    /**
     * Update vehicle owner profile.
     */
    public function update(UpdateVehicleOwnerProfileRequest $request)
    {
        $profile = $this->profileService->getProfile(
            $request->user()
        );

        if (!$profile) {
            return $this->error(
                'Vehicle owner profile not found.',
                null,
                404
            );
        }

        try {
            $profile = $this->profileService->update(
                $request->user(),
                $profile,
                $request->validated()
            );

            return $this->success(
                new VehicleOwnerProfileResource($profile),
                'Vehicle owner profile updated successfully.'
            );
        } catch (ValidationException $e) {
            return $this->error(
                'Unable to update vehicle owner profile.',
                $e->errors(),
                422
            );
        }
    }
}
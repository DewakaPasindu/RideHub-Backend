<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\Admin\ReviewVehicleOwnerRequest;
use App\Http\Resources\VehicleOwnerProfileResource;
use App\Models\VehicleOwnerProfile;
use App\Services\VehicleOwner\VehicleOwnerReviewService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VehicleOwnerReviewController extends BaseApiController
{
    public function __construct(
        protected VehicleOwnerReviewService $reviewService
    ) {
    }

    /**
     * Approve a vehicle owner profile.
     */
    public function approve(
        ReviewVehicleOwnerRequest $request,
        string $uuid
    ): JsonResponse {

        $profile = VehicleOwnerProfile::where(
            'uuid',
            $uuid
        )->firstOrFail();

        $profile = $this->reviewService->review(
            $request->user(),
            $profile,
            'approved',
            $request->validated('admin_notes')
        );

        return $this->success(
            new VehicleOwnerProfileResource($profile),
            'Vehicle owner profile approved successfully.'
        );
    }

    /**
     * Reject a vehicle owner profile.
     */
    public function reject(
        ReviewVehicleOwnerRequest $request,
        string $uuid
    ): JsonResponse {
        $profile = VehicleOwnerProfile::where('uuid', $uuid)->firstOrFail();

        $profile = $this->reviewService->review(
            $request->user(),
            $profile,
            'rejected',
            $request->validated('admin_notes')
        );

        return $this->success(
            new VehicleOwnerProfileResource($profile),
            'Vehicle owner profile rejected successfully.'
        );
    }

    /**
     * Request additional information.
     */
    public function requestMoreInformation(
        ReviewVehicleOwnerRequest $request,
        string $uuid
    ): JsonResponse {
        $profile = VehicleOwnerProfile::where('uuid', $uuid)->firstOrFail();

        $profile = $this->reviewService->review(
            $request->user(),
            $profile,
            'more_info_required',
            $request->validated('admin_notes')
        );

        return $this->success(
            new VehicleOwnerProfileResource($profile),
            'More information requested successfully.'
        );
    }
}
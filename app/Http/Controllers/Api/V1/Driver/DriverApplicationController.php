<?php

namespace App\Http\Controllers\Api\V1\Driver;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\Driver\StoreDriverApplicationRequest;
use App\Http\Requests\Driver\UpdateDriverApplicationRequest;
use App\Http\Resources\DriverApplicationResource;
use App\Services\Driver\DriverApplicationService;

class DriverApplicationController extends BaseApiController
{
    protected DriverApplicationService $driverApplicationService;

    public function __construct(DriverApplicationService $driverApplicationService)
    {
        $this->driverApplicationService = $driverApplicationService;
    }

    /**
     * Submit driver application.
     */
    public function store(StoreDriverApplicationRequest $request)
    {
        $application = $this->driverApplicationService->create(
            $request->validated()
        );

        return $this->success(
            new DriverApplicationResource($application),
            'Driver application submitted successfully.',
            201
        );
    }

    /**
     * Get current user's application.
     */
    public function show()
    {
        $application = $this->driverApplicationService->get();

        if (!$application) {
            return $this->error(
                'Driver application not found.',
                404
            );
        }

        return $this->success(
            new DriverApplicationResource($application),
            'Driver application retrieved successfully.'
        );
    }

    /**
     * Update driver application.
     */
    public function update(UpdateDriverApplicationRequest $request)
    {
        $application = $this->driverApplicationService->update(
            $request->validated()
        );

        return $this->success(
            new DriverApplicationResource($application),
            'Driver application updated successfully.'
        );
    }

    /**
     * Delete driver application.
     */
    public function destroy()
    {
        $this->driverApplicationService->delete();

        return $this->success(
            null,
            'Driver application deleted successfully.'
        );
    }
}
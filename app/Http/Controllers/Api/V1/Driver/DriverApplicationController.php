<?php

namespace App\Http\Controllers\Api\V1\Driver;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Driver\StoreDriverApplicationRequest;
use App\Http\Requests\Driver\UpdateDriverApplicationRequest;
use App\Services\Driver\DriverApplicationService;
use Illuminate\Validation\ValidationException;
use App\Http\Resources\Driver\DriverApplicationResource;


class DriverApplicationController extends Controller
{
    public function __construct(
        private DriverApplicationService $driverApplicationService
    ) {
    }

    /**
     * Submit driver application.
     */
    public function store(StoreDriverApplicationRequest $request)
    {
        try {

            $application = $this->driverApplicationService->create(
                $request->validated()
            );

            return ApiResponse::success(
                new DriverApplicationResource($application),
                'Driver application submitted successfully.',
                201
            );

        } catch (ValidationException $e) {

            return ApiResponse::error(
                'Validation failed.',
                $e->errors(),
                422
            );

        } catch (\Throwable $e) {

            report($e);

            return ApiResponse::error(
                'Unable to submit driver application.',
                null,
                500
            );
        }
    }

    /**
     * Get current user's application.
     */
    public function show()
    {
        try {

            $application = $this->driverApplicationService->get();

            return ApiResponse::success(
                new DriverApplicationResource($application),
                'Driver application retrieved successfully.'
            );

        } catch (\Throwable $e) {

            report($e);

            return ApiResponse::error(
                'Unable to retrieve application.',
                null,
                500
            );
        }
    }

    /**
     * Update application.
     */
    public function update(UpdateDriverApplicationRequest $request)
    {
        try {

            $application = $this->driverApplicationService->update(
                $request->validated()
            );

            return ApiResponse::success(
                new DriverApplicationResource($application),
                'Driver application updated successfully.'
            );

        } catch (ValidationException $e) {

            return ApiResponse::error(
                'Validation failed.',
                $e->errors(),
                422
            );

        } catch (\Throwable $e) {

            report($e);

            return ApiResponse::error(
                'Unable to update application.',
                null,
                500
            );
        }
    }

    /**
     * Delete application.
     */
    public function destroy()
    {
        try {

            $this->driverApplicationService->delete();

            return ApiResponse::success(
                null,
                'Driver application deleted successfully.'
            );

        } catch (ValidationException $e) {

            return ApiResponse::error(
                'Validation failed.',
                $e->errors(),
                422
            );

        } catch (\Throwable $e) {

            report($e);

            return ApiResponse::error(
                'Unable to delete application.',
                null,
                500
            );
        }
    }

}
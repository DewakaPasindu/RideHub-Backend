<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\Driver\DriverApplicationResource;
use App\Services\Admin\AdminDriverApplicationService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AdminDriverApplicationController extends Controller
{
    public function __construct(
        private AdminDriverApplicationService $service
    ) {
    }

    /**
     * List all applications.
     */
    public function index(Request $request)
    {
        try {

            $applications = $this->service->list(
                $request->only([
                    'status',
                    'search',
                ])
            );

            return ApiResponse::success(
                DriverApplicationResource::collection($applications),
                'Driver applications retrieved successfully.'
            );

        } catch (\Throwable $e) {

            report($e);

            return ApiResponse::error(
                'Unable to retrieve applications.',
                null,
                500
            );
        }
    }

    /**
     * View one application.
     */
    public function show(string $uuid)
    {
        try {

            $application = $this->service->show($uuid);

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
     * Approve application.
     */
    public function approve(Request $request, string $uuid)
    {
        try {

            $application = $this->service->approve(
                $uuid,
                $request->input('admin_notes')
            );

            return ApiResponse::success(
                new DriverApplicationResource($application),
                'Driver application approved successfully.'
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
                'Unable to approve application.',
                null,
                500
            );
        }
    }

    /**
     * Reject application.
     */
    public function reject(Request $request, string $uuid)
    {
        $request->validate([
            'admin_notes' => 'required|string|max:1000',
        ]);

        try {

            $application = $this->service->reject(
                $uuid,
                $request->input('admin_notes')
            );

            return ApiResponse::success(
                new DriverApplicationResource($application),
                'Driver application rejected successfully.'
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
                'Unable to reject application.',
                null,
                500
            );
        }
    }

    /**
     * Request more information.
     */
    public function requestMoreInformation(Request $request, string $uuid)
    {
        $request->validate([
            'admin_notes' => 'required|string|max:1000',
        ]);

        try {

            $application = $this->service->requestMoreInformation(
                $uuid,
                $request->input('admin_notes')
            );

            return ApiResponse::success(
                new DriverApplicationResource($application),
                'Additional information requested successfully.'
            );

        } catch (ValidationException $e) {

            return ApiResponse::error(
                'Validation failed.',
                $e->errors(),
                422
            );

        } catch (\Throwable $e) {

        dd(
        $e->getMessage(),
        $e->getFile(),
        $e->getLine()
    );

            report($e);

            return ApiResponse::error(
                'Unable to process request.',
                null,
                500
            );
        }
    }
}
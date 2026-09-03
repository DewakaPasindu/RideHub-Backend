<?php

namespace App\Http\Controllers\Api\V1\VehicleOwner;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\Shared\UploadDocumentRequest;
use App\Http\Resources\DocumentResource;
use App\Models\Vehicle;
use App\Services\Shared\DocumentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class VehicleDocumentController extends BaseApiController
{
    public function __construct(
        protected DocumentService $documentService
    ) {
    }

    /**
     * Upload a document for a vehicle owned by
     * the authenticated vehicle owner.
     */
    public function store(
        UploadDocumentRequest $request,
        Vehicle $vehicle
    ): JsonResponse {
        /*
         * Verify that the authenticated user owns
         * the requested vehicle.
         */
        $profile = $vehicle->vehicleOwnerProfile;

        if (! $profile || $profile->user_id !== $request->user()->id) {
            throw ValidationException::withMessages([
                'vehicle' => [
                    'You are not authorized to upload documents for this vehicle.',
                ],
            ]);
        }

        /*
         * Upload using the shared document service.
         */
        $document = $this->documentService->upload(
            $vehicle,
            $request->file('document'),
            $request->validated('document_type'),
            $request->user()->id
        );

        return $this->success(
            new DocumentResource($document),
            'Vehicle document uploaded successfully.',
            201
        );
    }

    /**
     * List all documents belonging to a vehicle.
     */
    public function index(
        Request $request,
        Vehicle $vehicle
    ): JsonResponse {
        $profile = $vehicle->vehicleOwnerProfile;

        if (! $profile || $profile->user_id !== $request->user()->id) {
            throw ValidationException::withMessages([
                'vehicle' => [
                    'You are not authorized to access documents for this vehicle.',
                ],
            ]);
        }

        $documents = $vehicle->documents()
            ->latest()
            ->get();

        return $this->success(
            DocumentResource::collection($documents),
            'Vehicle documents retrieved successfully.'
        );
    }

    /**
     * Delete a vehicle document.
     */
    public function destroy(
        Request $request,
        Vehicle $vehicle,
        string $documentUuid
    ): JsonResponse {
        $profile = $vehicle->vehicleOwnerProfile;

        if (! $profile || $profile->user_id !== $request->user()->id) {
            throw ValidationException::withMessages([
                'vehicle' => [
                    'You are not authorized to manage documents for this vehicle.',
                ],
            ]);
        }

        $document = $vehicle->documents()
            ->where('uuid', $documentUuid)
            ->firstOrFail();

        $this->documentService->delete($document);

        return $this->success(
            null,
            'Vehicle document deleted successfully.'
        );
    }
}
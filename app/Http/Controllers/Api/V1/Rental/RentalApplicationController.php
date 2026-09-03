<?php

namespace App\Http\Controllers\Api\V1\Rental;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\Rental\StoreRentalApplicationRequest;
use App\Http\Requests\Rental\UpdateRentalApplicationRequest;
use App\Http\Resources\RentalApplicationResource;
use App\Http\Resources\RentalDocumentResource;
use App\Models\RentalApplication;
use App\Models\RentalDocument;
use App\Models\Vehicle;
use App\Services\Rental\RentalNotificationService;
use App\Services\Shared\FileUploadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RentalApplicationController extends BaseApiController
{
    public function __construct(
        protected FileUploadService $fileUploadService
    ) {}

    /**
     * List user's rental applications.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $applications = RentalApplication::with(['vehicle', 'customer'])
            ->where('customer_id', $user->id)
            ->latest()
            ->get();

        return $this->success(
            RentalApplicationResource::collection($applications),
            'Rental applications retrieved successfully.'
        );
    }

    /**
     * Create a new draft application.
     */
    public function store(StoreRentalApplicationRequest $request): JsonResponse
    {
        // Re-check vehicle availability
        $vehicle = Vehicle::findOrFail($request->vehicle_id);
        if ($vehicle->application_status !== 'approved') {
            return $this->error('Selected vehicle is not available for rent.', null, 422);
        }

        $data = $request->validated();
        $data['customer_id'] = $request->user()->id;
        $data['status'] = 'draft';

        $app = RentalApplication::create($data);

        return $this->success(
            new RentalApplicationResource($app),
            'Rental application created in draft successfully.',
            201
        );
    }

    /**
     * Show a rental application details.
     */
    public function show(string $uuid): JsonResponse
    {
        $app = RentalApplication::with(['vehicle', 'customer', 'documents', 'conditions', 'conditions.photos', 'handover'])
            ->where('uuid', $uuid)
            ->first();

        if (!$app) {
            return $this->error('Rental application not found.', null, 404);
        }

        if (auth()->user()->cannot('view', $app)) {
            return $this->error('Unauthorized to view this application.', null, 403);
        }

        return $this->success(
            new RentalApplicationResource($app),
            'Rental application details retrieved successfully.'
        );
    }

    /**
     * Update application in draft or more-info status.
     */
    public function update(UpdateRentalApplicationRequest $request, string $uuid): JsonResponse
    {
        $app = RentalApplication::where('uuid', $uuid)->first();

        if (!$app) {
            return $this->error('Rental application not found.', null, 404);
        }

        if (auth()->user()->cannot('update', $app)) {
            return $this->error('Unauthorized to update this application or state is locked.', null, 403);
        }

        $app->update($request->validated());

        return $this->success(
            new RentalApplicationResource($app->fresh()),
            'Rental application updated successfully.'
        );
    }

    /**
     * Submit application for review.
     */
    public function submit(string $uuid): JsonResponse
    {
        $app = RentalApplication::with(['documents'])->where('uuid', $uuid)->first();

        if (!$app) {
            return $this->error('Rental application not found.', null, 404);
        }

        if ($app->customer_id !== auth()->id()) {
            return $this->error('Unauthorized.', null, 403);
        }

        // Verify required documents are present before submit
        $docs = $app->documents->pluck('document_type')->toArray();
        $requiredTypes = ['id_front', 'id_back', 'driving_license_front', 'driving_license_back', 'customer_live_photo'];
        
        foreach ($requiredTypes as $reqType) {
            if (!in_array($reqType, $docs)) {
                return $this->error("Verification error: Missing {$reqType} upload.", null, 422);
            }
        }

        $app->status = 'submitted';
        $app->submitted_at = now();
        $app->save();

        RentalNotificationService::notify(
            $app,
            'OWNER_RENTAL_REQUEST_RECEIVED',
            'New Rental Request Received',
            "Customer {$app->first_name} has submitted a self-drive application for review."
        );

        return $this->success(
            new RentalApplicationResource($app),
            'Rental application submitted successfully.'
        );
    }

    /**
     * Upload verification document securely to local disk.
     */
    public function uploadDocument(Request $request, string $uuid): JsonResponse
    {
        $app = RentalApplication::where('uuid', $uuid)->first();

        if (!$app) {
            return $this->error('Rental application not found.', null, 404);
        }

        if (auth()->user()->cannot('update', $app)) {
            return $this->error('Unauthorized to edit this application.', null, 403);
        }

        $request->validate([
            'document' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'type' => 'required|string|in:id_front,id_back,driving_license_front,driving_license_back',
        ]);

        $file = $request->file('document');
        
        // Use FileUploadService with 'local' secure disk
        $path = $this->fileUploadService->upload($file, 'secure_rental_documents/' . $app->id, 'local');

        // Upsert document record
        $doc = RentalDocument::updateOrCreate([
            'rental_application_id' => $app->id,
            'document_type' => $request->type
        ], [
            'document_path' => $path,
            'original_filename' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
            'verification_status' => 'pending',
            'uploaded_at' => now(),
        ]);

        return $this->success(
            new RentalDocumentResource($doc),
            'Document uploaded securely.'
        );
    }

    /**
     * Upload live selfie photo securely to local disk.
     */
    public function uploadLivePhoto(Request $request, string $uuid): JsonResponse
    {
        $app = RentalApplication::where('uuid', $uuid)->first();

        if (!$app) {
            return $this->error('Rental application not found.', null, 404);
        }

        if (auth()->user()->cannot('update', $app)) {
            return $this->error('Unauthorized to edit this application.', null, 403);
        }

        $request->validate([
            'photo' => 'required|image|mimes:jpg,jpeg,png|max:5120',
        ]);

        $file = $request->file('photo');
        
        // Use secure local disk
        $path = $this->fileUploadService->upload($file, 'secure_rental_documents/' . $app->id, 'local');

        $doc = RentalDocument::updateOrCreate([
            'rental_application_id' => $app->id,
            'document_type' => 'customer_live_photo'
        ], [
            'document_path' => $path,
            'original_filename' => 'live_selfie.png',
            'mime_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
            'verification_status' => 'pending',
            'uploaded_at' => now(),
        ]);

        return $this->success(
            new RentalDocumentResource($doc),
            'Live selfie photo uploaded securely.'
        );
    }
}

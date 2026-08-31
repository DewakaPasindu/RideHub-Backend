<?php

namespace App\Http\Controllers\Api\V1\Rental;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\Rental\StoreVehicleConditionRequest;
use App\Http\Resources\RentalVehicleConditionResource;
use App\Models\RentalApplication;
use App\Models\RentalVehicleCondition;
use App\Models\RentalConditionPhoto;
use App\Services\Shared\FileUploadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RentalConditionController extends BaseApiController
{
    public function __construct(
        protected FileUploadService $fileUploadService
    ) {}

    /**
     * Get condition records for a rental application.
     */
    public function index(string $appUuid): JsonResponse
    {
        $app = RentalApplication::where('uuid', $appUuid)->first();

        if (!$app) {
            return $this->error('Rental application not found.', null, 404);
        }

        if (auth()->user()->cannot('view', $app)) {
            return $this->error('Unauthorized.', null, 403);
        }

        $conditions = RentalVehicleCondition::with(['photos'])
            ->where('rental_application_id', $app->id)
            ->get();

        return $this->success(
            RentalVehicleConditionResource::collection($conditions),
            'Vehicle conditions retrieved.'
        );
    }

    /**
     * Submit pre-rental or return vehicle condition report.
     */
    public function store(StoreVehicleConditionRequest $request, string $appUuid): JsonResponse
    {
        $app = RentalApplication::where('uuid', $appUuid)->first();

        if (!$app) {
            return $this->error('Rental application not found.', null, 404);
        }

        if (auth()->user()->cannot('ownerAction', $app)) {
            return $this->error('Unauthorized: Only vehicle owner can record condition.', null, 403);
        }

        $stage = $request->get('inspection_stage', 'pre_rental');
        if (!in_array($stage, ['pre_rental', 'return'])) {
            return $this->error('Invalid inspection stage.', null, 422);
        }

        // Verify status matches stage
        if ($stage === 'pre_rental' && !in_array($app->status, ['owner_approved', 'under_review'])) {
            return $this->error('Vehicle condition can only be recorded after owner approval.', null, 422);
        }
        
        if ($stage === 'return' && $app->status !== 'active') {
            return $this->error('Return condition can only be recorded for active rentals.', null, 422);
        }

        $condition = RentalVehicleCondition::create([
            'rental_application_id' => $app->id,
            'recorded_by' => auth()->id(),
            'inspection_stage' => $stage,
            'odometer_reading' => $request->odometer_reading,
            'fuel_level' => $request->fuel_level,
            'exterior_condition' => $request->exterior_condition,
            'interior_condition' => $request->interior_condition,
            'existing_damage' => $request->existing_damage,
            'condition_description' => $request->condition_description,
        ]);

        return $this->success(
            new RentalVehicleConditionResource($condition),
            'Vehicle condition report created successfully.'
        );
    }

    /**
     * Upload photo for a vehicle condition report.
     */
    public function uploadPhoto(Request $request, string $appUuid): JsonResponse
    {
        $app = RentalApplication::where('uuid', $appUuid)->first();

        if (!$app) {
            return $this->error('Rental application not found.', null, 404);
        }

        if (auth()->user()->cannot('ownerAction', $app)) {
            return $this->error('Unauthorized.', null, 403);
        }

        $request->validate([
            'condition_uuid' => 'required|string|exists:rental_vehicle_conditions,uuid',
            'photo' => 'required|image|mimes:jpg,jpeg,png|max:5120',
            'photo_type' => 'required|string|in:front,rear,left,right,interior,odometer,fuel,damage,other',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $condition = RentalVehicleCondition::where('uuid', $request->condition_uuid)->first();
        if (!$condition) {
            return $this->error('Condition report not found.', null, 404);
        }

        $file = $request->file('photo');
        
        // Upload photo to public storage
        $path = $this->fileUploadService->upload($file, 'rental_conditions/' . $app->id, 'public');

        $photo = RentalConditionPhoto::create([
            'condition_id' => $condition->id,
            'photo_type' => $request->photo_type,
            'file_path' => $path,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'captured_at' => now(),
        ]);

        return $this->success(
            $photo,
            'Condition photo uploaded.'
        );
    }

    /**
     * Compare pre-rental vs return conditions.
     */
    public function getComparison(string $appUuid): JsonResponse
    {
        $app = RentalApplication::where('uuid', $appUuid)->first();

        if (!$app) {
            return $this->error('Rental application not found.', null, 404);
        }

        if (auth()->user()->cannot('view', $app)) {
            return $this->error('Unauthorized.', null, 403);
        }

        $pre = RentalVehicleCondition::with(['photos'])
            ->where('rental_application_id', $app->id)
            ->where('inspection_stage', 'pre_rental')
            ->first();

        $return = RentalVehicleCondition::with(['photos'])
            ->where('rental_application_id', $app->id)
            ->where('inspection_stage', 'return')
            ->first();

        return $this->success([
            'pre_rental' => $pre ? new RentalVehicleConditionResource($pre) : null,
            'return' => $return ? new RentalVehicleConditionResource($return) : null,
            'odometer_difference' => ($pre && $return) ? ($return->odometer_reading - $pre->odometer_reading) : 0,
            'fuel_difference' => ($pre && $return) ? ($return->fuel_level - $pre->fuel_level) : 0,
        ], 'Vehicle condition comparison retrieved.');
    }
}

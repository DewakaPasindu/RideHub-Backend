<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RentalVehicleConditionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'rental_application_id' => $this->rental_application_id,
            'recorded_by' => $this->recorded_by,
            'inspection_stage' => $this->inspection_stage,
            'odometer_reading' => $this->odometer_reading,
            'fuel_level' => $this->fuel_level,
            'exterior_condition' => $this->exterior_condition,
            'interior_condition' => $this->interior_condition,
            'existing_damage' => $this->existing_damage,
            'condition_description' => $this->condition_description,
            'created_at' => $this->created_at ? $this->created_at->toIso8601String() : null,
            'photos' => RentalConditionPhotoResource::collection($this->whenLoaded('photos')),
        ];
    }
}

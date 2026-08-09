<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VehicleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,

            'registration_number' => $this->registration_number,

            'make' => $this->make,
            'model' => $this->model,
            'variant' => $this->variant,

            'manufacturing_year' => $this->manufacturing_year,
            'color' => $this->color,

            'vehicle_type' => $this->vehicle_type?->value,
            'fuel_type' => $this->fuel_type?->value,
            'transmission' => $this->transmission?->value,

            'seating_capacity' => $this->seating_capacity,
            'doors' => $this->doors,

            'mileage' => $this->mileage,

            'chassis_number' => $this->chassis_number,
            'engine_number' => $this->engine_number,
            'vin' => $this->vin,

            'has_ac' => $this->has_ac,
            'has_gps' => $this->has_gps,

            'description' => $this->description,

            'application_status' => $this->application_status?->value,

            'verified_at' => $this->verified_at,
            'admin_notes' => $this->admin_notes,

            'vehicle_owner' => $this->whenLoaded(
                'vehicleOwnerProfile',
                function () {
                    return [
                        'uuid' => $this->vehicleOwnerProfile?->uuid,
                        'owner_type' => $this->vehicleOwnerProfile?->owner_type,
                        'business_name' => $this->vehicleOwnerProfile?->business_name,
                    ];
                }
            ),

            'documents' => $this->whenLoaded(
                'documents',
                fn () => DocumentResource::collection($this->documents)
            ),

            'images' => $this->whenLoaded(
                'images',
                fn () => $this->images
            ),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
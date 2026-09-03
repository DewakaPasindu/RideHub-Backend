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
        // Calculate average rating and review count from reviews relationship if loaded
        $avgRating = $this->reviews_avg_rating ?? 5.0;
        $reviewCount = $this->reviews_count ?? 0;

        return [
            'id' => $this->uuid, // Map UUID to 'id' for frontend compatibility
            'uuid' => $this->uuid,
            'owner_id' => $this->vehicleOwnerProfile?->uuid, // Map owner profile UUID to owner_id

            'vehicle_number' => $this->registration_number,
            'registration_number' => $this->registration_number,

            'brand' => $this->make,
            'make' => $this->make,
            'model' => $this->model,
            'variant' => $this->variant,

            'year' => $this->manufacturing_year,
            'manufacturing_year' => $this->manufacturing_year,
            'color' => $this->color,

            'vehicle_type' => $this->vehicle_type?->value ?? $this->vehicle_type,
            'fuel_type' => $this->fuel_type?->value ?? $this->fuel_type,
            'transmission' => $this->transmission?->value ?? $this->transmission,

            'seat_count' => $this->seating_capacity,
            'seating_capacity' => $this->seating_capacity,
            'doors' => $this->doors,

            'mileage' => $this->mileage,

            'chassis_number' => $this->chassis_number,
            'engine_number' => $this->engine_number,
            'vin' => $this->vin,

            'has_ac' => (bool)$this->has_ac,
            'has_gps' => (bool)$this->has_gps,

            'price_per_day' => (double)$this->price_per_day,
            'nearest_town' => $this->nearest_town,
            'location_lat' => $this->location_lat ? (double)$this->location_lat : null,
            'location_lng' => $this->location_lng ? (double)$this->location_lng : null,
            
            'features' => $this->features ?? [],
            'images' => $this->images ?? [],
            
            'description' => $this->description,

            'availability_status' => 'available', // Defaults to available when approved
            'approval_status' => $this->application_status?->value ?? $this->application_status,
            'application_status' => $this->application_status?->value ?? $this->application_status,
            'rejection_reason' => $this->rejection_reason,

            'verified_at' => $this->verified_at,
            'admin_notes' => $this->admin_notes,
            'revenue_license_document' => $this->revenue_license_document ? asset('storage/' . $this->revenue_license_document) : null,
            'insurance_card_document' => $this->insurance_card_document ? asset('storage/' . $this->insurance_card_document) : null,
            
            'owner' => [
                'first_name' => $this->vehicleOwnerProfile?->user?->first_name ?? 'Owner',
                'last_name' => $this->vehicleOwnerProfile?->user?->last_name ?? '',
                'email' => $this->vehicleOwnerProfile?->user?->email ?? '',
                'mobile_number' => $this->vehicleOwnerProfile?->phone ?? $this->vehicleOwnerProfile?->user?->phone ?? '',
            ],

            'avg_rating' => (double)$avgRating,
            'review_count' => (int)$reviewCount,

            'documents' => $this->whenLoaded(
                'documents',
                fn () => DocumentResource::collection($this->documents)
            ),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
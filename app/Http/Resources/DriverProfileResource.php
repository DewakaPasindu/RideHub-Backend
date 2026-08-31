<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class DriverProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $profilePhotoUrl = null;
        if ($this->selfie_photo) {
            $profilePhotoUrl = (strpos($this->selfie_photo, 'http') === 0) 
                ? $this->selfie_photo 
                : asset('storage/' . $this->selfie_photo);
        }

        return [
            'id' => $this->uuid, // Map UUID to 'id' for frontend compatibility
            'uuid' => $this->uuid,
            'user_id' => $this->user?->uuid ?? $this->user_id,

            'license_number' => $this->driving_license_number,
            'driving_license_number' => $this->driving_license_number,
            'years_of_experience' => (int)$this->years_of_experience,
            'experience_years' => (int)$this->years_of_experience,

            'phone' => $this->phone,
            'address' => $this->address,
            'profile_photo' => $profilePhotoUrl,
            'selfie_photo' => $profilePhotoUrl,

            'specialties' => $this->skills ?? [],
            'skills' => $this->skills ?? [],
            'languages' => $this->languages ?? [],
            'license_classes' => $this->license_classes ?? [],
            'vehicle_types' => $this->vehicle_types ?? [],

            'nearest_town' => $this->area?->name ?? 'Colombo',
            'rating' => (double)($this->rating ?? 5.0),
            'review_count' => (int)($this->review_count ?? 0),
            
            'verification_status' => $this->application_status,
            'availability_status' => $this->availability_status ?? 'available',
            'availability' => $this->availability,
            
            'approval_status' => $this->application_status,
            'rejection_reason' => $this->admin_notes,
            'admin_notes' => $this->admin_notes,

            'location_lat' => $this->location_lat ? (double)$this->location_lat : null,
            'location_lng' => $this->location_lng ? (double)$this->location_lng : null,

            'user' => [
                'first_name' => $this->first_name ?? $this->user?->first_name ?? 'Driver',
                'last_name' => $this->last_name ?? $this->user?->last_name ?? '',
                'email' => $this->user?->email ?? '',
                'mobile_number' => $this->phone ?? $this->user?->phone ?? '',
            ],

            'documents' => $this->whenLoaded(
                'documents',
                fn () => DocumentResource::collection($this->documents)
            ),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

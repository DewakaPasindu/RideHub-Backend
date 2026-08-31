<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,

            'gender' => $this->gender,

            'date_of_birth' => optional($this->date_of_birth)->format('Y-m-d'),

            'nic_passport' => $this->nic_passport,

            'emergency_contact_name' => $this->emergency_contact_name,

            'emergency_contact_phone' => $this->emergency_contact_phone,

            'preferred_language' => $this->preferred_language,
            
            'avatar' => $this->avatar
                ? asset('storage/' . $this->avatar)
                : null,

            'profile_completed' => $this->profile_completed,

            'completion_percentage' => $this->completion_percentage,

            'created_at' => optional($this->created_at)->toDateTimeString(),

            'updated_at' => optional($this->updated_at)->toDateTimeString(),
        ];
    }
}
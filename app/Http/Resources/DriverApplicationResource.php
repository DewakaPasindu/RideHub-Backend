<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DriverApplicationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [

            'uuid' => $this->uuid,

            'application_status' => $this->application_status,

            'first_name' => $this->first_name,

            'last_name' => $this->last_name,

            'nic_passport' => $this->nic_passport,

            'date_of_birth' => optional($this->date_of_birth)->format('Y-m-d'),

            'phone' => $this->phone,

            'address' => $this->address,

            'driving_license_number' => $this->driving_license_number,

            'license_expiry_date' => optional($this->license_expiry_date)->format('Y-m-d'),

            'years_of_experience' => $this->years_of_experience,

            'languages' => $this->languages,

            'skills' => $this->skills,

            'availability' => $this->availability,

            'license_document' => $this->license_document,

            'nic_document' => $this->nic_document,

            'selfie_photo' => $this->selfie_photo,

            'admin_notes' => $this->admin_notes,

            'reviewed_at' => optional($this->reviewed_at)->toDateTimeString(),

            'created_at' => optional($this->created_at)->toDateTimeString(),

            'updated_at' => optional($this->updated_at)->toDateTimeString(),

        ];
    }
}
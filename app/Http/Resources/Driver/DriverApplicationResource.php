<?php

namespace App\Http\Resources\Driver;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class DriverApplicationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [

            'id' => $this->id,
            'uuid' => $this->uuid,

            'application_status' => $this->application_status,

            /*
            |--------------------------------------------------------------------------
            | Personal Information
            |--------------------------------------------------------------------------
            */

            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'nic_passport' => $this->nic_passport,
            'date_of_birth' => $this->date_of_birth,
            'gender' => $this->gender,
            'phone' => $this->phone,

            'emergency_contact_name' => $this->emergency_contact_name,
            'emergency_contact_phone' => $this->emergency_contact_phone,

            'address' => $this->address,

            /*
            |--------------------------------------------------------------------------
            | License Information
            |--------------------------------------------------------------------------
            */

            'driving_license_number' => $this->driving_license_number,

            'license_classes' => $this->license_classes,

            'license_expiry_date' => $this->license_expiry_date,

            /*
            |--------------------------------------------------------------------------
            | Driver Information
            |--------------------------------------------------------------------------
            */

            'vehicle_types' => $this->vehicle_types,

            'years_of_experience' => $this->years_of_experience,

            'languages' => $this->languages,

            'skills' => $this->skills,

            'availability' => $this->availability,

            /*
            |--------------------------------------------------------------------------
            | Area
            |--------------------------------------------------------------------------
            */

            'area' => $this->whenLoaded('area', function () {
                return [
                    'id' => $this->area->id,
                    'name' => $this->area->name,
                ];
            }),

            /*
            |--------------------------------------------------------------------------
            | Documents
            |--------------------------------------------------------------------------
            */

            'license_document' => $this->license_document
                ? Storage::url($this->license_document)
                : null,

            'nic_document' => $this->nic_document
                ? Storage::url($this->nic_document)
                : null,

            'selfie_photo' => $this->selfie_photo
                ? Storage::url($this->selfie_photo)
                : null,

            /*
            |--------------------------------------------------------------------------
            | Review
            |--------------------------------------------------------------------------
            */

            'admin_notes' => $this->admin_notes,

            'reviewed_at' => $this->reviewed_at,

            /*
            |--------------------------------------------------------------------------
            | Dates
            |--------------------------------------------------------------------------
            */

            'created_at' => $this->created_at,

            'updated_at' => $this->updated_at,

        ];
    }
}
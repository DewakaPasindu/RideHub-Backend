<?php

namespace App\Http\Requests\Driver;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDriverApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $application = $this->user()->driverApplication;
        return [

            'first_name' => 'sometimes|string|max:100',

            'last_name' => 'sometimes|string|max:100',

            'phone' => [
            'sometimes',
            'string',
            'max:20',
            Rule::unique('driver_applications', 'phone')
                ->ignore($application?->id),
        ],

            'address' => 'sometimes|string|max:500',

            'license_expiry_date' => 'sometimes|date|after:today',

            'years_of_experience' => 'sometimes|integer|min:0|max:60',

            'availability' => 'sometimes|in:full_time,part_time,weekends',

            'languages' => 'sometimes|array',

            'languages.*' => 'string',

            'skills' => 'sometimes|array',

            'skills.*' => 'string',

            'license_classes' => 'sometimes|array',

            'license_classes.*' => 'string',

            'vehicle_types' => 'sometimes|array',

            'vehicle_types.*' => 'string',

            'area_id' => 'sometimes|exists:areas,id',

            'license_document' => 'sometimes|file|mimes:jpg,jpeg,png,pdf|max:5120',

            'nic_document' => 'sometimes|file|mimes:jpg,jpeg,png,pdf|max:5120',

            'selfie_photo' => 'sometimes|image|mimes:jpg,jpeg,png|max:5120',
        ];
    }
}
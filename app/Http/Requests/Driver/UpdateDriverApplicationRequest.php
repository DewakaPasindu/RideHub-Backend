<?php

namespace App\Http\Requests\Driver;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDriverApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [

            'first_name' => 'sometimes|string|max:100',

            'last_name' => 'sometimes|string|max:100',

            'nic_passport' => 'sometimes|string|max:50',

            'date_of_birth' => 'sometimes|date|before:today',

            'phone' => 'sometimes|string|max:20',

            'address' => 'sometimes|string|max:500',

            'driving_license_number' => 'sometimes|string|max:100',

            'license_expiry_date' => 'sometimes|date|after:today',

            'years_of_experience' => 'sometimes|integer|min:0|max:60',

            'languages' => 'sometimes|array',

            'languages.*' => 'string|max:50',

            'skills' => 'sometimes|array',

            'skills.*' => 'string|max:100',

            'availability' => 'sometimes|in:full_time,part_time,weekends',

            'license_document' => 'sometimes|file|mimes:jpg,jpeg,png,pdf|max:5120',

            'nic_document' => 'sometimes|file|mimes:jpg,jpeg,png,pdf|max:5120',

            'selfie_photo' => 'sometimes|image|mimes:jpg,jpeg,png|max:5120',

        ];
    }
}
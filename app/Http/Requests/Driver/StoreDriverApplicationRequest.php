<?php

namespace App\Http\Requests\Driver;

use Illuminate\Foundation\Http\FormRequest;

class StoreDriverApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [

            'first_name' => 'required|string|max:100',

            'last_name' => 'required|string|max:100',

            'nic_passport' => 'required|string|max:50',

            'date_of_birth' => 'required|date|before:today',

            'phone' => 'required|string|max:20',

            'address' => 'required|string|max:500',

            'driving_license_number' => 'required|string|max:100',

            'license_expiry_date' => 'required|date|after:today',

            'years_of_experience' => 'required|integer|min:0|max:60',

            'languages' => 'nullable|array',

            'languages.*' => 'string|max:50',

            'skills' => 'nullable|array',

            'skills.*' => 'string|max:100',

            'availability' => 'required|in:full_time,part_time,weekends',

            'license_document' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',

            'nic_document' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',

            'selfie_photo' => 'required|image|mimes:jpg,jpeg,png|max:5120',

        ];
    }
}
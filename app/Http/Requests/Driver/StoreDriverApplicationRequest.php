<?php

namespace App\Http\Requests\Driver;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreDriverApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [

            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',

            'nic_passport' => 'required|string|max:50|unique:driver_applications,nic_passport',

            'date_of_birth' => 'required|date|before:today',

            'gender' => 'required|in:male,female,other',

            'phone' => 'required|string|max:20|unique:driver_applications,phone',

            'emergency_contact_name' => 'nullable|string|max:100',

            'emergency_contact_phone' => 'nullable|string|max:20',

            'address' => 'required|string|max:500',

            'driving_license_number' => 'required|string|max:100|unique:driver_applications,driving_license_number',

            'license_classes' => 'nullable|array',

            'license_classes.*' => 'string',

            'license_expiry_date' => 'required|date|after:today',

            'vehicle_types' => 'nullable|array',

            'vehicle_types.*' => 'string',

            'years_of_experience' => 'required|integer|min:0|max:60',

            'languages' => 'nullable|array',

            'languages.*' => 'string',

            'skills' => 'nullable|array',

            'skills.*' => 'string',

            'area_id' => 'nullable|exists:areas,id',

            'availability' => 'required|in:full_time,part_time,weekends',

            'license_document' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',

            'nic_document' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',

            'selfie_photo' => 'required|image|mimes:jpg,jpeg,png|max:5120',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422)
        );
    }

    protected function prepareForValidation()
    {
        logger()->info('StoreDriverApplicationRequest reached', $this->all());
    }
}
<?php

namespace App\Http\Requests\Rental;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRentalApplicationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'first_name' => 'sometimes|string|max:100',
            'last_name' => 'sometimes|string|max:100',
            'phone' => 'sometimes|string|max:30',
            'email' => 'sometimes|email|max:150',
            'address' => 'sometimes|string|max:500',
            'id_type' => 'sometimes|string|in:nic,passport',
            'id_number' => 'sometimes|string|max:50',
            'driving_license_number' => 'sometimes|string|max:100',
            'license_expiry_date' => 'sometimes|date|after:today',
            'pickup_address' => 'sometimes|string',
            'pickup_latitude' => 'sometimes|numeric',
            'pickup_longitude' => 'sometimes|numeric',
            'return_address' => 'sometimes|string',
            'return_latitude' => 'sometimes|numeric',
            'return_longitude' => 'sometimes|numeric',
            'start_at' => 'sometimes|date|after_or_equal:today',
            'end_at' => 'sometimes|date|after:start_at',
            'passenger_count' => 'sometimes|integer|min:1|max:20',
            'luggage_requirement' => 'sometimes|string|in:light,medium,heavy',
            'rental_purpose' => 'nullable|string|max:255',
            'additional_requirements' => 'nullable|string',
        ];
    }
}

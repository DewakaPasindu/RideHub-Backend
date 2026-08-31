<?php

namespace App\Http\Requests\Rental;

use Illuminate\Foundation\Http\FormRequest;

class StoreRentalApplicationRequest extends FormRequest
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
            'vehicle_id' => 'required|exists:vehicles,id',
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'phone' => 'required|string|max:30',
            'email' => 'required|email|max:150',
            'address' => 'required|string|max:500',
            'id_type' => 'required|string|in:nic,passport',
            'id_number' => 'required|string|max:50',
            'driving_license_number' => 'required|string|max:100',
            'license_expiry_date' => 'required|date|after:today',
            'pickup_address' => 'required|string',
            'pickup_latitude' => 'required|numeric',
            'pickup_longitude' => 'required|numeric',
            'return_address' => 'required|string',
            'return_latitude' => 'required|numeric',
            'return_longitude' => 'required|numeric',
            'start_at' => 'required|date|after_or_equal:today',
            'end_at' => 'required|date|after:start_at',
            'passenger_count' => 'required|integer|min:1|max:20',
            'luggage_requirement' => 'required|string|in:light,medium,heavy',
            'rental_purpose' => 'nullable|string|max:255',
            'additional_requirements' => 'nullable|string',
        ];
    }
}

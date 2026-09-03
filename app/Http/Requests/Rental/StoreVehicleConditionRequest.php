<?php

namespace App\Http\Requests\Rental;

use Illuminate\Foundation\Http\FormRequest;

class StoreVehicleConditionRequest extends FormRequest
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
            'odometer_reading' => 'required|integer|min:0',
            'fuel_level' => 'required|integer|min:0|max:100',
            'exterior_condition' => 'nullable|string',
            'interior_condition' => 'nullable|string',
            'existing_damage' => 'nullable|array',
            'condition_description' => 'nullable|string',
        ];
    }
}

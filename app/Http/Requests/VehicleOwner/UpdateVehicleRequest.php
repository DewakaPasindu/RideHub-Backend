<?php

namespace App\Http\Requests\VehicleOwner;

use App\Core\Enums\FuelType;
use App\Core\Enums\TransmissionType;
use App\Core\Enums\VehicleType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateVehicleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $vehicle = $this->route('vehicle');

        return [
            'registration_number' => [
                'sometimes',
                'required',
                'string',
                'max:30',
                Rule::unique('vehicles', 'registration_number')
                    ->ignore($vehicle?->id),
            ],

            'make' => [
                'sometimes',
                'required',
                'string',
                'max:100',
            ],

            'model' => [
                'sometimes',
                'required',
                'string',
                'max:100',
            ],

            'variant' => [
                'nullable',
                'string',
                'max:100',
            ],

            'manufacturing_year' => [
                'sometimes',
                'required',
                'integer',
                'min:1900',
                'max:' . date('Y'),
            ],

            'color' => [
                'sometimes',
                'required',
                'string',
                'max:50',
            ],

            'vehicle_type' => [
                'sometimes',
                'required',
                Rule::in(array_column(VehicleType::cases(), 'value')),
            ],

            'fuel_type' => [
                'sometimes',
                'required',
                Rule::in(array_column(FuelType::cases(), 'value')),
            ],

            'transmission' => [
                'sometimes',
                'required',
                Rule::in(array_column(TransmissionType::cases(), 'value')),
            ],

            'seating_capacity' => [
                'sometimes',
                'required',
                'integer',
                'min:1',
                'max:100',
            ],

            'doors' => [
                'nullable',
                'integer',
                'min:1',
                'max:20',
            ],

            'mileage' => [
                'sometimes',
                'integer',
                'min:0',
            ],

            'chassis_number' => [
                'sometimes',
                'required',
                'string',
                'max:100',
                Rule::unique('vehicles', 'chassis_number')
                    ->ignore($vehicle?->id),
            ],

            'engine_number' => [
                'sometimes',
                'required',
                'string',
                'max:100',
                Rule::unique('vehicles', 'engine_number')
                    ->ignore($vehicle?->id),
            ],

            'vin' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('vehicles', 'vin')
                    ->ignore($vehicle?->id),
            ],

            'has_ac' => [
                'sometimes',
                'boolean',
            ],

            'has_gps' => [
                'sometimes',
                'boolean',
            ],

            'description' => [
                'nullable',
                'string',
                'max:5000',
            ],
        ];
    }
}
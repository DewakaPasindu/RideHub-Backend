<?php

namespace App\Http\Requests\VehicleOwner;

use App\Core\Enums\FuelType;
use App\Core\Enums\TransmissionType;
use App\Core\Enums\VehicleType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVehicleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            /*
            |--------------------------------------------------------------------------
            | Basic Vehicle Information
            |--------------------------------------------------------------------------
            */

            'registration_number' => [
                'required',
                'string',
                'max:50',
                'unique:vehicles,registration_number',
            ],

            'make' => [
                'required',
                'string',
                'max:100',
            ],

            'model' => [
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
                'required',
                'integer',
                'min:1900',
                'max:' . date('Y'),
            ],

            'color' => [
                'required',
                'string',
                'max:50',
            ],

            /*
            |--------------------------------------------------------------------------
            | Vehicle Classification
            |--------------------------------------------------------------------------
            */

            'vehicle_type' => [
                'required',
                Rule::in(
                    array_column(VehicleType::cases(), 'value')
                ),
            ],

            'fuel_type' => [
                'required',
                Rule::in(
                    array_column(FuelType::cases(), 'value')
                ),
            ],

            'transmission' => [
                'required',
                Rule::in(
                    array_column(TransmissionType::cases(), 'value')
                ),
            ],

            /*
            |--------------------------------------------------------------------------
            | Capacity
            |--------------------------------------------------------------------------
            */

            'seating_capacity' => [
                'required',
                'integer',
                'min:1',
                'max:100',
            ],

            'doors' => [
                'required',
                'integer',
                'min:1',
                'max:10',
            ],

            /*
            |--------------------------------------------------------------------------
            | Vehicle Condition / Identification
            |--------------------------------------------------------------------------
            */

            'mileage' => [
                'nullable',
                'integer',
                'min:0',
            ],

            'chassis_number' => [
                'required',
                'string',
                'max:100',
                'unique:vehicles,chassis_number',
            ],

            'engine_number' => [
                'required',
                'string',
                'max:100',
                'unique:vehicles,engine_number',
            ],

            'vin' => [
                'nullable',
                'string',
                'max:100',
                'unique:vehicles,vin',
            ],

            /*
            |--------------------------------------------------------------------------
            | Features & Specifications
            |--------------------------------------------------------------------------
            */

            'has_ac' => [
                'boolean',
            ],

            'has_gps' => [
                'boolean',
            ],

            'description' => [
                'nullable',
                'string',
                'max:2000',
            ],

            'price_per_day' => [
                'required',
                'numeric',
                'min:0',
            ],

            'nearest_town' => [
                'nullable',
                'string',
                'max:100',
            ],

            'location_lat' => [
                'nullable',
                'numeric',
                'between:-90,90',
            ],

            'location_lng' => [
                'nullable',
                'numeric',
                'between:-180,180',
            ],

            'features' => [
                'nullable',
                'array',
            ],

            'images' => [
                'nullable',
                'array',
            ],
            
            'available_from' => [
                'nullable',
                'date',
            ],
            
            'available_to' => [
                'nullable',
                'date',
                'after_or_equal:available_from',
            ],
        ];
    }
}
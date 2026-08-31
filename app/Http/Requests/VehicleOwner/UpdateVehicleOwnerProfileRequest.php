<?php

namespace App\Http\Requests\VehicleOwner;

use App\Core\Enums\OwnerType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateVehicleOwnerProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'owner_type' => [
                'sometimes',
                'required',
                Rule::in(array_column(OwnerType::cases(), 'value')),
            ],

            'business_name' => [
                'nullable',
                'string',
                'max:255',
            ],

            'nic_passport' => [
                'sometimes',
                'required',
                'string',
                'max:50',
            ],

            'phone' => [
                'sometimes',
                'required',
                'string',
                'max:20',
            ],

            'address' => [
                'sometimes',
                'required',
                'string',
                'max:1000',
            ],

            'country_id' => [
                'nullable',
                'exists:countries,id',
            ],

            'province_id' => [
                'nullable',
                'exists:provinces,id',
            ],

            'district_id' => [
                'nullable',
                'exists:districts,id',
            ],

            'city_id' => [
                'nullable',
                'exists:cities,id',
            ],

            'area_id' => [
                'nullable',
                'exists:areas,id',
            ],
        ];
    }
}
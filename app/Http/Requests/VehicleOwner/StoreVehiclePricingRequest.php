<?php

namespace App\Http\Requests\VehicleOwner;

use App\Core\Enums\CurrencyType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVehiclePricingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'base_price' => [
                'required',
                'numeric',
                'min:0',
            ],

            'price_per_hour' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'price_per_day' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'price_per_km' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'currency' => [
                'required',
                'string',
                Rule::enum(CurrencyType::class),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'base_price.required' =>
                'The base price is required.',

            'base_price.numeric' =>
                'The base price must be a valid number.',

            'base_price.min' =>
                'The base price cannot be negative.',

            'currency.required' =>
                'The currency is required.',

            'currency.enum' =>
                'The selected currency is invalid.',
        ];
    }
}
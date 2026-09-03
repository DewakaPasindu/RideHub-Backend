<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ReviewVehicleOwnerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'admin_notes' => [
                'nullable',
                'string',
                'max:5000',
            ],
        ];
    }
}
<?php

namespace App\Http\Requests\Admin;

use App\Core\Enums\ApplicationStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReviewVehicleOwnerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'application_status' => [
                'required',
                Rule::in([
                    ApplicationStatus::APPROVED->value,
                    ApplicationStatus::REJECTED->value,
                    ApplicationStatus::MORE_INFORMATION_REQUIRED->value,
                ]),
            ],

            'admin_notes' => [
                'nullable',
                'string',
                'max:5000',
            ],
        ];
    }
}
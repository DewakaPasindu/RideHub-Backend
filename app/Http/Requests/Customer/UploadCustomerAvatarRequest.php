<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;

class UploadCustomerAvatarRequest extends FormRequest
{
    /**
     * Determine if the user is authorized.
     */
    public function authorize(): bool
    {
         return true;
            }

    /**
     * Validation rules.
     */
    public function rules(): array
    {
        return [

            'avatar' => [

                'required',

                'image',

                'mimes:jpg,jpeg,png,webp',

                'max:2048',

            ],

        ];
    }
}
<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VehicleOwnerProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,

            'owner_type' => $this->owner_type,

            'business_name' => $this->business_name,

            'nic_passport' => $this->nic_passport,

            'phone' => $this->phone,

            'address' => $this->address,

            'application_status' => $this->application_status,

            'verified_at' => $this->verified_at,

            'admin_notes' => $this->admin_notes,

            'user' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'uuid' => $this->user->uuid,
                    'first_name' => $this->user->first_name,
                    'last_name' => $this->user->last_name,
                    'email' => $this->user->email,
                ];
            }),

            'location' => [
                'country' => $this->whenLoaded('country', function () {
                    return [
                        'id' => $this->country->id,
                        'name' => $this->country->name,
                    ];
                }),

                'province' => $this->whenLoaded('province', function () {
                    return [
                        'id' => $this->province->id,
                        'name' => $this->province->name,
                    ];
                }),

                'district' => $this->whenLoaded('district', function () {
                    return [
                        'id' => $this->district->id,
                        'name' => $this->district->name,
                    ];
                }),

                'city' => $this->whenLoaded('city', function () {
                    return [
                        'id' => $this->city->id,
                        'name' => $this->city->name,
                    ];
                }),

                'area' => $this->whenLoaded('area', function () {
                    return [
                        'id' => $this->area->id,
                        'name' => $this->area->name,
                    ];
                }),
            ],

            'created_at' => $this->created_at,

            'updated_at' => $this->updated_at,
        ];
    }
}
<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RentalHandoverResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'rental_application_id' => $this->rental_application_id,
            'status' => $this->status,
            'customer_confirmed_at' => $this->customer_confirmed_at ? $this->customer_confirmed_at->toIso8601String() : null,
            'owner_confirmed_at' => $this->owner_confirmed_at ? $this->owner_confirmed_at->toIso8601String() : null,
            'handover_at' => $this->handover_at ? $this->handover_at->toIso8601String() : null,
            'handover_latitude' => $this->handover_latitude,
            'handover_longitude' => $this->handover_longitude,
            'created_at' => $this->created_at ? $this->created_at->toIso8601String() : null,
        ];
    }
}

<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RentalConditionPhotoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'condition_id' => $this->condition_id,
            'photo_type' => $this->photo_type,
            'file_path' => $this->file_path,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'captured_at' => $this->captured_at ? $this->captured_at->toIso8601String() : null,
            'url' => asset('storage/' . $this->file_path),
        ];
    }
}

<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->uuid, // Map UUID to 'id'
            'uuid' => $this->uuid,
            'user_id' => $this->user?->uuid ?? $this->user_id,
            'booking_id' => $this->booking?->uuid ?? $this->booking_id,
            'target_type' => $this->target_type,
            'vehicle_id' => $this->vehicle?->uuid ?? $this->vehicle_id,
            'driver_profile_id' => $this->driverProfile?->uuid ?? $this->driver_profile_id,
            'target_name' => $this->target_type === 'vehicle' 
                ? ($this->vehicle ? trim($this->vehicle->make . ' ' . $this->vehicle->model) : 'Vehicle')
                : ($this->driverProfile ? trim($this->driverProfile->first_name . ' ' . $this->driverProfile->last_name) : 'Driver'),
            'rating' => (int)$this->rating,
            'comment' => $this->comment,
            'status' => $this->status,
            'moderation_note' => $this->moderation_note,
            'moderated_at' => $this->moderated_at ? $this->moderated_at->toDateTimeString() : null,
            'moderated_by' => $this->moderator?->uuid,
            
            'user' => [
                'first_name' => $this->user?->first_name ?? 'User',
                'last_name' => $this->user?->last_name ?? '',
                'profile_photo' => $this->user?->avatar ? asset('storage/' . $this->user->avatar) : null,
            ],
            
            'created_at' => $this->created_at ? $this->created_at->toDateTimeString() : null,
            'updated_at' => $this->updated_at ? $this->updated_at->toDateTimeString() : null,
        ];
    }
}

<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RentalApplicationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'booking_id' => $this->booking_id,
            'customer_id' => $this->customer_id,
            'vehicle_id' => $this->vehicle_id,
            'status' => $this->status,
            
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'phone' => $this->phone,
            'email' => $this->email,
            'address' => $this->address,
            'id_type' => $this->id_type,
            'id_number' => $this->id_number,
            'driving_license_number' => $this->driving_license_number,
            'license_expiry_date' => $this->license_expiry_date ? $this->license_expiry_date->format('Y-m-d') : null,
            
            'pickup_address' => $this->pickup_address,
            'pickup_latitude' => $this->pickup_latitude,
            'pickup_longitude' => $this->pickup_longitude,
            'return_address' => $this->return_address,
            'return_latitude' => $this->return_latitude,
            'return_longitude' => $this->return_longitude,
            
            'start_at' => $this->start_at ? $this->start_at->toIso8601String() : null,
            'end_at' => $this->end_at ? $this->end_at->toIso8601String() : null,
            'passenger_count' => $this->passenger_count,
            'luggage_requirement' => $this->luggage_requirement,
            'rental_purpose' => $this->rental_purpose,
            'additional_requirements' => $this->additional_requirements,
            'more_info_reason' => $this->more_info_reason,
            
            'submitted_at' => $this->submitted_at ? $this->submitted_at->toIso8601String() : null,
            'approved_at' => $this->approved_at ? $this->approved_at->toIso8601String() : null,
            'rejected_at' => $this->rejected_at ? $this->rejected_at->toIso8601String() : null,
            'created_at' => $this->created_at ? $this->created_at->toIso8601String() : null,
            
            'vehicle' => new VehicleResource($this->whenLoaded('vehicle')),
            'customer' => new UserResource($this->whenLoaded('customer')),
            'documents' => RentalDocumentResource::collection($this->whenLoaded('documents')),
            'conditions' => RentalVehicleConditionResource::collection($this->whenLoaded('conditions')),
            'handover' => new RentalHandoverResource($this->whenLoaded('handover')),
        ];
    }
}

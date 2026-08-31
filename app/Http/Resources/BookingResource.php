<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->uuid, // Map UUID to 'id' for frontend compatibility
            'uuid' => $this->uuid,
            'user_id' => $this->user?->uuid ?? $this->user_id,
            'booking_type' => $this->booking_type,
            'vehicle_id' => $this->vehicle?->uuid,
            'driver_profile_id' => $this->driverProfile?->uuid,
            'driver_assigned_id' => $this->driverAssigned?->uuid,
            'target_name' => $this->target_name,
            'start_date' => optional($this->start_date)->format('Y-m-d'),
            'end_date' => optional($this->end_date)->format('Y-m-d'),
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'pickup_location' => $this->pickup_location,
            'dropoff_location' => $this->dropoff_location,
            'pickup_lat' => $this->pickup_lat ? (double)$this->pickup_lat : null,
            'pickup_lng' => $this->pickup_lng ? (double)$this->pickup_lng : null,
            'dropoff_lat' => $this->dropoff_lat ? (double)$this->dropoff_lat : null,
            'dropoff_lng' => $this->dropoff_lng ? (double)$this->dropoff_lng : null,
            'passenger_count' => (int)$this->passenger_count,
            'ac_preference' => $this->ac_preference,
            'total_amount' => (double)$this->total_amount,
            'advance_amount' => (double)$this->advance_amount,
            'payment_receipt_url' => $this->payment_receipt_url,
            'payment_method' => $this->payment_method,
            'status' => $this->status,
            'notes' => $this->notes,
            'rejection_reason' => $this->rejection_reason,
            'trip_started_at' => $this->trip_started_at ? $this->trip_started_at->toDateTimeString() : null,
            'trip_ended_at' => $this->trip_ended_at ? $this->trip_ended_at->toDateTimeString() : null,
            'approved_at' => $this->approved_at ? $this->approved_at->toDateTimeString() : null,
            'approved_by' => $this->approvedBy?->uuid,
            'rejected_at' => $this->rejected_at ? $this->rejected_at->toDateTimeString() : null,
            'cancelled_at' => $this->cancelled_at ? $this->cancelled_at->toDateTimeString() : null,
            
            'user' => [
                'first_name' => $this->user?->first_name ?? 'Customer',
                'last_name' => $this->user?->last_name ?? '',
                'email' => $this->user?->email ?? '',
                'mobile_number' => $this->user?->phone ?? '',
            ],

            'vehicle' => $this->vehicle ? [
                'brand' => $this->vehicle->make,
                'model' => $this->vehicle->model,
                'vehicle_number' => $this->vehicle->registration_number,
                'uuid' => $this->vehicle->uuid,
            ] : null,

            'driver_profile' => $this->driverProfile ? [
                'license_number' => $this->driverProfile->driving_license_number,
                'uuid' => $this->driverProfile->uuid,
                'user' => [
                    'first_name' => $this->driverProfile->first_name ?? '',
                    'last_name' => $this->driverProfile->last_name ?? '',
                ]
            ] : null,
            
            'created_at' => $this->created_at ? $this->created_at->toDateTimeString() : null,
            'updated_at' => $this->updated_at ? $this->updated_at->toDateTimeString() : null,
        ];
    }
}

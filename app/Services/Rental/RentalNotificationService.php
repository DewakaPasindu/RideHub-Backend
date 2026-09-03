<?php

namespace App\Services\Rental;

use App\Models\Notification;
use App\Models\RentalApplication;

class RentalNotificationService
{
    /**
     * Send state change notification.
     */
    public static function notify(RentalApplication $app, string $type, string $title, string $message): void
    {
        // 1. Notify Customer
        Notification::create([
            'user_id' => $app->customer_id,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => [
                'rental_application_uuid' => $app->uuid,
                'status' => $app->status,
                'vehicle' => $app->vehicle ? ($app->vehicle->make . ' ' . $app->vehicle->model) : null,
            ]
        ]);

        // 2. Notify Owner
        $ownerUserId = $app->vehicle?->vehicleOwnerProfile?->user_id;
        if ($ownerUserId) {
            Notification::create([
                'user_id' => $ownerUserId,
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'data' => [
                    'rental_application_uuid' => $app->uuid,
                    'status' => $app->status,
                    'customer' => $app->first_name . ' ' . $app->last_name,
                ]
            ]);
        }
    }
}

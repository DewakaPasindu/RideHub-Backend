<?php

namespace App\Http\Controllers\Api\V1\Notification;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Resources\NotificationResource;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends BaseApiController
{
    /**
     * Get user's notifications.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $notifications = Notification::where('user_id', $user->id)
            ->latest()
            ->paginate($request->get('per_page', 20));

        return $this->success(
            NotificationResource::collection($notifications),
            'Notifications retrieved successfully.'
        );
    }

    /**
     * Mark single notification as read.
     */
    public function markAsRead(string $uuid): JsonResponse
    {
        $notification = Notification::where('uuid', $uuid)
            ->where('user_id', auth()->id())
            ->first();

        if (!$notification) {
            return $this->error('Notification not found.', null, 404);
        }

        $notification->update(['read_at' => now()]);

        return $this->success(new NotificationResource($notification), 'Notification marked as read.');
    }

    /**
     * Mark all user's notifications as read.
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        $user = $request->user();
        Notification::where('user_id', $user->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return $this->success(null, 'All notifications marked as read.');
    }
}

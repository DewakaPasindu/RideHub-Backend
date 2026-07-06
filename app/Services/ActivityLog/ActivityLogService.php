<?php

namespace App\Services;

use App\Enums\ActivityAction;
use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class ActivityLogService
{
    public function __construct(
        protected Request $request
    ) {}

    /**
     * Record an activity log.
     */
    public function log(
        ActivityAction $action,
        ?string $description = null,
        ?Model $subject = null,
        array $properties = [],
        ?int $userId = null
    ): ActivityLog {
        return ActivityLog::create([
            'user_id' => $userId ?? Auth::id(),

            'action' => $action->value,

            'description' => $description,

            'subject_type' => $subject?->getMorphClass(),

            'subject_id' => $subject?->getKey(),

            'ip_address' => $this->request->ip(),

            'user_agent' => $this->request->userAgent(),

            'properties' => $properties,

            'created_at' => now(),
        ]);
    }
}
<?php

namespace App\Services\VehicleOwner;

use App\Core\Enums\ApplicationStatus;
use App\Models\User;
use App\Models\VehicleOwnerProfile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class VehicleOwnerReviewService
{
    /**
     * Review a vehicle owner profile.
     */
    public function review(
        User $admin,
        VehicleOwnerProfile $profile,
        string $status,
        ?string $adminNotes = null
    ): VehicleOwnerProfile {
        return DB::transaction(function () use (
            $admin,
            $profile,
            $status,
            $adminNotes
        ) {

            /*
             * Only these statuses are valid for an admin review.
             */
            $allowedStatuses = [
                ApplicationStatus::APPROVED->value,
                ApplicationStatus::REJECTED->value,
                ApplicationStatus::MORE_INFORMATION_REQUIRED->value,
            ];

            if (! in_array($status, $allowedStatuses, true)) {
                throw ValidationException::withMessages([
                    'application_status' => [
                        'Invalid vehicle owner review status.',
                    ],
                ]);
            }

            /*
             * Prevent reviewing an already approved profile.
             */
            if (
                $profile->application_status ===
                ApplicationStatus::APPROVED->value
            ) {
                throw ValidationException::withMessages([
                    'profile' => [
                        'This vehicle owner profile has already been approved.',
                    ],
                ]);
            }

            /*
             * Update review information.
             */
            $profile->application_status = $status;
            $profile->admin_notes = $adminNotes;
            $profile->verified_by = $admin->id;

            /*
             * verified_at represents the time the admin
             * completed the review.
             */
            $profile->verified_at = now();

            $profile->save();

            return $profile->fresh([
                'user',
                'country',
                'province',
                'district',
                'city',
                'area',
                'reviewer',
            ]);
        });
    }
}
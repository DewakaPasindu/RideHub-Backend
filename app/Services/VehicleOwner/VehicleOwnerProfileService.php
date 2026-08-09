<?php

namespace App\Services\VehicleOwner;

use App\Core\Enums\ApplicationStatus;
use App\Models\User;
use App\Models\VehicleOwnerProfile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class VehicleOwnerProfileService
{
    /**
     * Create a vehicle owner profile for the authenticated user.
     */
    public function create(User $user, array $data): VehicleOwnerProfile
    {
        return DB::transaction(function () use ($user, $data) {

            $existingProfile = VehicleOwnerProfile::where(
                'user_id',
                $user->id
            )->first();

            if ($existingProfile) {
                throw ValidationException::withMessages([
                    'profile' => [
                        'Vehicle owner profile already exists for this user.',
                    ],
                ]);
            }

            $data['user_id'] = $user->id;

            $data['application_status'] =
                ApplicationStatus::DRAFT->value;

            return VehicleOwnerProfile::create($data);
        });
    }

    /**
     * Get the authenticated user's vehicle owner profile.
     */
    public function getProfile(User $user): ?VehicleOwnerProfile
    {
        return VehicleOwnerProfile::with([
            'user',
            'country',
            'province',
            'district',
            'city',
            'area',
            'reviewer',
        ])
        ->where('user_id', $user->id)
        ->first();
    }

    /**
     * Update the authenticated user's vehicle owner profile.
     */
    public function update(
        User $user,
        VehicleOwnerProfile $profile,
        array $data
    ): VehicleOwnerProfile {
        return DB::transaction(function () use (
            $user,
            $profile,
            $data
        ) {

            if ($profile->user_id !== $user->id) {
                throw ValidationException::withMessages([
                    'profile' => [
                        'You are not authorized to update this profile.',
                    ],
                ]);
            }

            /*
             * Users cannot modify administrative fields.
             */
            unset(
                $data['application_status'],
                $data['verified_at'],
                $data['verified_by'],
                $data['admin_notes']
            );

            /*
             * If an approved profile is edited,
             * return it to draft for re-review.
             */
            if (
                $profile->application_status ===
                ApplicationStatus::APPROVED->value
            ) {
                $profile->application_status =
                    ApplicationStatus::DRAFT->value;
            }

            $profile->fill($data);
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
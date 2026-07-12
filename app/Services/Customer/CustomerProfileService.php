<?php

namespace App\Services\Customer;

use App\Models\CustomerProfile;
use App\Services\ActivityLog\ActivityLogService;
use App\Services\Shared\FileUploadService;
use App\Core\Enums\ActivityAction;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use illuminate\Http\Request;

class CustomerProfileService
{
    public function __construct(
        private readonly FileUploadService $fileUploadService,
        private readonly ActivityLogService $activityLogService,
    ) {}

    /**
     * Get authenticated user's profile.
     */
    public function get(): ?CustomerProfile
    {
        return Auth::user()->customerProfile;
    }

    /**
     * Update customer profile.
     */
    public function update(array $data): CustomerProfile
    {
        $profile = Auth::user()->customerProfile;

        if (!$profile) {
            throw ValidationException::withMessages([
                'profile' => ['Customer profile not found.'],
            ]);
        }

        $profile->update([
            'gender' => $data['gender'] ?? $profile->gender,
            'date_of_birth' => $data['date_of_birth'] ?? $profile->date_of_birth,
            'nic_passport' => $data['nic_passport'] ?? $profile->nic_passport,
            'emergency_contact_name' => $data['emergency_contact_name'] ?? $profile->emergency_contact_name,
            'emergency_contact_phone' => $data['emergency_contact_phone'] ?? $profile->emergency_contact_phone,
            'preferred_language' => $data['preferred_language'] ?? $profile->preferred_language,
        ]);

        $profile->profile_completed = $this->calculateProfileCompletion($profile) === 100;
        $profile->save();

        // Activity Log
        $this->activityLogService->log(
            action: ActivityAction::PROFILE_UPDATED,
            description: 'Customer profile updated.',
            subject: $profile,
            properties: [
                'updated_fields' => array_keys($data),
            ]
        );

        return $profile->fresh();
    }

    /**
     * Upload customer avatar.
     */
    public function uploadAvatar(UploadedFile $file): CustomerProfile
    {
        $profile = Auth::user()->customerProfile;

        if (!$profile) {
            throw ValidationException::withMessages([
                'profile' => ['Customer profile not found.'],
            ]);
        }

        $avatar = $this->fileUploadService->replace(
            file: $file,
            oldPath: $profile->avatar,
            directory: 'uploads/customers/avatars'
        );

        $profile->update([
            'avatar' => $avatar,
        ]);

        // Activity Log
        $this->activityLogService->log(
            action: ActivityAction::PROFILE_PHOTO_UPDATED,
            description: 'Customer updated profile avatar.',
            subject: $profile,
            properties: [
                'avatar' => $avatar,
            ]
        );

        return $profile->fresh();
    }


    /**
     * Calculate profile completion percentage.
     */
    private function calculateProfileCompletion(CustomerProfile $profile): int
    {
        $fields = [
            'gender',
            'date_of_birth',
            'nic_passport',
            'emergency_contact_name',
            'emergency_contact_phone',
            'preferred_language',
        ];

        $completed = 0;

        foreach ($fields as $field) {
            if (!empty($profile->{$field})) {
                $completed++;
            }
        }

        return (int) round(($completed / count($fields)) * 100);
    }
}


/**
     * Delete customer profile.
     */
    // public function delete(): void
    // {
    //     $profile = Auth::user()->customerProfile;

    //     if (!$profile) {
    //         throw ValidationException::withMessages([
    //             'profile' => ['Customer profile not found.']
    //         ]);
    //     }

    //     $profile->delete();
    // }

     /**
     * Create a customer profile.
     */
    // public function create(array $data): CustomerProfile
    // {
    //     $user = Auth::user();

    //     if ($user->customerProfile) {
    //         throw ValidationException::withMessages([
    //             'profile' => ['Customer profile already exists.']
    //         ]);
    //     }

    //     $profile = CustomerProfile::create([
    //         'user_id' => $user->id,
    //         'gender' => $data['gender'] ?? null,
    //         'date_of_birth' => $data['date_of_birth'] ?? null,
    //         'nic_passport' => $data['nic_passport'] ?? null,
    //         'emergency_contact_name' => $data['emergency_contact_name'] ?? null,
    //         'emergency_contact_phone' => $data['emergency_contact_phone'] ?? null,
    //         'preferred_language' => $data['preferred_language'] ?? 'English',
    //         'profile_completed' => $this->isProfileCompleted($data),
    //     ]);

    //     return $profile;
    // }
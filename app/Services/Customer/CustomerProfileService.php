<?php

namespace App\Services\Customer;

use App\Models\CustomerProfile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class CustomerProfileService
{
    /**
     * Create a customer profile.
     */
    public function create(array $data): CustomerProfile
    {
        $user = Auth::user();

        if ($user->customerProfile) {
            throw ValidationException::withMessages([
                'profile' => ['Customer profile already exists.']
            ]);
        }

        $profile = CustomerProfile::create([
            'user_id' => $user->id,
            'gender' => $data['gender'] ?? null,
            'date_of_birth' => $data['date_of_birth'] ?? null,
            'nic_passport' => $data['nic_passport'] ?? null,
            'emergency_contact_name' => $data['emergency_contact_name'] ?? null,
            'emergency_contact_phone' => $data['emergency_contact_phone'] ?? null,
            'preferred_language' => $data['preferred_language'] ?? 'English',
            'profile_completed' => $this->isProfileCompleted($data),
        ]);

        return $profile;
    }

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
                'profile' => ['Customer profile not found.']
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

        $profile->profile_completed = $this->isProfileCompleted($profile->toArray());
        $profile->save();

        return $profile->fresh();
    }

    /**
     * Delete customer profile.
     */
    public function delete(): void
    {
        $profile = Auth::user()->customerProfile;

        if (!$profile) {
            throw ValidationException::withMessages([
                'profile' => ['Customer profile not found.']
            ]);
        }

        $profile->delete();
    }

    /**
     * Check whether the profile is complete.
     */
    private function isProfileCompleted(array $data): bool
    {
        return !empty($data['gender']) &&
               !empty($data['date_of_birth']) &&
               !empty($data['nic_passport']) &&
               !empty($data['emergency_contact_name']) &&
               !empty($data['emergency_contact_phone']) &&
               !empty($data['preferred_language']);
    }
}
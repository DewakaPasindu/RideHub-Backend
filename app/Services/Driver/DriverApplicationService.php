<?php

namespace App\Services\Driver;

use App\Models\DriverApplication;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Core\Enums\ApplicationStatus;

class DriverApplicationService
{
    /**
     * Create a new driver application.
     */
    public function create(array $data): DriverApplication
    {
        return DB::transaction(function () use ($data) {

            $user = Auth::user();

            // Prevent duplicate active applications
            $existing = DriverApplication::where('user_id', $user->id)
                ->whereIn('application_status', [
                    ApplicationStatus::PENDING->value,
                    ApplicationStatus::MORE_INFORMATION_REQUIRED->value,
                ])
                ->first();

            if ($existing) {
                throw ValidationException::withMessages([
                    'application' => [
                        'You already have an active driver application.'
                    ]
                ]);
            }

            // TODO: Store uploaded files
            // We'll implement this in the next step.

            return DriverApplication::create([
                'user_id' => $user->id,

                'application_status' => ApplicationStatus::PENDING,

                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],

                'nic_passport' => $data['nic_passport'],

                'date_of_birth' => $data['date_of_birth'],

                'phone' => $data['phone'],

                'address' => $data['address'],

                'driving_license_number' => $data['driving_license_number'],

                'license_expiry_date' => $data['license_expiry_date'],

                'years_of_experience' => $data['years_of_experience'],

                'languages' => $data['languages'] ?? null,

                'skills' => $data['skills'] ?? null,

                'availability' => $data['availability'],

                // Temporary placeholders
                'license_document' => '',

                'nic_document' => '',

                'selfie_photo' => '',
            ]);

        });
    }

    /**
     * Get current user's driver application.
     */
    public function get()
    {
        return Auth::user()->driverApplication;
    }

    /**
     * Update current driver's application.
     */
    public function update(array $data): DriverApplication
    {
        $application = Auth::user()->driverApplication;

        if (!$application) {
            throw ValidationException::withMessages([
                'application' => [
                    'Driver application not found.'
                ]
            ]);
        }

        // Prevent updates after approval or rejection
        if (in_array($application->application_status, [
            ApplicationStatus::APPROVED,
            ApplicationStatus::REJECTED,
        ])) {
            throw ValidationException::withMessages([
                'application' => [
                    'This application can no longer be updated.'
                ]
            ]);
        }

        $application->update($data);

        return $application->fresh();
    }

    /**
     * Delete current application.
     */
    /**
     * Delete current application.
     */
    public function delete(): void
    {
        $application = Auth::user()->driverApplication;

        if (!$application) {
            throw ValidationException::withMessages([
                'application' => [
                    'Driver application not found.'
                ]
            ]);
        }

        if ($application->application_status === ApplicationStatus::APPROVED) {
            throw ValidationException::withMessages([
                'application' => [
                    'Approved applications cannot be deleted.'
                ]
            ]);
        }

        $application->delete();
    }
}
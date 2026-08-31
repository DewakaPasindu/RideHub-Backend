<?php

namespace App\Services\Driver;

use App\Core\Enums\ApplicationStatus;
use App\Models\DriverApplication;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Storage;

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
                        'You already have an active driver application.',
                    ],
                ]);
            }

            $licenseDocument = $data['license_document']->store(
                'drivers/licenses',
                'public'
            );

            $nicDocument = $data['nic_document']->store(
                'drivers/nic',
                'public'
            );

            $selfiePhoto = $data['selfie_photo']->store(
                'drivers/selfies',
                'public'
            );

            return DriverApplication::create([

                'user_id' => $user->id,

                'application_status' => ApplicationStatus::PENDING->value,

                // Personal Information
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'nic_passport' => $data['nic_passport'],
                'date_of_birth' => $data['date_of_birth'],
                'gender' => $data['gender'],
                'phone' => $data['phone'],
                'emergency_contact_name' => $data['emergency_contact_name'] ?? null,
                'emergency_contact_phone' => $data['emergency_contact_phone'] ?? null,
                'address' => $data['address'],

                // License Details
                'driving_license_number' => $data['driving_license_number'],
                'license_classes' => $data['license_classes'] ?? null,
                'license_expiry_date' => $data['license_expiry_date'],

                // Driver Details
                'vehicle_types' => $data['vehicle_types'] ?? null,
                'years_of_experience' => $data['years_of_experience'],
                'languages' => $data['languages'] ?? null,
                'skills' => $data['skills'] ?? null,
                'area_id' => $data['area_id'] ?? null,
                'availability' => $data['availability'],

                // Documents
                'license_document' => $licenseDocument,
                'nic_document' => $nicDocument,
                'selfie_photo' => $selfiePhoto,
            ]);
        });
    }

    /**
     * Get current user's driver application.
     */
    public function get(): ?DriverApplication
    {
        return Auth::user()->driverApplication;
    }

    /**
     * Update current driver's application.
     */
    public function update(array $data): DriverApplication
    {
        $application = Auth::user()->driverApplication;

        if (! $application) {
            throw ValidationException::withMessages([
                'application' => [
                    'Driver application not found.',
                ],
            ]);
        }

        // Prevent updates after approval or rejection
        if (in_array($application->application_status, [
            ApplicationStatus::APPROVED->value,
            ApplicationStatus::REJECTED->value,
        ])) {
            throw ValidationException::withMessages([
                'application' => [
                    'This application can no longer be updated.',
                ],
            ]);
        }

        // TODO:
        // Handle document replacements here in the next step.

        if (isset($data['license_document'])) {

            if ($application->license_document) {
                Storage::disk('public')->delete($application->license_document);
            }

            $data['license_document'] = $data['license_document']->store(
                'drivers/licenses',
                'public'
            );
        }

        if (isset($data['nic_document'])) {

            if ($application->nic_document) {
                Storage::disk('public')->delete($application->nic_document);
            }

            $data['nic_document'] = $data['nic_document']->store(
                'drivers/nic',
                'public'
            );
        }

        if (isset($data['selfie_photo'])) {

            if ($application->selfie_photo) {
                Storage::disk('public')->delete($application->selfie_photo);
            }

            $data['selfie_photo'] = $data['selfie_photo']->store(
                'drivers/selfies',
                'public'
            );
        }

        $application->update($data);
        return $application->fresh();
    }

    /**
     * Delete current driver's application.
     */
    public function delete(): void
    {
        $application = Auth::user()->driverApplication;

        if (! $application) {
            throw ValidationException::withMessages([
                'application' => [
                    'Driver application not found.',
                ],
            ]);
        }

        if ($application->application_status === ApplicationStatus::APPROVED->value) {
            throw ValidationException::withMessages([
                'application' => [
                    'Approved applications cannot be deleted.',
                ],
            ]);
        }

        $application->delete();
    }
}
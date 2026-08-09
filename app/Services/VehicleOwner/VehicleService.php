<?php

namespace App\Services\VehicleOwner;

use App\Core\Enums\ApplicationStatus;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleOwnerProfile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class VehicleService
{
    /**
     * Create a vehicle for the authenticated vehicle owner.
     */
    public function create(User $user, array $data): Vehicle
    {
        return DB::transaction(function () use ($user, $data) {

            $profile = VehicleOwnerProfile::where('user_id', $user->id)
                ->first();

            if (!$profile) {
                throw ValidationException::withMessages([
                    'profile' => [
                        'Vehicle owner profile not found.',
                    ],
                ]);
            }

            if (
                $profile->application_status !==
                ApplicationStatus::APPROVED->value
            ) {
                throw ValidationException::withMessages([
                    'profile' => [
                        'Vehicle owner profile must be approved before adding a vehicle.',
                    ],
                ]);
            }

            $data['vehicle_owner_profile_id'] = $profile->id;

            $data['application_status'] =
                ApplicationStatus::DRAFT->value;

            return Vehicle::create($data);
        });
    }

    /**
     * Get all vehicles belonging to the authenticated vehicle owner.
     */
    public function getVehicles(User $user)
    {
        $profile = VehicleOwnerProfile::where('user_id', $user->id)
            ->first();

        if (!$profile) {
            return collect();
        }

        return Vehicle::with([
            'vehicleOwnerProfile',
            'reviewer',
            'documents',
            'images',
            'pricing',
            'availability',
            'insurance',
        ])
        ->where('vehicle_owner_profile_id', $profile->id)
        ->latest()
        ->get();
    }

    /**
     * Get a specific vehicle belonging to the authenticated vehicle owner.
     */
    public function getVehicle(
        User $user,
        Vehicle $vehicle
    ): Vehicle {

        $this->verifyOwnership($user, $vehicle);

        return $vehicle->load([
            'vehicleOwnerProfile',
            'reviewer',
            'documents',
            'images',
            'pricing',
            'availability',
            'insurance',
        ]);
    }

    /**
     * Update a vehicle belonging to the authenticated vehicle owner.
     */
    public function update(
        User $user,
        Vehicle $vehicle,
        array $data
    ): Vehicle {

        return DB::transaction(function () use (
            $user,
            $vehicle,
            $data
        ) {

            $this->verifyOwnership($user, $vehicle);

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
             * If an approved vehicle is edited,
             * return it to draft for re-review.
             */
            if (
                $vehicle->application_status ===
                ApplicationStatus::APPROVED
            ) {
                $data['application_status'] =
                    ApplicationStatus::DRAFT->value;
            }

            $vehicle->fill($data);
            $vehicle->save();

            return $vehicle->fresh([
                'vehicleOwnerProfile',
                'reviewer',
                'documents',
                'images',
                'pricing',
                'availability',
                'insurance',
            ]);
        });
    }

    /**
     * Delete a vehicle belonging to the authenticated vehicle owner.
     */
    public function delete(
        User $user,
        Vehicle $vehicle
    ): void {

        $this->verifyOwnership($user, $vehicle);

        $vehicle->delete();
    }

    /**
     * Verify that the vehicle belongs to the authenticated owner.
     */
    private function verifyOwnership(
        User $user,
        Vehicle $vehicle
    ): void {

        $profile = VehicleOwnerProfile::where('user_id', $user->id)
            ->first();

        if (!$profile) {
            throw ValidationException::withMessages([
                'profile' => [
                    'Vehicle owner profile not found.',
                ],
            ]);
        }

        if (
            $vehicle->vehicle_owner_profile_id !==
            $profile->id
        ) {
            throw ValidationException::withMessages([
                'vehicle' => [
                    'You are not authorized to access this vehicle.',
                ],
            ]);
        }
    }
}
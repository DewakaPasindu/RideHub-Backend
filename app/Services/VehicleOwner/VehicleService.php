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
     * List approved vehicles matching search criteria.
     */
    public function listPublic(array $filters)
    {
        $query = Vehicle::with(['vehicleOwnerProfile', 'vehicleOwnerProfile.user'])
            ->where('application_status', 'approved');

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('make', 'like', "%{$search}%")
                  ->orWhere('model', 'like', "%{$search}%")
                  ->orWhere('color', 'like', "%{$search}%")
                  ->orWhere('nearest_town', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['vehicle_type'])) {
            $query->where('vehicle_type', $filters['vehicle_type']);
        }

        if (!empty($filters['nearest_town'])) {
            $query->where('nearest_town', $filters['nearest_town']);
        }

        if (!empty($filters['min_seats'])) {
            $query->where('seating_capacity', '>=', (int)$filters['min_seats']);
        }

        if (!empty($filters['transmission'])) {
            $query->where('transmission', $filters['transmission']);
        }

        if (!empty($filters['fuel_type'])) {
            $query->where('fuel_type', $filters['fuel_type']);
        }

        if (isset($filters['has_ac'])) {
            $query->where('has_ac', filter_var($filters['has_ac'], FILTER_VALIDATE_BOOLEAN));
        }

        if (!empty($filters['max_price'])) {
            $query->where('price_per_day', '<=', (float)$filters['max_price']);
        }

        $sort = $filters['sort'] ?? 'latest';
        if ($sort === 'price_asc') {
            $query->orderBy('price_per_day', 'asc');
        } elseif ($sort === 'price_desc') {
            $query->orderBy('price_per_day', 'desc');
        } else {
            $query->latest();
        }

        $page = (int)($filters['page'] ?? 1);
        $perPage = (int)($filters['per_page'] ?? 15);

        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Upload documents for a vehicle.
     */
    public function uploadDocuments(User $user, Vehicle $vehicle, array $files): Vehicle
    {
        $this->verifyOwnership($user, $vehicle);

        if (isset($files['revenue_license_document'])) {
            $path = $files['revenue_license_document']->store('vehicles/documents', 'public');
            $vehicle->revenue_license_document = $path;
        }

        if (isset($files['insurance_card_document'])) {
            $path = $files['insurance_card_document']->store('vehicles/documents', 'public');
            $vehicle->insurance_card_document = $path;
        }

        $vehicle->save();

        return $vehicle->fresh();
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
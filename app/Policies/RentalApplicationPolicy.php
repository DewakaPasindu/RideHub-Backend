<?php

namespace App\Policies;

use App\Models\RentalApplication;
use App\Models\User;

class RentalApplicationPolicy
{
    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, RentalApplication $rentalApplication): bool
    {
        // Admin check
        if ($user->hasRole('admin') || $user->hasRole('superadmin')) {
            return true;
        }

        // Customer check
        if ($user->id === $rentalApplication->customer_id) {
            return true;
        }

        // Owner check
        $ownerUser = $rentalApplication->vehicle?->vehicleOwnerProfile?->user_id;
        if ($ownerUser && $user->id === $ownerUser) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, RentalApplication $rentalApplication): bool
    {
        // Only customer can edit, and only in draft or more_info status
        if ($user->id !== $rentalApplication->customer_id) {
            return false;
        }

        return in_array($rentalApplication->status, ['draft', 'more_information_required']);
    }

    /**
     * Determine whether the owner can review or inspect.
     */
    public function ownerAction(User $user, RentalApplication $rentalApplication): bool
    {
        $ownerUser = $rentalApplication->vehicle?->vehicleOwnerProfile?->user_id;
        return $ownerUser && $user->id === $ownerUser;
    }
}

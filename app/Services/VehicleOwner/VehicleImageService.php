<?php

namespace App\Services\VehicleOwner;

use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class VehicleImageService
{
    /**
     * Upload an image for a vehicle.
     */
    public function upload(
        User $user,
        Vehicle $vehicle,
        UploadedFile $file,
        string $imageType
    ): VehicleImage {
        $this->verifyOwnership($user, $vehicle);

        return DB::transaction(function () use (
            $user,
            $vehicle,
            $file,
            $imageType
        ) {
            /*
             * Prevent duplicate image types.
             */
            $exists = VehicleImage::where('vehicle_id', $vehicle->id)
                ->where('image_type', $imageType)
                ->exists();

            if ($exists) {
                throw ValidationException::withMessages([
                    'image_type' => [
                        'An image for this position already exists.'
                    ],
                ]);
            }

            $extension = strtolower(
                $file->getClientOriginalExtension()
            );

            $storedName = Str::uuid() . '.' . $extension;

            $directory = 'vehicle-images/' . $vehicle->id;

            $filePath = $file->storeAs(
                $directory,
                $storedName,
                'public'
            );

            /*
             * Interior is not automatically primary.
             * The front image becomes primary by default.
             */
            $isPrimary = $imageType === 'front';

            $sortOrder = match ($imageType) {
                'front' => 1,
                'rear' => 2,
                'left_side' => 3,
                'right_side' => 4,
                'interior' => 5,
                default => 0,
            };

            /*
             * If another image is primary, remove that status.
             */
            if ($isPrimary) {
                VehicleImage::where('vehicle_id', $vehicle->id)
                    ->update([
                        'is_primary' => false,
                    ]);
            }

            return VehicleImage::create([
                'vehicle_id' => $vehicle->id,

                'image_type' => $imageType,

                'original_name' => $file->getClientOriginalName(),
                'stored_name' => $storedName,

                'disk' => 'public',
                'file_path' => $filePath,

                'mime_type' => $file->getMimeType(),
                'extension' => $extension,
                'file_size' => $file->getSize(),

                'is_primary' => $isPrimary,
                'sort_order' => $sortOrder,

                'uploaded_by' => $user->id,
            ]);
        });
    }

    /**
     * Get all images belonging to a vehicle.
     */
    public function getImages(
        User $user,
        Vehicle $vehicle
    ) {
        $this->verifyOwnership($user, $vehicle);

        return VehicleImage::where('vehicle_id', $vehicle->id)
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * Delete a vehicle image.
     */
    public function delete(
        User $user,
        Vehicle $vehicle,
        VehicleImage $image
    ): void {
        $this->verifyOwnership($user, $vehicle);

        if ($image->vehicle_id !== $vehicle->id) {
            throw ValidationException::withMessages([
                'image' => [
                    'This image does not belong to the specified vehicle.'
                ],
            ]);
        }

        DB::transaction(function () use ($image) {

            if ($image->file_path) {
                Storage::disk($image->disk)->delete(
                    $image->file_path
                );
            }

            $image->delete();
        });
    }

    /**
     * Verify that the vehicle belongs to the authenticated owner.
     */
    private function verifyOwnership(
        User $user,
        Vehicle $vehicle
    ): void {
        $profile = $vehicle->vehicleOwnerProfile;

        if (!$profile) {
            throw ValidationException::withMessages([
                'profile' => [
                    'Vehicle owner profile not found.',
                ],
            ]);
        }

        if ($profile->user_id !== $user->id) {
            throw ValidationException::withMessages([
                'vehicle' => [
                    'You are not authorized to access this vehicle.',
                ],
            ]);
        }
    }
}
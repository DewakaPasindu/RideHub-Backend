<?php

namespace App\Http\Controllers\Api\V1\Driver;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Resources\DriverProfileResource;
use App\Models\DriverApplication;
use App\Models\Area;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DriverController extends BaseApiController
{
    /**
     * List approved drivers with filtering.
     */
    public function index(Request $request): JsonResponse
    {
        $query = DriverApplication::with(['user', 'area'])
            ->where('application_status', 'approved');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('nearest_town', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%");
            });
        }

        if ($request->filled('min_experience')) {
            $query->where('years_of_experience', '>=', (int)$request->min_experience);
        }

        if ($request->filled('max_experience')) {
            $query->where('years_of_experience', '<=', (int)$request->max_experience);
        }

        if ($request->filled('min_rating')) {
            $query->where('rating', '>=', (double)$request->min_rating);
        }

        if ($request->filled('nearest_town')) {
            $town = $request->nearest_town;
            $query->whereHas('area', function ($q) use ($town) {
                $q->where('name', 'like', "%{$town}%");
            });
        }

        if ($request->filled('availability_status')) {
            $query->where('availability_status', $request->availability_status);
        }

        // Sorting
        $sort = $request->get('sort', 'rating_desc');
        switch ($sort) {
            case 'experience_desc':
                $query->orderBy('years_of_experience', 'desc');
                break;
            case 'experience_asc':
                $query->orderBy('years_of_experience', 'asc');
                break;
            case 'rating_desc':
            default:
                $query->orderBy('rating', 'desc');
                break;
        }

        $perPage = (int)$request->get('per_page', 12);
        $drivers = $query->paginate($perPage);

        return $this->success(
            DriverProfileResource::collection($drivers),
            'Drivers retrieved successfully.'
        );
    }

    /**
     * Get a specific driver profile by UUID.
     */
    public function show(string $uuid): JsonResponse
    {
        $driver = DriverApplication::with(['user', 'area'])
            ->where('uuid', $uuid)
            ->first();

        if (!$driver) {
            return $this->error('Driver not found.', null, 404);
        }

        return $this->success(
            new DriverProfileResource($driver),
            'Driver retrieved successfully.'
        );
    }

    /**
     * Get a driver profile by User UUID.
     */
    public function byUser(string $userId): JsonResponse
    {
        $user = User::where('uuid', $userId)->first();
        if (!$user) {
            return $this->error('User not found.', null, 404);
        }

        $driver = DriverApplication::with(['user', 'area'])
            ->where('user_id', $user->id)
            ->first();

        if (!$driver) {
            return $this->error('Driver profile not found.', null, 404);
        }

        return $this->success(
            new DriverProfileResource($driver),
            'Driver retrieved successfully.'
        );
    }

    /**
     * Register a driver application.
     */
    public function register(Request $request): JsonResponse
    {
        $user = $request->user();

        // Check if application exists
        $existing = DriverApplication::where('user_id', $user->id)->first();
        if ($existing) {
            return $this->error('Driver application already exists for this user.', null, 422);
        }

        // Map inputs from frontend DriverInsert format
        $data = $request->all();
        
        // Find area by nearest town
        $areaId = null;
        if (!empty($data['nearest_town'])) {
            $area = Area::where('name', 'like', "%{$data['nearest_town']}%")->first();
            if ($area) {
                $areaId = $area->id;
            }
        }

        // Base photo/doc paths
        $selfiePath = 'placeholder_selfie.png';
        $licensePath = 'placeholder_license.png';
        $nicPath = 'placeholder_nic.png';

        $driver = DriverApplication::create([
            'uuid' => (string) Str::uuid(),
            'user_id' => $user->id,
            'application_status' => 'pending',
            'first_name' => $data['first_name'] ?? $user->first_name,
            'last_name' => $data['last_name'] ?? $user->last_name,
            'nic_passport' => $data['nic_passport'] ?? $data['nic_number'] ?? 'N/A',
            'date_of_birth' => $data['date_of_birth'] ?? now()->subYears(20)->format('Y-m-d'),
            'gender' => $data['gender'] ?? 'other',
            'phone' => $data['phone'] ?? $user->phone ?? 'N/A',
            'address' => $data['address'] ?? 'N/A',
            'driving_license_number' => $data['driving_license_number'] ?? $data['license_number'] ?? 'N/A',
            'license_classes' => $data['license_classes'] ?? [],
            'license_expiry_date' => $data['license_expiry_date'] ?? now()->addYears(5)->format('Y-m-d'),
            'years_of_experience' => $data['years_of_experience'] ?? $data['experience_years'] ?? 0,
            'languages' => $data['languages'] ?? [],
            'skills' => $data['skills'] ?? $data['specialties'] ?? [],
            'area_id' => $areaId,
            'availability' => $data['availability'] ?? 'full_time',
            'rating' => 5.0,
            'review_count' => 0,
            'availability_status' => 'available',
            'selfie_photo' => $selfiePath,
            'license_document' => $licensePath,
            'nic_document' => $nicPath,
        ]);

        // Assign Spatie role
        $user->assignRole('Driver');

        return $this->success(
            new DriverProfileResource($driver),
            'Driver application registered successfully.',
            201
        );
    }

    /**
     * Update driver application.
     */
    public function update(Request $request, string $uuid): JsonResponse
    {
        $driver = DriverApplication::where('uuid', $uuid)->first();
        if (!$driver) {
            return $this->error('Driver profile not found.', null, 404);
        }

        $data = $request->all();
        
        if (isset($data['availability_status'])) {
            $driver->availability_status = $data['availability_status'];
        }
        if (isset($data['location_lat'])) {
            $driver->location_lat = $data['location_lat'];
        }
        if (isset($data['location_lng'])) {
            $driver->location_lng = $data['location_lng'];
        }
        if (isset($data['skills'])) {
            $driver->skills = $data['skills'];
        }
        if (isset($data['availability'])) {
            $driver->availability = $data['availability'];
        }
        
        $driver->save();

        return $this->success(
            new DriverProfileResource($driver),
            'Driver profile updated successfully.'
        );
    }

    /**
     * Upload driver verification documents.
     */
    public function uploadDocuments(Request $request, string $uuid): JsonResponse
    {
        $driver = DriverApplication::where('uuid', $uuid)->first();
        if (!$driver) {
            return $this->error('Driver profile not found.', null, 404);
        }

        if ($request->hasFile('selfie')) {
            $driver->selfie_photo = $request->file('selfie')->store('drivers/selfies', 'public');
        }
        if ($request->hasFile('license')) {
            $driver->license_document = $request->file('license')->store('drivers/licenses', 'public');
        }
        if ($request->hasFile('nic')) {
            $driver->nic_document = $request->file('nic')->store('drivers/nic', 'public');
        }

        $driver->save();

        return $this->success(
            new DriverProfileResource($driver),
            'Driver verification documents uploaded successfully.'
        );
    }

    /**
     * Admin approve driver.
     */
    public function approve(string $uuid): JsonResponse
    {
        $driver = DriverApplication::where('uuid', $uuid)->first();
        if (!$driver) {
            return $this->error('Driver application not found.', null, 404);
        }

        $driver->application_status = 'approved';
        $driver->reviewed_at = now();
        $driver->reviewed_by = auth()->id();
        $driver->save();

        // Ensure user has driver role
        $driver->user->assignRole('Driver');

        return $this->success(null, 'Driver application approved successfully.');
    }

    /**
     * Admin reject driver.
     */
    public function reject(Request $request, string $uuid): JsonResponse
    {
        $driver = DriverApplication::where('uuid', $uuid)->first();
        if (!$driver) {
            return $this->error('Driver application not found.', null, 404);
        }

        $driver->application_status = 'rejected';
        $driver->admin_notes = $request->get('reason', 'Verification rejected');
        $driver->reviewed_at = now();
        $driver->reviewed_by = auth()->id();
        $driver->save();

        return $this->success(null, 'Driver application rejected.');
    }

    /**
     * Admin suspend driver.
     */
    public function suspend(Request $request, string $uuid): JsonResponse
    {
        $driver = DriverApplication::where('uuid', $uuid)->first();
        if (!$driver) {
            return $this->error('Driver profile not found.', null, 404);
        }

        $driver->availability_status = 'unavailable';
        $driver->admin_notes = $request->get('reason', 'Driver profile suspended');
        $driver->save();

        return $this->success(null, 'Driver profile suspended.');
    }

    /**
     * Pending applications count.
     */
    public function pendingCount(): JsonResponse
    {
        $count = DriverApplication::where('application_status', 'pending')->count();
        return $this->success(['count' => $count], 'Pending drivers count.');
    }
}

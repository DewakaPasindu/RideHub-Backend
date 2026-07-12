<?php

namespace App\Services\Admin;

use App\Core\Enums\ApplicationStatus;
use App\Models\DriverApplication;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AdminDriverApplicationService
{
    /**
     * Get paginated driver applications.
     */
    public function list(array $filters = []): LengthAwarePaginator
    {
        $query = DriverApplication::query()
            ->with(['user', 'reviewer', 'area']);

        if (!empty($filters['status'])) {
            $query->where('application_status', $filters['status']);
        }

        if (!empty($filters['search'])) {

            $search = $filters['search'];

            $query->where(function ($q) use ($search) {

                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('nic_passport', 'like', "%{$search}%")
                    ->orWhere('driving_license_number', 'like', "%{$search}%");

            });
        }

        return $query
            ->latest()
            ->paginate(15);
    }

    /**
     * View one application.
     */
    public function show(string $uuid): DriverApplication
    {
        return DriverApplication::with([
            'user',
            'reviewer',
            'area',
        ])->where('uuid', $uuid)->firstOrFail();
    }

    /**
     * Approve application.
     */
    public function approve(string $uuid, ?string $notes = null): DriverApplication
    {
        return DB::transaction(function () use ($uuid, $notes) {

            $application = DriverApplication::where('uuid', $uuid)
                ->firstOrFail();

            if ($application->application_status === ApplicationStatus::APPROVED->value) {
                throw ValidationException::withMessages([
                    'application' => [
                        'Application already approved.'
                    ]
                ]);
            }

            $application->update([
                'application_status' => ApplicationStatus::APPROVED->value,
                'admin_notes' => $notes,
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now(),
            ]);

            return $application->fresh();
        });
    }

    /**
     * Reject application.
     */
    public function reject(string $uuid, string $notes): DriverApplication
    {
        return DB::transaction(function () use ($uuid, $notes) {

            $application = DriverApplication::where('uuid', $uuid)
                ->firstOrFail();

            $application->update([
                'application_status' => ApplicationStatus::REJECTED->value,
                'admin_notes' => $notes,
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now(),
            ]);

            return $application->fresh();
        });
    }

    /**
     * Request more information.
     */
    public function requestMoreInformation(string $uuid, string $notes): DriverApplication
    {
        return DB::transaction(function () use ($uuid, $notes) {

            $application = DriverApplication::where('uuid', $uuid)
                ->firstOrFail();

            $application->update([
                'application_status' => ApplicationStatus::MORE_INFORMATION_REQUIRED->value,
                'admin_notes' => $notes,
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now(),
            ]);

            return $application->fresh();
        });
    }
}
<?php

namespace App\Http\Controllers\Api\V1\Rental;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Resources\RentalHandoverResource;
use App\Models\RentalApplication;
use App\Models\RentalHandover;
use App\Services\Rental\RentalNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RentalHandoverController extends BaseApiController
{
    /**
     * Get or create handover record.
     */
    public function show(string $appUuid): JsonResponse
    {
        $app = RentalApplication::where('uuid', $appUuid)->first();

        if (!$app) {
            return $this->error('Rental application not found.', null, 404);
        }

        if (auth()->user()->cannot('view', $app)) {
            return $this->error('Unauthorized.', null, 403);
        }

        $handover = RentalHandover::firstOrCreate([
            'rental_application_id' => $app->id
        ], [
            'status' => 'pending'
        ]);

        return $this->success(
            new RentalHandoverResource($handover),
            'Handover status retrieved.'
        );
    }

    /**
     * Customer confirms pre-rental vehicle condition review.
     */
    public function customerConfirm(Request $request, string $appUuid): JsonResponse
    {
        $app = RentalApplication::with(['conditions'])->where('uuid', $appUuid)->first();

        if (!$app) {
            return $this->error('Rental application not found.', null, 404);
        }

        if ($app->customer_id !== auth()->id()) {
            return $this->error('Unauthorized: Only customer can confirm handover.', null, 403);
        }

        // Verify pre-rental condition is present
        $preRental = $app->conditions()->where('inspection_stage', 'pre_rental')->first();
        if (!$preRental) {
            return $this->error('Handover error: Pre-rental vehicle condition report not submitted by owner yet.', null, 422);
        }

        $handover = RentalHandover::firstOrCreate([
            'rental_application_id' => $app->id
        ]);

        return DB::transaction(function () use ($app, $handover, $request) {
            $handover->customer_confirmed_at = now();
            $handover->status = $handover->owner_confirmed_at ? 'completed' : 'customer_confirmed';
            
            if ($request->filled('latitude') && $request->filled('longitude')) {
                $handover->handover_latitude = $request->latitude;
                $handover->handover_longitude = $request->longitude;
            }
            
            $handover->save();

            $this->checkAndActivateRental($app, $handover);

            return $this->success(
                new RentalHandoverResource($handover),
                'Customer handover confirmation recorded.'
            );
        });
    }

    /**
     * Owner confirms handover.
     */
    public function ownerConfirm(Request $request, string $appUuid): JsonResponse
    {
        $app = RentalApplication::where('uuid', $appUuid)->first();

        if (!$app) {
            return $this->error('Rental application not found.', null, 404);
        }

        if (auth()->user()->cannot('ownerAction', $app)) {
            return $this->error('Unauthorized: Only owner can confirm handover.', null, 403);
        }

        $handover = RentalHandover::firstOrCreate([
            'rental_application_id' => $app->id
        ]);

        return DB::transaction(function () use ($app, $handover, $request) {
            $handover->owner_confirmed_at = now();
            $handover->status = $handover->customer_confirmed_at ? 'completed' : 'owner_confirmed';
            
            if ($request->filled('latitude') && $request->filled('longitude')) {
                $handover->handover_latitude = $request->latitude;
                $handover->handover_longitude = $request->longitude;
            }
            
            $handover->save();

            $this->checkAndActivateRental($app, $handover);

            return $this->success(
                new RentalHandoverResource($handover),
                'Owner handover confirmation recorded.'
            );
        });
    }

    /**
     * If both customer and owner confirmed, transition status to ACTIVE.
     */
    protected function checkAndActivateRental(RentalApplication $app, RentalHandover $handover): void
    {
        if ($handover->status === 'completed') {
            $handover->handover_at = now();
            $handover->save();

            // Transition application and booking statuses
            $app->status = 'active';
            $app->save();

            if ($app->booking) {
                $app->booking->status = 'active';
                $app->booking->trip_started_at = now();
                $app->booking->save();
            }

            RentalNotificationService::notify(
                $app,
                'RENTAL_ACTIVE',
                'Rental is Active',
                "Your rental for the vehicle is now active! Happy driving!"
            );
        }
    }
}

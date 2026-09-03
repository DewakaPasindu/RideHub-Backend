<?php

namespace App\Http\Controllers\Api\V1\Rental;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Resources\RentalApplicationResource;
use App\Models\RentalApplication;
use App\Services\Rental\RentalNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RentalReturnController extends BaseApiController
{
    /**
     * Complete the rental return and set vehicle status to completed/available.
     */
    public function completeReturn(Request $request, string $uuid): JsonResponse
    {
        $app = RentalApplication::with(['conditions'])->where('uuid', $uuid)->first();

        if (!$app) {
            return $this->error('Rental application not found.', null, 404);
        }

        if (auth()->user()->cannot('ownerAction', $app)) {
            return $this->error('Unauthorized.', null, 403);
        }

        if ($app->status !== 'active') {
            return $this->error('Only active rentals can be completed.', null, 422);
        }

        // Verify return inspection exists
        $hasReturnInspect = $app->conditions()->where('inspection_stage', 'return')->exists();
        if (!$hasReturnInspect) {
            return $this->error('Completion error: Return condition report not submitted by owner yet.', null, 422);
        }

        return DB::transaction(function () use ($app) {
            $app->status = 'completed';
            $app->save();

            if ($app->booking) {
                $app->booking->status = 'completed';
                $app->booking->trip_ended_at = now();
                $app->booking->save();
            }

            RentalNotificationService::notify(
                $app,
                'RENTAL_COMPLETED',
                'Rental Completed Successfully',
                "Your self-drive rental transaction is completed. Thank you!"
            );

            return $this->success(
                new RentalApplicationResource($app),
                'Rental application completed successfully.'
            );
        });
    }
}

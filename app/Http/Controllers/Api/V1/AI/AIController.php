<?php

namespace App\Http\Controllers\Api\V1\AI;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Resources\VehicleResource;
use App\Http\Resources\DriverProfileResource;
use App\Models\Vehicle;
use App\Models\DriverApplication;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AIController extends BaseApiController
{
    /**
     * AI vehicle recommendation and scoring engine.
     */
    public function vehicleRecommendations(Request $request): JsonResponse
    {
        $request->validate([
            'passenger_count' => 'required|integer|min:1',
            'budget' => 'required|numeric',
        ]);

        $passengerCount = $request->passenger_count;
        $budget = $request->budget;
        $distanceKm = $request->get('distance_km', 100);
        $luggageSize = $request->get('luggage_size', 'medium');
        $preferredType = $request->vehicle_type;

        // Fetch approved vehicles
        $vehicles = Vehicle::with(['vehicleOwnerProfile', 'vehicleOwnerProfile.user'])
            ->where('application_status', 'approved')
            ->get();

        $recommendations = [];

        foreach ($vehicles as $vehicle) {
            // Seat capacity score (30 pts)
            $seatScore = 0;
            if ($vehicle->seating_capacity >= $passengerCount) {
                $seatScore = max(15, 30 - ($vehicle->seating_capacity - $passengerCount) * 2);
            }

            // Budget fit score (25 pts)
            $budgetScore = 0;
            if ($vehicle->price_per_day <= $budget) {
                $budgetScore = min(25, 25 * ($budget / $vehicle->price_per_day));
            } else {
                $budgetScore = max(0, 10 - (($vehicle->price_per_day - $budget) / $budget) * 20);
            }

            // Vehicle type match (25 pts)
            $typeMatch = 10;
            if ($preferredType && strtolower($vehicle->vehicle_type->value ?? $vehicle->vehicle_type) === strtolower($preferredType)) {
                $typeMatch = 25;
            } elseif ($passengerCount > 8 && in_array(strtolower($vehicle->vehicle_type->value ?? $vehicle->vehicle_type), ['van', 'minibus', 'bus'])) {
                $typeMatch = 20;
            }

            // Luggage match (10 pts)
            $luggageScore = 6;
            if ($luggageSize === 'heavy' && in_array(strtolower($vehicle->vehicle_type->value ?? $vehicle->vehicle_type), ['van', 'minibus', 'bus'])) {
                $luggageScore = 10;
            } elseif ($luggageSize === 'light' && strtolower($vehicle->vehicle_type->value ?? $vehicle->vehicle_type) === 'car') {
                $luggageScore = 10;
            }

            // Distance efficiency (10 pts)
            $distScore = 5;
            if ($distanceKm > 100 && in_array(strtolower($vehicle->vehicle_type->value ?? $vehicle->vehicle_type), ['van', 'suv'])) {
                $distScore = 10;
            }

            $score = $seatScore + $budgetScore + $typeMatch + $luggageScore + $distScore;
            $confidence = min(95, round($score * 0.95 + ($seatScore > 0 ? 5 : 0)));

            // Reasons text
            $reasons = [
                "Fits {$vehicle->seating_capacity} passengers ({$passengerCount} requested).",
                "Daily price LKR " . number_format($vehicle->price_per_day) . " compared to budget of LKR " . number_format($budget) . ".",
            ];
            
            if ($typeMatch >= 20) {
                $reasons[] = "Vehicle type (" . ucfirst($vehicle->vehicle_type->value ?? $vehicle->vehicle_type) . ") is ideal for this passenger size.";
            }
            if ($distScore === 10) {
                $reasons[] = "Great comfort and space for long distance trip ({$distanceKm} km).";
            }

            $recommendations[] = [
                'vehicle' => new VehicleResource($vehicle),
                'score' => (int)round($score),
                'confidence' => (int)$confidence,
                'reasons' => $reasons,
                'rank' => 1, // Will rank after sorting
            ];
        }

        // Sort by score desc
        usort($recommendations, function ($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        // Add rank
        foreach ($recommendations as $index => &$rec) {
            $rec['rank'] = $index + 1;
        }

        return $this->success($recommendations, 'Vehicle recommendations computed.');
    }

    /**
     * AI driver matching scoring engine.
     */
    public function driverMatching(Request $request): JsonResponse
    {
        $request->validate([
            'pickup_location' => 'required|array',
            'pickup_location.lat' => 'required|numeric',
            'pickup_location.lng' => 'required|numeric',
        ]);

        $pickupLat = $request->input('pickup_location.lat');
        $pickupLng = $request->input('pickup_location.lng');
        $distanceKm = $request->get('distance_km', 100);

        // Fetch approved available drivers
        $drivers = DriverApplication::with(['user', 'area'])
            ->where('application_status', 'approved')
            ->get();

        $matches = [];

        foreach ($drivers as $driver) {
            // Distance calculation
            $driverLat = $driver->location_lat ?? 6.9271; // Colombo default fallback
            $driverLng = $driver->location_lng ?? 79.8612;
            
            $dist = $this->haversine($pickupLat, $pickupLng, $driverLat, $driverLng);
            
            // Distance score (20%)
            $distScore = max(0, 100 - $dist * 6);

            // Experience score (25%)
            $expScore = min(100, ($driver->years_of_experience / 15) * 100);

            // Rating score (35%)
            $ratingScore = ($driver->rating / 5) * 100;

            // Availability score (20%)
            $availScore = $driver->availability_status === 'available' ? 100 : 20;

            $finalScore = round(
                $distScore * 0.20 +
                $expScore * 0.25 +
                $ratingScore * 0.35 +
                $availScore * 0.20
            );

            $estimatedArrivalMin = max(5, round($dist * 2.5));

            $matches[] = [
                'driver' => new DriverProfileResource($driver),
                'distance_score' => (int)round($distScore),
                'experience_score' => (int)round($expScore),
                'rating_score' => (int)round($ratingScore),
                'availability_score' => (int)round($availScore),
                'final_score' => (int)$finalScore,
                'distance_km' => round($dist, 1),
                'reason' => "Recommended: " . round($dist, 1) . " km from pickup, " . $driver->years_of_experience . "y experience, " . number_format($driver->rating, 1) . " star rating.",
                'estimated_arrival_min' => (int)$estimatedArrivalMin,
            ];
        }

        // Sort by final score desc
        usort($matches, function ($a, $b) {
            return $b['final_score'] <=> $a['final_score'];
        });

        return $this->success($matches, 'Driver matches computed.');
    }

    /**
     * AI trip planner chatbot.
     */
    public function chat(Request $request): JsonResponse
    {
        $request->validate(['message' => 'required|string']);
        $msg = strtolower($request->message);

        // Rule-based chatbot reply helper
        if (strpos($msg, 'hello') !== false || strpos($msg, 'hi') !== false) {
            $reply = "Hello! I am your RideHub AI Assistant. I can recommend vehicles for rent, match you with professional drivers, or help plan routes for your trip across Sri Lanka. What are you planning?";
        } elseif (strpos($msg, 'van') !== false || strpos($msg, 'group') !== false || strpos($msg, '10 people') !== false) {
            $reply = "For group travel or passenger sizes above 6, I highly recommend checking out our vans like the Toyota HiAce or Dolphin, which comfortable accommodate up to 14 passengers. You can search them in our Vehicles section.";
        } elseif (strpos($msg, 'kandy') !== false || strpos($msg, 'route') !== false || strpos($msg, 'colombo') !== false) {
            $reply = "The route from Colombo to Kandy is roughly 115 km via the A1 highway, taking about 3 hours depending on traffic. I recommend hiring a driver with experience in hill country roads for a smoother trip!";
        } else {
            $reply = "I understand you need assistance with booking your ride. Let me know the pickup town, destination, and passenger count, and I will recommend the best vehicle and matching drivers for your Sri Lankan journey!";
        }

        return $this->success([
            'reply' => $reply,
            'suggestions' => [
                "Recommend a van for 10 people",
                "Best route from Colombo to Galle",
                "How do I become a driver?"
            ]
        ], 'AI Chat response generated.');
    }

    /**
     * Haversine distance helper.
     */
    private function haversine($lat1, $lon1, $lat2, $lon2): float
    {
        $earthRadius = 6371; // km
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat/2) * sin($dLat/2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon/2) * sin($dLon/2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        return $earthRadius * $c;
    }
}

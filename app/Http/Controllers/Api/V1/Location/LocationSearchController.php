<?php

namespace App\Http\Controllers\Api\V1\Location;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\DriverApplication;
use App\Models\LocationTracking;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class LocationSearchController extends BaseApiController
{
    /**
     * Geocoding search (forward lookup).
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate(['q' => 'required|string']);
        $query = $request->q;
        $limit = $request->get('limit', 5);

        try {
            $url = "https://nominatim.openstreetmap.org/search?q=" . urlencode($query) . "&format=json&limit={$limit}&countrycodes=lk";
            $response = Http::withHeaders([
                'User-Agent' => 'RideHub-SriLanka-App'
            ])->get($url);

            if ($response->successful()) {
                $results = $response->json();
                $formatted = array_map(function ($r) {
                    return [
                        'place_id' => (string)$r['place_id'],
                        'display_name' => $r['display_name'],
                        'lat' => (double)$r['lat'],
                        'lng' => (double)$r['lon'],
                    ];
                }, $results);

                return $this->success($formatted, 'Locations search successful.');
            }
        } catch (\Throwable $e) {
            // Fallback to empty if Nominatim is down
        }

        return $this->success([], 'Locations search empty.');
    }

    /**
     * Reverse geocoding lookup.
     */
    public function reverse(Request $request): JsonResponse
    {
        $request->validate([
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
        ]);

        $lat = $request->lat;
        $lng = $request->lng;

        try {
            $url = "https://nominatim.openstreetmap.org/reverse?lat={$lat}&lon={$lng}&format=json";
            $response = Http::withHeaders([
                'User-Agent' => 'RideHub-SriLanka-App'
            ])->get($url);

            if ($response->successful()) {
                $result = $response->json();
                return $this->success([
                    'address' => $result['display_name'] ?? "{$lat}, {$lng}"
                ], 'Reverse geocode successful.');
            }
        } catch (\Throwable $e) {
            // Fallback
        }

        return $this->success([
            'address' => "{$lat}, {$lng}"
        ], 'Reverse geocode successful.');
    }

    /**
     * Calculate route distance and duration via OSRM.
     */
    public function distance(Request $request): JsonResponse
    {
        $request->validate([
            'from_lat' => 'required|numeric',
            'from_lng' => 'required|numeric',
            'to_lat' => 'required|numeric',
            'to_lng' => 'required|numeric',
        ]);

        $fromLat = $request->from_lat;
        $fromLng = $request->from_lng;
        $toLat = $request->to_lat;
        $toLng = $request->to_lng;

        try {
            // OSRM format: driving/lng,lat;lng,lat
            $url = "http://router.project-osrm.org/route/v1/driving/{$fromLng},{$fromLat};{$toLng},{$toLat}?overview=false";
            $response = Http::get($url);

            if ($response->successful()) {
                $data = $response->json();
                if (!empty($data['routes'])) {
                    $route = $data['routes'][0];
                    $distanceKm = $route['distance'] / 1000;
                    $durationMin = $route['duration'] / 60;

                    $hours = floor($durationMin / 60);
                    $mins = round($durationMin % 60);
                    $durationLabel = $hours > 0 
                        ? "{$hours} hr" . ($hours > 1 ? 's' : '') . ($mins > 0 ? " {$mins} min" : '')
                        : "{$mins} min";

                    return $this->success([
                        'distance_km' => round($distanceKm, 1),
                        'duration_minutes' => (int)round($durationMin),
                        'duration_label' => $durationLabel,
                    ], 'Route calculation successful.');
                }
            }
        } catch (\Throwable $e) {
            // Fallback to Haversine
        }

        // Haversine fallback estimate
        $distanceKm = $this->haversine($fromLat, $fromLng, $toLat, $toLng) * 1.3; // 1.3x road winding factor
        $durationMin = ($distanceKm / 40) * 60; // 40 km/h average speed
        $hours = floor($durationMin / 60);
        $mins = round($durationMin % 60);
        $durationLabel = $hours > 0 
            ? "{$hours} hr" . ($hours > 1 ? 's' : '') . ($mins > 0 ? " {$mins} min" : '')
            : "{$mins} min";

        return $this->success([
            'distance_km' => round($distanceKm, 1),
            'duration_minutes' => (int)round($durationMin),
            'duration_label' => $durationLabel,
        ], 'Route calculation successful (estimated).');
    }

    /**
     * Update driver current GPS location.
     */
    public function updateDriverLocation(Request $request, string $driverUuid): JsonResponse
    {
        $request->validate([
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
        ]);

        $driver = DriverApplication::where('uuid', $driverUuid)->first();
        if (!$driver) {
            return $this->error('Driver profile not found.', null, 404);
        }

        $lat = $request->lat;
        $lng = $request->lng;

        // Update driver location coordinates
        $driver->location_lat = $lat;
        $driver->location_lng = $lng;
        $driver->save();

        // Track in history
        LocationTracking::create([
            'uuid' => (string) Str::uuid(),
            'entity_type' => 'driver',
            'entity_id' => $driver->id,
            'entity_uuid' => $driver->uuid,
            'lat' => $lat,
            'lng' => $lng,
            'recorded_at' => now(),
        ]);

        return $this->success(null, 'Driver location updated.');
    }

    /**
     * Get driver current GPS location.
     */
    public function getDriverLocation(string $driverUuid): JsonResponse
    {
        $driver = DriverApplication::where('uuid', $driverUuid)->first();
        if (!$driver) {
            return $this->error('Driver profile not found.', null, 404);
        }

        return $this->success([
            'lat' => $driver->location_lat ? (double)$driver->location_lat : null,
            'lng' => $driver->location_lng ? (double)$driver->location_lng : null,
            'recorded_at' => $driver->updated_at ? $driver->updated_at->toDateTimeString() : null,
        ], 'Driver location coordinates retrieved.');
    }

    /**
     * Haversine distance formula.
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

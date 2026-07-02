<?php

namespace App\Services\Location;

use App\Models\Area;
use App\Models\City;
use App\Models\Country;
use App\Models\District;
use App\Models\Province;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class LocationService
{
    /**
     * Get all active countries.
     */
    public function getCountries(): Collection
    {
        return Country::where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    /**
     * Get provinces by country UUID.
     */
    public function getProvinces(string $countryUuid): Collection
    {
        $country = Country::where('uuid', $countryUuid)
            ->where('is_active', true)
            ->firstOrFail();

        return $country->provinces()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    /**
     * Get districts by province UUID.
     */
    public function getDistricts(string $provinceUuid): Collection
    {
        $province = Province::where('uuid', $provinceUuid)
            ->where('is_active', true)
            ->firstOrFail();

        return $province->districts()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    /**
     * Get cities by district UUID.
     */
    public function getCities(string $districtUuid): Collection
    {
        $district = District::where('uuid', $districtUuid)
            ->where('is_active', true)
            ->firstOrFail();

        return $district->cities()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    /**
     * Get areas by city UUID.
     */
    public function getAreas(string $cityUuid): Collection
    {
        $city = City::where('uuid', $cityUuid)
            ->where('is_active', true)
            ->firstOrFail();

        return $city->areas()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }
}
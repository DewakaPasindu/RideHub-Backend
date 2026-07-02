<?php

namespace App\Http\Controllers\Api\Location;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Resources\AreaResource;
use App\Http\Resources\CityResource;
use App\Http\Resources\CountryResource;
use App\Http\Resources\DistrictResource;
use App\Http\Resources\ProvinceResource;
use App\Services\Location\LocationService;
use Illuminate\Http\JsonResponse;

class LocationController extends BaseApiController
{
    public function __construct(
        private readonly LocationService $locationService
    ) {
    }

    /**
     * Get all active countries.
     */
    public function countries(): JsonResponse
    {
        $countries = $this->locationService->getCountries();

        return $this->success(
            CountryResource::collection($countries),
            'Countries retrieved successfully.'
        );
    }

    /**
     * Get provinces by country UUID.
     */
    public function provinces(string $countryUuid): JsonResponse
    {
        $provinces = $this->locationService->getProvinces($countryUuid);

        return $this->success(
            ProvinceResource::collection($provinces),
            'Provinces retrieved successfully.'
        );
    }

    /**
     * Get districts by province UUID.
     */
    public function districts(string $provinceUuid): JsonResponse
    {
        $districts = $this->locationService->getDistricts($provinceUuid);

        return $this->success(
            DistrictResource::collection($districts),
            'Districts retrieved successfully.'
        );
    }

    /**
     * Get cities by district UUID.
     */
    public function cities(string $districtUuid): JsonResponse
    {
        $cities = $this->locationService->getCities($districtUuid);

        return $this->success(
            CityResource::collection($cities),
            'Cities retrieved successfully.'
        );
    }

    /**
     * Get areas by city UUID.
     */
    public function areas(string $cityUuid): JsonResponse
    {
        $areas = $this->locationService->getAreas($cityUuid);

        return $this->success(
            AreaResource::collection($areas),
            'Areas retrieved successfully.'
        );
    }
}
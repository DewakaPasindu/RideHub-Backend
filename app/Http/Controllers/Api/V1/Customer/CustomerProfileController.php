<?php

namespace App\Http\Controllers\Api\V1\Customer;

use App\Http\Controllers\Api\BaseApiController;
// use App\Http\Requests\Customer\StoreCustomerProfileRequest;
use App\Http\Requests\Customer\UpdateCustomerProfileRequest;
use App\Http\Resources\CustomerProfileResource;
use App\Services\Customer\CustomerProfileService;

class CustomerProfileController extends BaseApiController
{
    protected CustomerProfileService $customerProfileService;

    public function __construct(CustomerProfileService $customerProfileService)
    {
        $this->customerProfileService = $customerProfileService;
    }

    /**
     * Create customer profile.
     */
    // public function store(StoreCustomerProfileRequest $request)
    // {
    //     $profile = $this->customerProfileService->create($request->validated());

    //     return $this->success(
    //         new CustomerProfileResource($profile),
    //         'Customer profile created successfully.',
    //         201
    //     );
    // }

    /**
     * Get authenticated user's profile.
     */
    public function show()
    {
        $profile = $this->customerProfileService->get();

        if (!$profile) {
            return $this->error(
                'Customer profile not found.',
                404
            );
        }

        return $this->success(
            new CustomerProfileResource($profile),
            'Customer profile retrieved successfully.'
        );
    }

    /**
     * Update customer profile.
     */
    public function update(UpdateCustomerProfileRequest $request)
    {
        $profile = $this->customerProfileService->update($request->validated());

        return $this->success(
            new CustomerProfileResource($profile),
            'Customer profile updated successfully.'
        );
    }

    /**
     * Delete customer profile.
     */
    // public function destroy()
    // {
    //     $this->customerProfileService->delete();

    //     return $this->success(
    //         null,
    //         'Customer profile deleted successfully.'
    //     );
    // }
}
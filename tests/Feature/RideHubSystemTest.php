<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Vehicle;
use App\Models\DriverApplication;
use App\Models\VehicleOwnerProfile;
use App\Models\Booking;
use App\Models\Review;
use Database\Seeders\RoleSeeder;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class RideHubSystemTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed roles & permissions
        $this->seed(RoleSeeder::class);
        $this->seed(PermissionSeeder::class);
        $this->seed(RolePermissionSeeder::class);
    }

    /**
     * Test ping route.
     */
    public function test_ping_route(): void
    {
        $response = $this->getJson('/api/v1/ping');
        $response->assertStatus(200)
                 ->assertJson(['status' => 'ok']);
    }

    /**
     * Test user registration, login, and customer profile updates.
     */
    public function test_user_registration_login_and_customer_profile(): void
    {
        // 1. Register User
        $registerData = [
            'first_name' => 'Nuwan',
            'last_name' => 'Perera',
            'email' => 'nuwan@example.com',
            'phone' => '0771234567',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
        ];

        $response = $this->postJson('/api/v1/auth/register', $registerData);
        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'success',
                     'data' => [
                         'user' => ['uuid', 'email', 'first_name', 'last_name'],
                         'token'
                     ]
                 ]);

        $token = $response->json('data.token');

        // 2. Fetch profile via /auth/me
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
                         ->getJson('/api/v1/auth/me');
        $response->assertStatus(200)
                 ->assertJsonPath('data.email', 'nuwan@example.com');

        // 3. Update customer profile
        $profileData = [
            'gender' => 'male',
            'date_of_birth' => '1995-05-15',
            'nic_passport' => '199512345678',
            'emergency_contact_name' => 'Saman Perera',
            'emergency_contact_phone' => '0777654321',
            'preferred_language' => 'Sinhala',
        ];

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
                         ->putJson('/api/v1/customer/profile', $profileData);
        $response->assertStatus(200)
                 ->assertJsonPath('data.nic_passport', '199512345678')
                 ->assertJsonPath('data.profile_completed', true);
    }

    /**
     * Test driver application, registration, listing, and admin verification workflow.
     */
    public function test_driver_application_and_approval(): void
    {
        // 1. Create a user
        $user = User::factory()->create();
        $user->assignRole('Customer');
        $token = $user->createToken('Test')->plainTextToken;

        // 2. Register as Driver
        $driverData = [
            'license_number' => 'B78945612',
            'experience_years' => 7,
            'phone' => '0779876543',
            'address' => '123 Main St, Kandy',
            'nearest_town' => 'Kandy',
            'skills' => ['Long Distance', 'Manual Transmission'],
        ];

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
                         ->postJson('/api/v1/drivers/register', $driverData);
        $response->assertStatus(201)
                 ->assertJsonPath('data.driving_license_number', 'B78945612')
                 ->assertJsonPath('data.approval_status', 'pending');

        $driverUuid = $response->json('data.uuid');

        // Verify role updated
        $this->assertTrue($user->hasRole('Driver'));

        // 3. Admin pending count
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
                         ->getJson('/api/v1/admin/drivers/pending-count');
        $response->assertStatus(200)
                 ->assertJsonPath('data.count', 1);

        // 4. Admin approves driver
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
                         ->postJson("/api/v1/admin/drivers/{$driverUuid}/approve");
        $response->assertStatus(200);

        // 5. Check in public list
        $response = $this->getJson('/api/v1/drivers');
        $response->assertStatus(200)
                 ->assertJsonCount(1, 'data');
    }

    /**
     * Test vehicle owner profile, verification, and adding vehicle properties.
     */
    public function test_vehicle_owner_profile_registration_approval_and_adding_vehicle(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Customer');
        $token = $user->createToken('Test')->plainTextToken;

        // 1. Create vehicle owner profile
        $ownerProfileData = [
            'owner_type' => 'individual',
            'nic_passport' => '199012345678',
            'phone' => '0779998887',
            'address' => '456 Galle Rd, Colombo',
        ];

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
                         ->postJson('/api/v1/vehicle-owner/profile', $ownerProfileData);
        $response->assertStatus(201)
                 ->assertJsonPath('data.phone', '0779998887');

        $profileUuid = $response->json('data.uuid');

        // 2. Add vehicle before profile is approved (should fail!)
        $vehicleData = [
            'registration_number' => 'WP-CAD-8956',
            'make' => 'Toyota',
            'model' => 'Allion',
            'manufacturing_year' => 2018,
            'color' => 'Silver',
            'vehicle_type' => 'car',
            'fuel_type' => 'petrol',
            'transmission' => 'automatic',
            'seating_capacity' => 5,
            'doors' => 4,
            'chassis_number' => 'CH12345678ALLION',
            'engine_number' => 'EN98765432ALLION',
            'price_per_day' => 8500,
            'nearest_town' => 'Colombo',
        ];

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
                         ->postJson('/api/v1/vehicle-owner/vehicles', $vehicleData);
        $response->assertStatus(422); // Validation exception: Profile must be approved

        // 3. Admin approves profile
        $ownerProfile = VehicleOwnerProfile::where('uuid', $profileUuid)->first();
        $ownerProfile->application_status = 'approved';
        $ownerProfile->save();

        // 4. Register vehicle again (should succeed!)
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
                         ->postJson('/api/v1/vehicle-owner/vehicles', $vehicleData);
        $response->assertStatus(201)
                 ->assertJsonPath('data.registration_number', 'WP-CAD-8956')
                 ->assertJsonPath('data.price_per_day', 8500);

        $vehicleUuid = $response->json('data.uuid');

        // 5. Admin approves vehicle
        $vehicle = Vehicle::where('uuid', $vehicleUuid)->first();
        $vehicle->application_status = 'approved';
        $vehicle->save();

        // 6. Retrieve list of vehicles for owner
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
                         ->getJson('/api/v1/vehicle-owner/vehicles');
        $response->assertStatus(200)
                 ->assertJsonCount(1, 'data');
    }

    /**
     * Test booking flow, cancellation, and trip status transitions.
     */
    public function test_booking_creation_cancellation_and_trip_status(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('Test')->plainTextToken;

        // Seed a vehicle directly
        $owner = User::factory()->create();
        $ownerProfile = VehicleOwnerProfile::create([
            'uuid' => (string)Str::uuid(),
            'user_id' => $owner->id,
            'owner_type' => 'individual',
            'nic_passport' => '198012345678',
            'phone' => '0776543210',
            'address' => 'Test Address',
            'application_status' => 'approved',
        ]);

        $vehicle = Vehicle::create([
            'uuid' => (string)Str::uuid(),
            'vehicle_owner_profile_id' => $ownerProfile->id,
            'registration_number' => 'WP-CAB-1234',
            'make' => 'Toyota',
            'model' => 'Prius',
            'manufacturing_year' => 2019,
            'color' => 'White',
            'vehicle_type' => 'car',
            'fuel_type' => 'hybrid',
            'transmission' => 'automatic',
            'seating_capacity' => 5,
            'doors' => 4,
            'chassis_number' => 'CH11112222',
            'engine_number' => 'EN33334444',
            'price_per_day' => 12000,
            'nearest_town' => 'Colombo',
            'application_status' => 'approved',
        ]);

        // 1. Create booking
        $bookingData = [
            'booking_type' => 'vehicle',
            'vehicle_id' => $vehicle->uuid,
            'target_name' => 'Toyota Prius',
            'start_date' => now()->addDays(2)->format('Y-m-d'),
            'end_date' => now()->addDays(5)->format('Y-m-d'),
            'pickup_location' => 'Colombo Airport',
            'total_amount' => 36000,
        ];

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
                         ->postJson('/api/v1/bookings', $bookingData);
        $response->assertStatus(201)
                 ->assertJsonPath('data.status', 'pending');

        $bookingUuid = $response->json('data.uuid');

        // 2. Admin approves booking
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
                         ->postJson("/api/v1/admin/bookings/{$bookingUuid}/approve");
        $response->assertStatus(200);

        // 3. Start trip
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
                         ->postJson("/api/v1/admin/bookings/{$bookingUuid}/start-trip");
        $response->assertStatus(200);

        // 4. Complete trip
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
                         ->postJson("/api/v1/admin/bookings/{$bookingUuid}/complete-trip");
        $response->assertStatus(200);

        // Check stats
        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
                         ->getJson('/api/v1/admin/bookings/status-counts');
        $response->assertStatus(200)
                 ->assertJsonPath('data.completed', 1);
    }

    /**
     * Test booking date conflict validations.
     */
    public function test_booking_conflict_prevention(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('Test')->plainTextToken;

        // Seed a vehicle
        $owner = User::factory()->create();
        $ownerProfile = VehicleOwnerProfile::create([
            'uuid' => (string)Str::uuid(),
            'user_id' => $owner->id,
            'owner_type' => 'individual',
            'nic_passport' => '198012345678',
            'phone' => '0776543210',
            'address' => 'Test Address',
            'application_status' => 'approved',
        ]);

        $vehicle = Vehicle::create([
            'uuid' => (string)Str::uuid(),
            'vehicle_owner_profile_id' => $ownerProfile->id,
            'registration_number' => 'WP-CAB-1234',
            'make' => 'Toyota',
            'model' => 'Prius',
            'manufacturing_year' => 2019,
            'color' => 'White',
            'vehicle_type' => 'car',
            'fuel_type' => 'hybrid',
            'transmission' => 'automatic',
            'seating_capacity' => 5,
            'doors' => 4,
            'chassis_number' => 'CH11112222',
            'engine_number' => 'EN33334444',
            'price_per_day' => 12000,
            'nearest_town' => 'Colombo',
            'application_status' => 'approved',
        ]);

        // Create an active booking
        Booking::create([
            'uuid' => (string)Str::uuid(),
            'user_id' => $user->id,
            'booking_type' => 'vehicle',
            'vehicle_id' => $vehicle->id,
            'start_date' => now()->addDays(2)->format('Y-m-d'),
            'end_date' => now()->addDays(5)->format('Y-m-d'),
            'pickup_location' => 'Colombo',
            'total_amount' => 36000,
            'status' => 'approved',
        ]);

        // Try booking overlapping dates (should fail!)
        $bookingData = [
            'booking_type' => 'vehicle',
            'vehicle_id' => $vehicle->uuid,
            'start_date' => now()->addDays(3)->format('Y-m-d'),
            'end_date' => now()->addDays(4)->format('Y-m-d'),
            'pickup_location' => 'Airport',
            'total_amount' => 12000,
        ];

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
                         ->postJson('/api/v1/bookings', $bookingData);
        $response->assertStatus(422);
    }

    /**
     * Test review posting and stats calculation.
     */
    public function test_reviews_ratings_and_stats(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('Test')->plainTextToken;

        // Seed vehicle
        $owner = User::factory()->create();
        $ownerProfile = VehicleOwnerProfile::create([
            'uuid' => (string)Str::uuid(),
            'user_id' => $owner->id,
            'owner_type' => 'individual',
            'nic_passport' => '198012345678',
            'phone' => '0776543210',
            'address' => 'Test Address',
            'application_status' => 'approved',
        ]);

        $vehicle = Vehicle::create([
            'uuid' => (string)Str::uuid(),
            'vehicle_owner_profile_id' => $ownerProfile->id,
            'registration_number' => 'WP-CAB-1234',
            'make' => 'Toyota',
            'model' => 'Prius',
            'manufacturing_year' => 2019,
            'color' => 'White',
            'vehicle_type' => 'car',
            'fuel_type' => 'hybrid',
            'transmission' => 'automatic',
            'seating_capacity' => 5,
            'doors' => 4,
            'chassis_number' => 'CH11112222',
            'engine_number' => 'EN33334444',
            'price_per_day' => 12000,
            'nearest_town' => 'Colombo',
            'application_status' => 'approved',
        ]);

        $booking = Booking::create([
            'uuid' => (string)Str::uuid(),
            'user_id' => $user->id,
            'booking_type' => 'vehicle',
            'vehicle_id' => $vehicle->id,
            'start_date' => now()->addDays(2)->format('Y-m-d'),
            'end_date' => now()->addDays(3)->format('Y-m-d'),
            'pickup_location' => 'Colombo',
            'total_amount' => 12000,
            'status' => 'completed',
        ]);

        // Submit review
        $reviewData = [
            'booking_id' => $booking->uuid,
            'target_type' => 'vehicle',
            'rating' => 5,
            'comment' => 'Fabulous ride, very clean car.',
        ];

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
                         ->postJson('/api/v1/reviews', $reviewData);
        $response->assertStatus(201)
                 ->assertJsonPath('data.rating', 5);

        // Fetch stats
        $response = $this->getJson("/api/v1/vehicles/{$vehicle->uuid}/reviews/stats");
        $response->assertStatus(200)
                 ->assertJsonPath('data.avg', 5)
                 ->assertJsonPath('data.count', 1);
    }
}

<?php

namespace App\Services\Auth;

use App\Models\User;
// use App\Models\CustomerProfile;
use App\Core\Enums\ActivityAction;
use App\Services\ActivityLog\ActivityLogService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthService
{
    public function __construct(
        private readonly ActivityLogService $activityLogService
    ) {}
    /**
     * Register User
     */
    public function register(array $data): array
    {
        $user = DB::transaction(function () use ($data) {

            $user = User::create([

                'uuid' => Str::uuid(),

                'first_name' => $data['first_name'],

                'last_name' => $data['last_name'],

                'email' => $data['email'],

                'phone' => $data['phone'] ?? null,

                'password' => Hash::make($data['password']),

                'status' => 'active',

            ]);

            $user->assignRole('Customer');
            $user->customerProfile()->create([]);

            $this->activityLogService->log(
                action: ActivityAction::USER_REGISTERED,
                description: 'New customer registered.',
                subject: $user,
                properties: [
                    'email' => $user->email,
                    'role' => 'Customer',
                ]
            );

            return $user;
        });

        $token = $user->createToken('RideHub')->plainTextToken;

        return [

            'user' => $user,

            'token' => $token,

        ];

    }

    /**
     * Login
     */
    public function login(array $data): array
    {
        $user = User::where('email',$data['email'])->first();

        if(!$user || !Hash::check($data['password'],$user->password))
        {
            abort(401,'Invalid Credentials');
        }

        $user->tokens()->delete();

        $token = $user->createToken('RideHub')->plainTextToken;

        return [

            'user'=>$user,

            'token'=>$token,

        ];
    }

    /**
     * Logout
     */
    public function logout(User $user): void
    {
        $user->currentAccessToken()->delete();
    }
}
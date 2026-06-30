<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthService
{
    /**
     * Register User
     */
    public function register(array $data): array
    {
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

        $token = $user->createToken('RideHub')->plainTextToken;

        return [

            'user'=>$user,

            'token'=>$token,

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
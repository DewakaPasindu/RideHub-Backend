<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Services\Auth\AuthService;
use Illuminate\Http\Request;

class AuthController extends BaseApiController
{
    public function __construct(
        private readonly AuthService $authService
    ) {}

    /**
     * Register
     */
    public function register(RegisterRequest $request)
    {
        $result = $this->authService->register($request->validated());

        return $this->success([

            'user' => new UserResource($result['user']),

            'token' => $result['token']

        ], 'Registration successful.', 201);
    }

    /**
     * Login
     */
    public function login(LoginRequest $request)
    {
        $result = $this->authService->login($request->validated());

        return $this->success([

            'user' => new UserResource($result['user']),

            'token' => $result['token']

        ], 'Login successful.');
    }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        $this->authService->logout($request->user());

        return $this->success(null, 'Logout successful.');
    }

    /**
     * Current User
     */
    public function me(Request $request)
    {
        return $this->success(

            new UserResource($request->user()),

            'Authenticated user.'

        );
    }
}
<?php

namespace App\Http\Controllers;

use App\Services\AuthService;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Helpers\ApiResponse;

class AuthController extends Controller
{
    protected AuthService $authService;

    public function __construct(AuthService $service)
    {
        $this->authService = $service;
    }

    public function login(LoginRequest $request)
    {
        try {
            $data = $this->authService->login(
                $request->validated()['email'],
                $request->validated()['password']
            );

            return ApiResponse::success($data, 'Login successful');
        } catch (\Exception $e) {
            return ApiResponse::error($e->getMessage(), null, 500 ?: 401);
        }
    }

    public function register(RegisterRequest $request)
{
    try {
        $data = $this->authService->register($request->validated());

        return ApiResponse::success($data, 'Registration successful');
    } catch (\Exception $e) {
        return ApiResponse::error($e->getMessage(), null, 500);
    }
}

}
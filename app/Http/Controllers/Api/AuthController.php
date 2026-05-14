<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        protected AuthService $authService,
    ) {}

    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['sometimes', 'string', 'max:255'],
        ]);

        $result = $this->authService->attemptTokenLogin(
            $data['email'],
            $data['password'],
            $data['device_name'] ?? 'spa',
        );

        return response()->json([
            'token' => $result['token'],
            'user' => $result['user']->toApiArray(),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $this->authService->revokeCurrentToken($request->user());

        return response()->json(['message' => 'Logged out']);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'user' => $request->user()->toApiArray(),
        ]);
    }
}

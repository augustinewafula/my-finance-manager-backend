<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function user(Request $request): User
    {
        return $request->user();
    }

    public function login(LoginRequest $request, UserService $userService): JsonResponse
    {
        $token = $userService->login($request->email);
        return response()->json(['token' => $token]);
    }

    public function logout(UserService $userService): JsonResponse
    {
        $userService->logout(auth()->user());
        return response()->json(['message' => 'Logout successful']);
    }

}

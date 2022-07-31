<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
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

    public function register(RegisterRequest $request, UserService $userService): JsonResponse
    {
        $user = $userService->store($request->name, $request->email, $request->password);
        $token = $userService->login($user->email);
        return response()->json(['token' => $token], 201);
    }

    public function logout(UserService $userService): JsonResponse
    {
        $userService->logout(auth()->user());
        return response()->json(['message' => 'Logout successful']);
    }

}

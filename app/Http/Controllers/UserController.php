<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Services\UserService;

class UserController extends Controller
{
    public function login(LoginRequest $request, UserService $userService)
    {
        $token = $userService->login($request->email);
        return response()->json(['token' => $token]);
    }

}

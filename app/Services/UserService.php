<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;

class UserService
{

    public function login($email): string
    {
        $user = User::where('email', $email)->first();
        return $user->createToken('access_token')->plainTextToken;
    }

    public function logout($user): void
    {
        $user->currentAccessToken()->delete();
    }

}

<?php

namespace App\Services;

use App\Models\User;

class UserService
{
    public function login($email): string
    {
        $user = User::where('email', $email)->first();
        return $user->createToken('access_token')->plainTextToken;
    }

}

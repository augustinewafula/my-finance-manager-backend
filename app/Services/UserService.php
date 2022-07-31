<?php

namespace App\Services;

use App\Models\TransactionCategory;
use App\Models\TransactionSubCategory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserService
{

    public function login($email): string
    {
        $user = User::whereEmail($email)->first();

        return $user->createToken('access_token')->plainTextToken;
    }

    public function store(string $name, string $email, string $password): User
    {
        return DB::transaction(function () use ($name, $email, $password) {
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => bcrypt($password),
            ]);
            $this->assignDefaultTransactionCategoriesAndSubCategories($user);

            return $user;
        });
    }

    public function assignDefaultTransactionCategoriesAndSubCategories($user): void
    {
        $defaultTransactionCategories = $this->getDefaultTransactionCategories();
        $defaultTransactionSubCategories = $this->getDefaultTransactionSubCategories();

        $this->assignTransactionCategoriesToUser($user, $defaultTransactionCategories);
        $this->assignTransactionSubCategoriesToUser($user, $defaultTransactionSubCategories);
    }

    public function getDefaultTransactionCategories()
    {
        return TransactionCategory::default()
            ->get();
    }

    public function getDefaultTransactionSubCategories()
    {
        return TransactionSubCategory::default()
            ->get();
    }

    public function assignTransactionCategoriesToUser($user, $transactionCategories): void
    {
        $user->transactionCategories()->attach($transactionCategories);
    }

    public function assignTransactionSubCategoriesToUser($user, $transactionSubCategories): void
    {
        $user->transactionSubCategories()->attach($transactionSubCategories);
    }

    public function logout($user): void
    {
        $user->currentAccessToken()->delete();
    }

}

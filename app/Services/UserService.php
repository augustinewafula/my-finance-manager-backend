<?php

namespace App\Services;

use App\Models\TransactionCategory;
use App\Models\TransactionSubCategory;
use App\Models\User;
use Illuminate\Http\Request;

class UserService
{

    public function login($email): string
    {
        $user = User::whereEmail($email)->first();
        return $user->createToken('access_token')->plainTextToken;
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

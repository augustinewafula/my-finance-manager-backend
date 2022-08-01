<?php

namespace App\Services;

use App\Models\TransactionCategory;
use Illuminate\Support\Facades\DB;

class TransactionCategoryService
{
    public function store($name): TransactionCategory
    {
        return DB::transaction(static function () use ($name) {
            $transactionCategory = TransactionCategory::create([
                'name' => $name,
            ]);
            $userService = new UserService();
            $userService->assignTransactionCategoriesToUser(auth()->user(), $transactionCategory->id);

            return $transactionCategory;
        });
    }

}

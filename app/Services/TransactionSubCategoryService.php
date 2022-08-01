<?php

namespace App\Services;

use App\Models\TransactionSubCategory;
use Illuminate\Support\Facades\DB;

class TransactionSubCategoryService
{
    public function store($name, $transaction_category_id): TransactionSubCategory
    {
        return DB::transaction(static function () use ($name, $transaction_category_id) {
            $transactionSubCategory = TransactionSubCategory::create([
                'name' => $name,
                'transaction_category_id' => $transaction_category_id,
            ]);
            $userService = new UserService();
            $userService->assignTransactionSubCategoriesToUser(auth()->user(), $transactionSubCategory->id);

            return $transactionSubCategory;
        });
    }

}

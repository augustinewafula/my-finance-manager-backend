<?php

namespace App\Actions;

use App\Models\TransactionCategory;
use Illuminate\Support\Str;

class IdentifyMpesaTransactionCategory
{
    public function execute(string $transactionType): array
    {
        $transactionType = Str::of($transactionType)->lower()->toString();

        if (Str::of($transactionType)->contains(['hotel', 'restaurant', 'lodging'])) {
            $category_id = TransactionCategory::where('name', 'Food')->value('id');
            return [
                'category_id' => $category_id,
                'sub_category_id' => null,
            ];
        }

        if (Str::of($transactionType)->contains(['supermarket', 'groceries', 'fashion', 'electronics'])) {
            $category_id = TransactionCategory::where('name', 'Shopping')->value('id');
            return [
                'category_id' => $category_id,
                'sub_category_id' => null,
            ];
        }

        $category_id = TransactionCategory::where('name', 'Other')->value('id');
        return [
            'category_id' => $category_id,
            'sub_category_id' => null,
        ];
    }

}

<?php

namespace App\Actions;

use App\Models\IdentifiedTransactionCategory;
use App\Models\TransactionCategory;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class IdentifyMpesaTransactionCategory
{
    public function execute(string $transactionType): array
    {
        $transactionType = Str::of($transactionType)
            ->lower()
            ->toString();
        Log::info('type: '.$transactionType);
        if ($category = $this->existsInIdentifiedCategories($transactionType)) {
            return $category;
        }

        $preIdentifiedTransactionCategories = $this->getPreIdentifiedTransactionCategories();

        $category = [];
        foreach ($preIdentifiedTransactionCategories as $preIdentifiedTransactionCategory) {
            $identified_category = $this->identify($transactionType, $preIdentifiedTransactionCategory['categoryName'], $preIdentifiedTransactionCategory['keywords']);
            if($identified_category) {
                $category = $identified_category;
                $this->storeIdentifiedTransactionCategory($transactionType, $category);
                break;
            }

        }

        if (empty($category)) {
            $category_id = TransactionCategory::where('name', 'Other')->value('id');
            $category = [
                'category_id' => $category_id,
                'sub_category_id' => null,
            ];
        }

        return $category;
    }

    public function existsInIdentifiedCategories(string $transactionType): bool|array
    {
        $identifiedCategory = IdentifiedTransactionCategory::whereSubject($transactionType)->first();

        if ($identifiedCategory) {
            return [
                'category_id' => $identifiedCategory->transaction_category_id,
                'sub_category_id' => $identifiedCategory->transaction_sub_category_id,
            ];
        }

        return false;
    }

    /**
     * @return array[]
     */
    private function getPreIdentifiedTransactionCategories(): array
    {
        return [
            ['categoryName' => 'Food', 'keywords' => ['hotel', 'restaurant', 'lodging']],
            ['categoryName' => 'Travel', 'keywords' => ['airline', 'travel', 'flight', 'bus']],
            ['categoryName' => 'Shopping', 'keywords' => ['shopping', 'shop', 'store', 'retail', 'supermarket', 'groceries', 'fashion', 'electronics']],
            ['categoryName' => 'Entertainment', 'keywords' => ['entertainment', 'theatre', 'cinema']],
            ['categoryName' => 'Transport', 'keywords' => ['transport', 'bus', 'train']],
            ['categoryName' => 'Bills', 'keywords' => ['bills', 'bill', 'payment']],
        ];
    }

    public function identify(string $transactionType, string $categoryName, array $keywords): array
    {
        if (Str::of($transactionType)->contains($keywords)) {
            $category_id = TransactionCategory::where('name', $categoryName)->value('id');
            return [
                'category_id' => $category_id,
                'sub_category_id' => null,
            ];
        }

        return [];
    }

    public function storeIdentifiedTransactionCategory(string $transactionType, array $category): void
    {
        IdentifiedTransactionCategory::create([
            'subject' => $transactionType,
            'transaction_category_id' => $category['category_id'],
            'transaction_sub_category_id' => $category['sub_category_id'],
        ]);
    }

}

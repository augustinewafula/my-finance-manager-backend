<?php

namespace App\Actions;

use App\Models\IdentifiedTransactionCategory;
use App\Models\TransactionCategory;
use App\Models\TransactionSubCategory;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class IdentifyMpesaTransactionCategory
{
    public function execute(string $transactionSubject): array
    {
        $transactionSubject = Str::of($transactionSubject)
            ->lower()
            ->toString();
        Log::info('type: '.$transactionSubject);
        if ($category = $this->existsInIdentifiedCategories($transactionSubject)) {
            return $category;
        }

        $identifiedCategory = $this->identifyTransactionCategoryAndSubCategory($transactionSubject);
        $this->storeIdentifiedTransactionCategory($transactionSubject, $identifiedCategory);


        return $identifiedCategory;
    }

    public function existsInIdentifiedCategories(string $transactionSubject): bool|array
    {
        $identifiedCategory = IdentifiedTransactionCategory::whereSubject($transactionSubject)->first();

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
            ['categoryName' => 'Education', 'keywords' => ['school', 'education', 'college', 'university']],
            ['categoryName' => 'Healthcare', 'keywords' => ['hospital', 'health', 'healthcare', 'medical']],
            ['categoryName' => 'Insurance', 'keywords' => ['insurance']],
            ['categoryName' => 'Investments', 'keywords' => ['investments', 'stocks', 'shares', 'equity', 'trading']],
            ['categoryName' => 'Rent', 'keywords' => ['rent']],
            ['categoryName' => 'Utilities', 'keywords' => ['utilities', 'water', 'electricity', 'power', 'energy']],
            ['categoryName' => 'Charity', 'keywords' => ['charity', 'donation']],
            ['categoryName' => 'Salary', 'keywords' => ['salary', 'wages', 'income']],
            ['categoryName' => 'Transfer', 'keywords' => ['transfer', 'send', 'receive']],
            ['categoryName' => 'Savings', 'keywords' => ['savings', 'deposit']],
        ];
    }

    private function identifyTransactionCategoryAndSubCategory($transactionSubject): array
    {
        $transactionCategory = null;
        $transactionSubCategory = null;
        $preIdentifiedCategories = $this->getPreIdentifiedTransactionCategories();

        foreach ($preIdentifiedCategories as $category) {
            // check if any keyword in category matches transaction subject
            foreach ($category['keywords'] as $keyword) {
                if (stripos($transactionSubject, $keyword)) {
                    $transactionCategory = TransactionCategory::where('name', $category['categoryName'])
                        ->with('transactionSubCategories')
                        ->first();
                    if (!empty($transactionCategory->transactionSubCategories)) {
                        // check if any keyword in subcategory matches transaction subject
                        foreach ($transactionCategory->transactionSubCategories as $subCategory) {
                            if (stripos($transactionSubject, $subCategory->name)) {
                                $transactionSubCategory = TransactionSubCategory::where('name', $subCategory->name)
                                    ->where('transaction_category_id', $transactionCategory->id)
                                    ->first();
                                break;
                            }
                        }
                    }
                    break;
                }
            }
            if ($transactionCategory !== null) {
                break;
            }
        }
        if ($transactionCategory === null) {
            $transactionCategory = TransactionCategory::where('name', 'Other')->first();
        }
        $categoryId = $transactionCategory->id;
        $transactionSubCategoryId = $transactionSubCategory ? $transactionSubCategory->id : null;

        return [
            'category_id' => $categoryId,
            'sub_category_id' => $transactionSubCategoryId,
        ];
    }

    public function storeIdentifiedTransactionCategory(string $transactionSubject, array $category): void
    {
        IdentifiedTransactionCategory::create([
            'subject' => $transactionSubject,
            'transaction_category_id' => $category['category_id'],
            'transaction_sub_category_id' => $category['sub_category_id'],
        ]);
    }

}

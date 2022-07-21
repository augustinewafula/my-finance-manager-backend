<?php

namespace Database\Seeders;

use App\Models\TransactionCategory;
use App\Models\TransactionSubCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TransactionCategoryAndSubCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Food',
                'sub_categories' => []
            ],
            [
                'name' => 'Entertainment',
                'sub_categories' => [
                    'Movie',
                    'Party',
                    'Concert',
                    'Sport',
                    'Other',
                ]
            ],
            [
                'name' => 'Transport',
                'sub_categories' => [
                    'Car',
                    'Train',
                    'Bus',
                    'Flight',
                    'Other',
                ]
            ],
            [
                'name' => 'Shopping',
                'sub_categories' => [
                    'Clothes',
                    'Shoes',
                    'Accessories',
                    'Other',
                ]
            ],
            [
                'name' => 'Other',
                'sub_categories' => []
            ],
            [
                'name' => 'Bills',
                'sub_categories' => [
                    'Electricity',
                    'Water',
                    'Gas',
                    'Rent',
                    'Internet',
                    'Telephone',
                    'Other',
                ]
            ],
            [
                'name' => 'Friends and family',
                'sub_categories' => []
            ],
            [
                'name' => 'Withdrawal',
                'sub_categories' => []
            ]
        ];

        foreach ($categories as $category) {
            $created_category = TransactionCategory::create([
                'name' => $category['name'],
            ]);

            foreach ($category['sub_categories'] as $sub_category) {
                TransactionSubCategory::create([
                    'name' => $sub_category,
                    'transaction_category_id' => $created_category->id,
                ]);
            }
        }
    }
}

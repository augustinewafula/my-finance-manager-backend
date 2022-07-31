<?php

namespace Database\Seeders;

use App\Models\User;
use App\Services\UserService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @param UserService $userService
     * @return void
     */
    public function run(UserService $userService): void
    {
        $user = User::create([
            'name' => 'Augustine',
            'email' => 'augustinetreezy@gmail.com',
            'password' => bcrypt('Treezy32')
        ]);

        $userService->assignDefaultTransactionCategoriesAndSubCategories($user);
    }
}

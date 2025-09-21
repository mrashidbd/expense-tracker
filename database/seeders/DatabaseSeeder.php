<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create a test user if one doesn't exist
        $user = User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => bcrypt('password'),
            ]
        );

        // Create default income categories
        $incomeCategories = [
            ['name' => 'Salary', 'color' => '#87c38f'],
            ['name' => 'Client Payments', 'color' => '#226f54'],
            ['name' => 'Freelance', 'color' => '#87c38f'],
            ['name' => 'Investment Returns', 'color' => '#226f54'],
            ['name' => 'Rental Income', 'color' => '#87c38f'],
            ['name' => 'Business Revenue', 'color' => '#226f54'],
            ['name' => 'Other Income', 'color' => '#87c38f'],
        ];

        foreach ($incomeCategories as $category) {
            Category::firstOrCreate(
                [
                    'name' => $category['name'],
                    'type' => 'income',
                    'user_id' => $user->id
                ],
                [
                    'color' => $category['color']
                ]
            );
        }

        // Create default expense categories
        $expenseCategories = [
            ['name' => 'Food & Dining', 'color' => '#da2c38'],
            ['name' => 'Transportation', 'color' => '#b91c26'],
            ['name' => 'Utilities', 'color' => '#da2c38'],
            ['name' => 'Rent/Mortgage', 'color' => '#b91c26'],
            ['name' => 'Healthcare', 'color' => '#da2c38'],
            ['name' => 'Entertainment', 'color' => '#b91c26'],
            ['name' => 'Shopping', 'color' => '#da2c38'],
            ['name' => 'Education', 'color' => '#b91c26'],
            ['name' => 'Insurance', 'color' => '#da2c38'],
            ['name' => 'Travel', 'color' => '#b91c26'],
            ['name' => 'Other Expenses', 'color' => '#da2c38'],
        ];

        foreach ($expenseCategories as $category) {
            Category::firstOrCreate(
                [
                    'name' => $category['name'],
                    'type' => 'expense',
                    'user_id' => $user->id
                ],
                [
                    'color' => $category['color']
                ]
            );
        }
    }
}

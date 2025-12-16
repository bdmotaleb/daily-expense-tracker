<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaults = [
            ['name' => 'Food & Groceries', 'type' => 'expense', 'color' => '#f97316'],
            ['name' => 'Rent & Housing', 'type' => 'expense', 'color' => '#22c55e'],
            ['name' => 'Utilities', 'type' => 'expense', 'color' => '#3b82f6'],
            ['name' => 'Transportation', 'type' => 'expense', 'color' => '#a855f7'],
            ['name' => 'Healthcare', 'type' => 'expense', 'color' => '#ef4444'],
            ['name' => 'Entertainment', 'type' => 'expense', 'color' => '#eab308'],
            ['name' => 'Shopping', 'type' => 'expense', 'color' => '#6366f1'],
            ['name' => 'Loan Payments', 'type' => 'expense', 'color' => '#10b981'],
            ['name' => 'Credit Card Payments', 'type' => 'expense', 'color' => '#f43f5e'],
            ['name' => 'Savings', 'type' => 'expense', 'color' => '#8b5cf6'],
            ['name' => 'Salary', 'type' => 'income', 'color' => '#14b8a6'],
            ['name' => 'Freelance', 'type' => 'income', 'color' => '#8b5cf6'],
            ['name' => 'Investments', 'type' => 'income', 'color' => '#f59e0b'],
            ['name' => 'Gift', 'type' => 'income', 'color' => '#f59e0b'],
            ['name' => 'Bonus', 'type' => 'income', 'color' => '#f59e0b'],
            ['name' => 'Other Income', 'type' => 'income', 'color' => '#f59e0b'],
            ['name' => 'Taxes', 'type' => 'expense', 'color' => '#dc2626'],
            ['name' => 'Other', 'type' => 'expense', 'color' => '#6b7280']
        ];

        foreach ($defaults as $data) {
            Category::firstOrCreate(
                [
                    'user_id' => null,
                    'name' => $data['name'],
                    'type' => $data['type'],
                ],
                [
                    'color' => $data['color'],
                ]
            );
        }
    }
}



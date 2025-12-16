<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Expense;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class ExpenseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $userId = 1;

        // Basic guard in case user_id 1 does not exist yet
        if (!\App\Models\User::whereKey($userId)->exists()) {
            return;
        }

        // Use existing expense categories (global + user-specific)
        $categories = Category::where('type', 'expense')->get();

        if ($categories->isEmpty()) {
            return;
        }

        $paymentMethods = ['Cash', 'Credit Card', 'Debit Card', 'Bank Transfer', 'E-Wallet'];

        // Generate expenses spread over roughly the last 12 months
        foreach (range(1, 2000) as $i) {
            $date = Carbon::now()->subDays(rand(0, 350))->toDateString();
            $category = $categories->random();

            Expense::create([
                'user_id'        => $userId,
                'category_id'    => $category->id,
                'amount'         => rand(500, 30_000) / 100, // 5.00 – 300.00
                'payment_method' => $paymentMethods[array_rand($paymentMethods)],
                'date'           => $date,
                'note'           => fake()->boolean(40) ? fake()->sentence(6) : null,
            ]);
        }
    }
}



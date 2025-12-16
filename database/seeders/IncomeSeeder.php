<?php

namespace Database\Seeders;

use App\Models\Income;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class IncomeSeeder extends Seeder
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

        $sources = ['Salary', 'Freelance', 'Investments', 'Bonus', 'Gift'];

        // Generate incomes spread over roughly the last 12 months
        foreach (range(1, 200) as $i) {
            $date = Carbon::now()->subDays(rand(0, 349))->toDateString();

            Income::create([
                'user_id' => $userId,
                'amount'  => rand(50_000, 300_000) / 100, // 500.00 – 3000.00
                'source'  => $sources[array_rand($sources)],
                'date'    => $date,
                'note'    => fake()->boolean(30) ? fake()->sentence(5) : null,
            ]);
        }
    }
}



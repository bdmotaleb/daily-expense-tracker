<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Expense;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpenseTest extends TestCase
{
    use RefreshDatabase;

    protected function authenticate(): User
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        return $user;
    }

    public function test_can_list_expenses(): void
    {
        $user = $this->authenticate();
        $category = Category::create([
            'user_id' => $user->id,
            'name' => 'Test Category',
            'type' => 'expense',
        ]);

        Expense::create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'amount' => 50,
            'payment_method' => 'Cash',
            'date' => now()->toDateString(),
        ]);

        $response = $this->getJson('/api/expenses');

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => [['id', 'amount', 'date', 'category']]]);
    }

    public function test_can_create_expense(): void
    {
        $user = $this->authenticate();
        $category = Category::create([
            'user_id' => $user->id,
            'name' => 'Test Category',
            'type' => 'expense',
        ]);

        $response = $this->postJson('/api/expenses', [
            'category_id' => $category->id,
            'amount' => 75,
            'payment_method' => 'Card',
            'date' => now()->toDateString(),
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['id', 'amount', 'date', 'category']);
    }
}



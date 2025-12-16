<?php

namespace Tests\Feature;

use App\Models\Income;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IncomeTest extends TestCase
{
    use RefreshDatabase;

    protected function authenticate(): User
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        return $user;
    }

    public function test_can_list_incomes(): void
    {
        $user = $this->authenticate();

        Income::create([
            'user_id' => $user->id,
            'amount' => 100,
            'source' => 'Salary',
            'date' => now()->toDateString(),
        ]);

        $response = $this->getJson('/api/incomes');

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => [['id', 'amount', 'source', 'date']]]);
    }

    public function test_can_create_income(): void
    {
        $this->authenticate();

        $response = $this->postJson('/api/incomes', [
            'amount' => 200,
            'source' => 'Freelance',
            'date' => now()->toDateString(),
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['id', 'amount', 'source', 'date']);
    }
}



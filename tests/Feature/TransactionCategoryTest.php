<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class TransactionCategoryTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createUser();
    }

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_unauthenticated_user_cannot_access_transaction_categories(): void
    {
        $response = $this->get('/api/v1/transaction-categories', [
            'Accept' => 'application/json'
        ]);

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_access_transaction_categories(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')->get('/api/v1/transaction-categories', [
            'Accept' => 'application/json'
        ]);

        $response->assertStatus(200);
    }

    public function test_unauthenticated_user_cannot_create_transaction_categories(): void
    {
        $response = $this->post('/api/v1/transaction-categories', [
            'name' => 'Test transaction category',
        ], [
            'Accept' => 'application/json'
        ]);

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_create_transaction_categories(): void
    {
        $response = $this->createTransactionCategory();

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'id',
            'name',
            'created_at',
            'updated_at'
        ]);
        $this->assertDatabaseHas('transaction_categories', [
            'name' => 'Test transaction category'
        ]);
    }

    public function test_authenticated_user_can_delete_categories(): void
    {
        $response = $this->createTransactionCategory();
        $response->assertStatus(201);
        $response->assertJsonStructure([
            'id',
            'name',
            'created_at',
            'updated_at'
        ]);
        $this->assertDatabaseHas('transaction_categories', [
            'name' => 'Test transaction category'
        ]);

        $response = $this->actingAs($this->user, 'sanctum')->delete('/api/v1/transaction-categories/' . $response->json('id'), [], [
            'Accept' => 'application/json'
        ]);

        $response->assertStatus(204);
        $this->assertDatabaseMissing('transaction_categories', [
            'name' => 'Test transaction category'
        ]);
    }

    public function createTransactionCategory(): TestResponse
    {
        return $this->actingAs($this->user, 'sanctum')->post('/api/v1/transaction-categories', [
            'name' => 'Test transaction category',
        ], [
            'Accept' => 'application/json'
        ]);
    }

    private function createUser(): User
    {
        return User::factory()->create(['password' => bcrypt('password')]);
    }


}

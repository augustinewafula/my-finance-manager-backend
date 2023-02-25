<?php

namespace Tests\Feature;

use App\Models\Bond;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class BondsTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createUser();
    }

    public function test_unauthenticated_user_cannot_access_bonds(): void
    {
        $response = $this->get('/api/v1/bonds', [
            'Accept' => 'application/json'
        ]);

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_access_bonds(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')->get('/api/v1/bonds', [
            'Accept' => 'application/json'
        ]);

        $response->assertStatus(200);
    }

    public function test_authenticated_user_can_create_bonds(): void
    {
        $response = $this->createBond();

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'bond' => [
                'id',
                'issue_number',
                'coupon_rate',
                'amount_invested',
                'created_at',
                'updated_at'
            ]
        ]);
    }

    public function test_authenticated_user_cannot_create_bonds_with_invalid_dates(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')->post('/api/v1/bonds', [
            'issue_number' => '123456',
            'coupon_rate' => 5.5,
            'amount_invested' => 1000,
            'interest_payment_dates' => "01-01-202, 01-02-221, 01-3-2021"
        ], [
            'Accept' => 'application/json'
        ]);

        $response->assertStatus(422);

    }

    public function test_authenticated_user_can_update_bonds(): void
    {
        $createBondResponse = $this->createBond();
        $bondId = $createBondResponse->json('bond.id');

        $data = [
            'issue_number' => '1738',
            'coupon_rate' => 5.5,
            'amount_invested' => 1000,
            'interest_payment_dates' => "01-01-2021, 01-02-2021, 01-03-2021"
        ];
        $response = $this->actingAs($this->user, 'sanctum')->put("/api/v1/bonds/$bondId", $data, [
            'Accept' => 'application/json'
        ]);

        $response->assertStatus(200);
        Log::info($response->json());
        $response->assertJson([
            'message' => 'Bond updated successfully',
            'bond' => Arr::except($data, ['interest_payment_dates'])
        ]);
    }

    private function createUser(): User
    {
        return User::factory()->create(['password' => bcrypt('password')]);
    }

    /**
     * @return TestResponse
     */
    private function createBond(): TestResponse
    {
        return $this->actingAs($this->user, 'sanctum')->post('/api/v1/bonds', [
            'issue_number' => '123456',
            'coupon_rate' => 5.5,
            'amount_invested' => 1000,
            'interest_payment_dates' => "01-01-2021, 01-02-2021, 01-03-2021"
        ], [
            'Accept' => 'application/json'
        ]);
    }
}
